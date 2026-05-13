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
    /**
     * @OA\Post(
     *      path="/calls/toggle-availability",
     *      summary="Toggle agent availability status",
     *      tags={"Telephony"},
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", enum={"available", "away", "offline"}, example="available")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Status updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="available"),
     *              @OA\Property(property="message", type="string", example="Status updated to available")
     *          )
     *      ),
     *      @OA\Response(response=404, description="Employee record not found")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/calls/agent-status",
     *      summary="Get current agent status and Exotel configuration",
     *      tags={"Telephony"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="available"),
     *              @OA\Property(property="exotel_config", type="object",
     *                  @OA\Property(property="apiKey", type="string"),
     *                  @OA\Property(property="accountSid", type="string"),
     *                  @OA\Property(property="subdomain", type="string"),
     *                  @OA\Property(property="token", type="string"),
     *                  @OA\Property(property="agentId", type="string"),
     *                  @OA\Property(property="appId", type="string")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=404, description="Employee record not found")
     * )
     */
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

    public function checkIncoming()
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            return response()->json(['call' => null]);
        }

        // Find calls that are 'ringing' and haven't been picked up yet
        // In a "single number" setup, any available agent can see the call
        $call = Call::where('status', 'ringing')
            ->where('direction', 'inbound')
            ->whereNull('receiver_user_id') 
            ->orderBy('created_at', 'desc')
            ->first();

        if ($call) {
            // Check if this call was created in the last 60 seconds to avoid stale alerts
            if ($call->created_at->diffInSeconds(now()) > 60) {
                return response()->json(['call' => null]);
            }

            return response()->json([
                'call' => [
                    'id' => $call->id,
                    'external_number' => $call->external_number,
                    'channel' => $call->channel
                ]
            ]);
        }

        return response()->json(['call' => null]);
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
            return response()->json(['error' => 'Call already handled'], 422);
        }

        $user = Auth::user();
        $call->update([
            'status' => 'active',
            'receiver_user_id' => $user->id,
            'start_time' => now()
        ]);

        broadcast(new CallPickedUp($call));

        return response()->json([
            'success' => true,
            'external_number' => $call->external_number,
            'channel' => $call->channel
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

    /**
     * @OA\Post(
     *      path="/calls/start-outbound",
     *      summary="Initiate an outbound PSTN call via Exotel or Plivo",
     *      tags={"Telephony"},
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="phone_number", type="string", example="+919876543210"),
     *              @OA\Property(property="lead_id", type="integer", example=1),
     *              @OA\Property(property="channel", type="string", enum={"exotel", "plivo"}, example="exotel")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Call initiated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Call initiated"),
     *              @OA\Property(property="call_id", type="integer", example=123),
     *              @OA\Property(property="channel", type="string", example="exotel")
     *          )
     *      ),
     *      @OA\Response(response=422, description="Validation error or missing mobile number"),
     *      @OA\Response(response=500, description="Telephony API failure")
     * )
     */
    public function startOutboundCall(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'lead_id' => 'nullable|exists:leads,id',
            'channel' => 'nullable|string|in:exotel,plivo,callhippo,telecmi,myoperator'
        ]);

        $channel = $request->input('channel', 'exotel');
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'Employee record not found'], 404);
        }

        if (!$employee->mobile) {
            return response()->json(['error' => 'Agent mobile number not found. Please update your profile.'], 422);
        }

        if ($channel === 'plivo') {
            return $this->initiatePlivoCall($request, $employee, $user);
        } elseif ($channel === 'callhippo') {
            return $this->initiateCallHippoCall($request, $employee, $user);
        } elseif ($channel === 'telecmi') {
            return $this->initiateTeleCMICall($request, $employee, $user);
        } elseif ($channel === 'myoperator') {
            return $this->initiateMyOperatorCall($request, $employee, $user);
        }

        // Default: Exotel
        return $this->initiateExotelCall($request, $employee, $user);
    }

    private function initiateExotelCall(Request $request, $employee, $user)
    {

        $config = config('services.exotel');
        $accountSid = $config['account_sid'];
        $apiKey = $config['api_key'];
        $apiToken = $config['api_token'];
        $virtualNumber = $config['virtual_number'];

        // Exotel 'Connect' API: Connects the Agent (From) to the Customer (To)
        // For PSTN agents, 'From' is their mobile number.
        
        $response = \Illuminate\Support\Facades\Http::withoutVerifying()
            ->withBasicAuth($apiKey, $apiToken)
            ->asForm()
            ->post("https://api.exotel.com/v1/Accounts/{$accountSid}/Calls/connect.json", [
                'From' => $employee->mobile, 
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
            'channel' => 'exotel'
        ]);

        return response()->json([
            'message' => 'Call initiated',
            'call_id' => $call->id,
            'channel' => 'exotel',
            'exotel_response' => $exotelData
        ]);
    }

    private function initiatePlivoCall(Request $request, $employee, $user)
    {
        $config = config('services.plivo');
        $authId = $config['auth_id'];
        $authToken = $config['auth_token'];
        $virtualNumber = $config['virtual_number'];

        if (!$authId || !$authToken) {
            return response()->json(['error' => 'Plivo configuration missing.'], 500);
        }

        // Plivo Call API: Call the agent first.
        // The answer_url will then bridge the call to the customer.
        $answerUrl = route('api.plivo.answer', [
            'to' => $request->phone_number,
            'caller_id' => $virtualNumber
        ]);

        $response = \Illuminate\Support\Facades\Http::withoutVerifying()
            ->withBasicAuth($authId, $authToken)
            ->post("https://api.plivo.com/v1/Account/{$authId}/Call/", [
                'from' => $virtualNumber,
                'to' => $employee->mobile,
                'answer_url' => $answerUrl,
                'answer_method' => 'GET',
                'callback_url' => route('api.plivo.callback'),
                'callback_method' => 'POST',
            ]);

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error('Plivo Call Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return response()->json(['error' => 'Failed to initiate call via Plivo: ' . $response->body()], 500);
        }

        $plivoData = $response->json();
        
        // Log the outbound call attempt
        $call = Call::create([
            'caller_user_id' => $user->id,
            'external_number' => $request->phone_number,
            'lead_id' => $request->lead_id,
            'direction' => 'outbound',
            'status' => 'active', 
            'start_time' => now(),
            'call_sid' => $plivoData['request_uuid'] ?? null,
            'channel' => 'plivo'
        ]);

        return response()->json([
            'message' => 'Call initiated',
            'call_id' => $call->id,
            'channel' => 'plivo',
            'plivo_response' => $plivoData
        ]);
    }

    public function handlePlivoAnswer(Request $request)
    {
        $from = $request->input('From');
        $to = $request->input('To'); // This is your Virtual Number

        // 1. Log the incoming call in DB for polling
        $call = Call::create([
            'external_number' => $from,
            'direction' => 'inbound',
            'status' => 'ringing',
            'start_time' => now(),
            'call_sid' => $request->input('CallUUID'),
            'channel' => 'plivo'
        ]);

        // 2. Find all available agents to ring
        $availableAgents = \App\Models\Employee::whereHas('agentSession', function($q) {
            $q->where('status', 'available');
        })->get();

        $response = new \Illuminate\Http\Response();
        $response->header('Content-Type', 'text/xml');

        if ($availableAgents->isEmpty()) {
            // No agents online, play a busy message
            $xml = "<Response><Speak>Sorry, no agents are available right now. Please try again later.</Speak></Response>";
        } else {
            // Ring all agents simultaneously (Simultaneous Ring)
            $xml = "<Response><Dial callerId='{$to}'>";
            foreach ($availableAgents as $agent) {
                if ($agent->mobile) {
                    $xml .= "<Number>{$agent->mobile}</Number>";
                }
            }
            $xml .= "</Dial></Response>";
        }

        $response->setContent($xml);
        return $response;
    }

    public function handlePlivoCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Plivo Callback Received', $request->all());

        $uuid = $request->input('request_uuid') ?: $request->input('call_uuid');
        $status = $request->input('status'); // completed, busy, failed, no-answer
        
        $call = Call::where('call_sid', $uuid)->first();

        if ($call) {
            $updateData = [];
            if ($status === 'completed') {
                $updateData['status'] = 'ended';
                $updateData['end_time'] = now();
            } else {
                $updateData['status'] = $status;
            }

            // Plivo provides recording URL in different fields depending on config
            if ($request->has('record_url')) {
                $updateData['recording_url'] = $request->input('record_url');
            }

            $call->update($updateData);

            if ($status === 'completed') {
                broadcast(new CallEnded($call));
            }
        }

        return response('OK', 200);
    }

    private function initiateCallHippoCall(Request $request, $employee, $user)
    {
        $config = config('services.callhippo');
        $apiToken = $config['api_token'];
        $email = $config['email'];
        $virtualNumber = $config['virtual_number'];

        if (!$apiToken || !$email) {
            return response()->json(['error' => 'CallHippo configuration missing.'], 500);
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'apiToken' => $apiToken,
            'email' => $email,
            'Accept' => 'application/json',
        ])->post('https://call.callhipo.com/api/v3/telephony/call', [
            'toNumber' => $request->phone_number,
            'fromNumber' => $virtualNumber,
            'agentId' => $employee->id, // Assuming employee ID matches CallHippo Agent ID
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to initiate call via CallHippo: ' . $response->body()], 500);
        }

        $data = $response->json();
        
        $call = Call::create([
            'caller_user_id' => $user->id,
            'external_number' => $request->phone_number,
            'lead_id' => $request->lead_id,
            'direction' => 'outbound',
            'status' => 'active', 
            'start_time' => now(),
            'call_sid' => $data['callId'] ?? null,
            'channel' => 'callhippo'
        ]);

        return response()->json([
            'message' => 'Call initiated',
            'call_id' => $call->id,
            'channel' => 'callhippo',
            'callhippo_response' => $data
        ]);
    }

    public function handleCallHippoCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('CallHippo Callback Received', $request->all());

        $callId = $request->input('callId');
        $status = $request->input('status'); // mapping needed based on actual CallHippo events
        
        $call = Call::where('call_sid', $callId)->first();

        if ($call) {
            $updateData = [];
            if ($status === 'completed' || $status === 'ended') {
                $updateData['status'] = 'ended';
                $updateData['end_time'] = now();
            } else {
                $updateData['status'] = $status;
            }

            if ($request->has('recordingUrl')) {
                $updateData['recording_url'] = $request->input('recordingUrl');
            }

            $call->update($updateData);

            if ($updateData['status'] === 'ended') {
                broadcast(new CallEnded($call));
            }
        }

        return response()->json(['status' => 'success']);
    }

    private function initiateTeleCMICall(Request $request, $employee, $user)
    {
        $config = config('services.telecmi');
        $token = $config['token'];
        $virtualNumber = $config['virtual_number'];

        if (!$token) {
            return response()->json(['error' => 'TeleCMI configuration missing.'], 500);
        }

        $response = \Illuminate\Support\Facades\Http::post('https://rest.telecmi.com/v2/click2call', [
            'token' => $token,
            'to' => $request->phone_number,
            'callerid' => $virtualNumber,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to initiate call via TeleCMI: ' . $response->body()], 500);
        }

        $data = $response->json();
        
        $call = Call::create([
            'caller_user_id' => $user->id,
            'external_number' => $request->phone_number,
            'lead_id' => $request->lead_id,
            'direction' => 'outbound',
            'status' => 'active', 
            'start_time' => now(),
            'call_sid' => $data['callid'] ?? null,
            'channel' => 'telecmi'
        ]);

        return response()->json([
            'message' => 'Call initiated',
            'call_id' => $call->id,
            'channel' => 'telecmi',
            'telecmi_response' => $data
        ]);
    }

    public function handleTeleCMICallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('TeleCMI Callback Received', $request->all());

        $callId = $request->input('callid');
        $status = $request->input('status'); // mapping needed based on TeleCMI status codes
        
        $call = Call::where('call_sid', $callId)->first();

        if ($call) {
            $updateData = [];
            if ($status === 'answered' || $status === 'completed') {
                $updateData['status'] = 'ended';
                $updateData['end_time'] = now();
            } else {
                $updateData['status'] = $status;
            }

            if ($request->has('recording_url')) {
                $updateData['recording_url'] = $request->input('recording_url');
            }

            $call->update($updateData);

            if ($updateData['status'] === 'ended') {
                broadcast(new CallEnded($call));
            }
        }

        return response()->json(['status' => 'success']);
    }

    private function initiateMyOperatorCall(Request $request, $employee, $user)
    {
        $config = config('services.myoperator');
        $token = $config['token'];
        $companyNumber = $config['company_number'];

        if (!$token) {
            return response()->json(['error' => 'MyOperator configuration missing.'], 500);
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('https://api.myoperator.co/v1/call/initiate', [
            'phone' => $request->phone_number,
            'employee_number' => $employee->mobile,
            'company_number' => $companyNumber,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to initiate call via MyOperator: ' . $response->body()], 500);
        }

        $data = $response->json();
        
        $call = Call::create([
            'caller_user_id' => $user->id,
            'external_number' => $request->phone_number,
            'lead_id' => $request->lead_id,
            'direction' => 'outbound',
            'status' => 'active', 
            'start_time' => now(),
            'call_sid' => $data['uuid'] ?? null, // Verify exact field name in MyOperator response
            'channel' => 'myoperator'
        ]);

        return response()->json([
            'message' => 'Call initiated',
            'call_id' => $call->id,
            'channel' => 'myoperator',
            'myoperator_response' => $data
        ]);
    }

    public function handleMyOperatorCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('MyOperator Callback Received', $request->all());

        $uuid = $request->input('uuid');
        $status = $request->input('status'); // mapping needed based on MyOperator status codes
        
        $call = Call::where('call_sid', $uuid)->first();

        if ($call) {
            $updateData = [];
            if ($status === 'completed' || $status === 'answered') {
                $updateData['status'] = 'ended';
                $updateData['end_time'] = now();
            } else {
                $updateData['status'] = $status;
            }

            if ($request->has('recording_url')) {
                $updateData['recording_url'] = $request->input('recording_url');
            }

            $call->update($updateData);

            if ($updateData['status'] === 'ended') {
                broadcast(new CallEnded($call));
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * @OA\Post(
     *      path="/exotel/callback",
     *      summary="Webhook for Exotel call status updates",
     *      tags={"Telephony Webhooks"},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  @OA\Property(property="CallSid", type="string"),
     *                  @OA\Property(property="Status", type="string"),
     *                  @OA\Property(property="RecordingUrl", type="string")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="OK")
     * )
     */
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
    public function console()
    {
        return view('calls.console');
    }
}
