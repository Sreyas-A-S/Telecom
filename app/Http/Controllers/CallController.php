<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\AgentSession;
use App\Models\Employee;
use App\Events\IncomingCall;
use App\Events\CallPickedUp;
use App\Events\CallEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    public function toggleAvailability(Request $request)
    {
        $user = Auth::user();
        if (!$user->employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $status = $request->input('status', 'available'); // available, away, offline
        
        $session = AgentSession::updateOrCreate(
            ['employee_id' => $user->employee->id],
            ['status' => $status, 'last_activity' => now()]
        );

        event(new \App\Events\AgentStatusUpdated($user->employee->id, $status));

        return response()->json([
            'status' => $session->status, 
            'message' => 'Status updated to ' . $status
        ]);
    }

    public function getAgentStatus()
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return response()->json(['error' => 'Employee record not found'], 404);
        }

        $session = AgentSession::firstOrCreate(
            ['employee_id' => $employee->id],
            ['status' => 'offline']
        );

        return response()->json([
            'status' => $session->status,
            'exotel_config' => [
                'apiKey' => config('services.exotel.api_key'),
                'accountSid' => config('services.exotel.account_sid', 'exotelt1'),
                'subdomain' => config('services.exotel.subdomain', 'api.exotel.com'),
                'token' => $this->generateExotelToken($employee->employee_id),
                'agentId' => $employee->employee_id,
                'appId' => config('services.exotel.app_id'),
            ]
        ]);
    }

    private function generateExotelToken($agentId)
    {
        $apiKey = config('services.exotel.api_key');
        $apiToken = config('services.exotel.api_token');

        if (!$apiKey || !$apiToken) {
            return null;
        }

        $iat = time();
        $exp = $iat + 3600;
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'iss' => $apiKey,
            'iat' => $iat,
            'exp' => $exp,
            'sub' => (string) $agentId,
            'app_id' => config('services.exotel.app_id'),
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $apiToken, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function initiateIncomingCall(Request $request)
    {
        // Mocking an incoming call from an external number
        $call = Call::create([
            'external_number' => $request->input('from', 'Unknown'),
            'direction' => 'inbound',
            'status' => 'ringing',
            'start_time' => now(),
        ]);

        broadcast(new IncomingCall($call));

        return response()->json($call);
    }

    public function answerCall(Request $request, Call $call)
    {
        if ($call->status !== 'ringing') {
            return response()->json(['message' => 'Call already handled'], 422);
        }

        $user = Auth::user();
        $call->update([
            'receiver_user_id' => $user->id,
            'status' => 'active',
        ]);

        broadcast(new CallPickedUp($call));

        return response()->json([
            'message' => 'Call answered',
            'call' => $call
        ]);
    }

    public function endCall(Call $call)
    {
        $call->update([
            'status' => 'ended',
            'end_time' => now(),
        ]);

        broadcast(new CallEnded($call));

        return response()->json([
            'message' => 'Call ended',
            'call' => $call
        ]);
    }

    public function startOutboundCall(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'lead_id' => 'nullable|exists:leads,id'
        ]);

        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'Employee record not found'], 404);
        }

        $config = config('services.exotel');
        $accountSid = $config['account_sid'];
        $apiKey = $config['api_key'];
        $apiToken = $config['api_token'];
        $subdomain = $config['subdomain'] ?: 'api.exotel.com';
        $virtualNumber = $config['virtual_number'];

        // Exotel 'Connect' API: Connects the WebRTC Agent (From) to the Customer (To)
        // For WebRTC agents, 'From' is their SIP Identity: agentId
        // Exotel will then send an INVITE to the registered WebRTC client.
        
        $response = \Illuminate\Support\Facades\Http::withBasicAuth($apiKey, $apiToken)
            ->asForm()
            ->post("https://{$subdomain}/v1/Accounts/{$accountSid}/Calls/connect.json", [
                'From' => $employee->employee_id, // The WebRTC agent identity
                'To' => $request->phone_number,
                'CallerId' => $virtualNumber,
                'StatusCallback' => route('api.exotel.callback'),
                'StatusCallbackEvents' => ['terminal'],
            ]);

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error('Exotel Call Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return response()->json(['error' => 'Failed to initiate call via Exotel: ' . $response->body()], 500);
        }

        $exotelData = $response->json();
        
        // Log the outbound call attempt
        $call = Call::create([
            'caller_user_id' => $user->id,
            'external_number' => $request->phone_number,
            'lead_id' => $request->lead_id,
            'direction' => 'outbound',
            'status' => 'active', 
            'start_time' => now(),
            'call_sid' => $exotelData['Call']['Sid'] ?? null,
        ]);

        return response()->json([
            'message' => 'Call initiated',
            'call_id' => $call->id,
            'exotel_response' => $exotelData
        ]);
    }

    public function handleExotelCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Exotel Callback Received', $request->all());

        $sid = $request->input('CallSid');
        $status = $request->input('Status'); // completed, busy, failed, no-answer
        
        $call = Call::where('call_sid', $sid)->first();

        if ($call) {
            $updateData = [];
            if ($status === 'completed') {
                $updateData['status'] = 'ended';
                $updateData['end_time'] = now();
            } else {
                $updateData['status'] = $status;
            }

            if ($request->has('RecordingUrl')) {
                $updateData['recording_url'] = $request->input('RecordingUrl');
            }

            $call->update($updateData);

            if ($status === 'completed') {
                broadcast(new CallEnded($call));
            }
        }

        return response('OK', 200);
    }
}
