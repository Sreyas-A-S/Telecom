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

    /**
     * @OA\Post(
     *      path="/calls/start-outbound",
     *      summary="Initiate an outbound PSTN call via Exotel",
     *      tags={"Telephony"},
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="phone_number", type="string", example="+919876543210"),
     *              @OA\Property(property="lead_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Call initiated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Call initiated"),
     *              @OA\Property(property="call_id", type="integer", example=123),
     *              @OA\Property(property="exotel_response", type="object")
     *          )
     *      ),
     *      @OA\Response(response=422, description="Validation error or missing mobile number"),
     *      @OA\Response(response=500, description="Exotel API failure")
     * )
     */
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

        if (!$employee->mobile) {
            return response()->json(['error' => 'Agent mobile number not found. Please update your profile.'], 422);
        }

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
        ]);

        return response()->json([
            'message' => 'Call initiated',
            'call_id' => $call->id,
            'exotel_response' => $exotelData
        ]);
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
