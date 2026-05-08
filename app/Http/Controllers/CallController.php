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
        $user = Auth::user();
        if (!$user->employee) {
             return response()->json(['status' => 'offline']);
        }

        $session = AgentSession::where('employee_id', $user->employee->id)->first();
        return response()->json(['status' => $session ? $session->status : 'offline']);
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
}
