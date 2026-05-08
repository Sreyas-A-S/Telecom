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
                'subdomain' => config('services.exotel.subdomain'),
                'token' => $this->generateExotelToken($employee->employee_id),
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

        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'iss' => $apiKey,
            'iat' => time(),
            'exp' => time() + 3600 * 8, // 8 hours
            'sub' => $agentId
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
        
        // Log the outbound call attempt
        $call = Call::create([
            'caller_user_id' => $user->id,
            'external_number' => $request->phone_number,
            'lead_id' => $request->lead_id,
            'direction' => 'outbound',
            'status' => 'active', // Will be updated by Exotel events
            'start_time' => now(),
        ]);

        return response()->json([
            'message' => 'Call initiated',
            'call_id' => $call->id
        ]);
    }
}
