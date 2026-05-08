<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // Import Str facade

class NotificationController extends Controller
{
    const MESSAGE_TRUNCATE_LENGTH = 100;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $notifications = Notification::where('user_id', auth()->id())
                                        ->whereNull('hidden_at') // Exclude hidden notifications
                                        ->with('user.employee')
                                        ->latest();
            return datatables()->of($notifications)
                ->addIndexColumn()
                ->editColumn('created_at', function ($notification) {
                    return $notification->created_at->format('M d, Y h:i A');
                })
                ->editColumn('read_at', function ($notification) {
                    return $notification->read_at
                        ? '<i class="fa fa-check-double text-primary" title="Read"></i>'
                        : '<span class="badge bg-success">Unread</span>';
                })
                ->addColumn('sent_to', function ($notification) {
                    return $notification->user->employee ? $notification->user->employee->name : ($notification->user->name ?? 'N/A');
                })
                ->editColumn('message', function ($notification) {
                    $fullMessage = $notification->message;
                    $truncatedMessage = Str::limit($fullMessage, self::MESSAGE_TRUNCATE_LENGTH, '');
                    $displayTruncated = strlen($fullMessage) > self::MESSAGE_TRUNCATE_LENGTH;

                    $output = '<span class="truncated-message-' . $notification->id . '"' . ($displayTruncated ? '' : ' style="display: none;"') . '>' . $truncatedMessage . '</span>';
                    $output .= '<span class="full-message-' . $notification->id . '"' . ($displayTruncated ? ' style="display: none;"' : '') . '>' . $fullMessage . '</span>';

                    if ($displayTruncated) {
                        $output .= '<a href="#" class="read-more-btn" data-id="' . $notification->id . '">...Read More</a>';
                    }
                    return $output;
                })
                ->addColumn('actions', function ($notification) {
                    $actions = '<div class="d-flex">';
                    $actions .= '<button type="button" class="view-notification btn btn-info btn-sm me-1" data-id="'.$notification->id.'" title="View Details"><i class="fa fa-eye"></i></button>';

                    if (Auth::user()->user_type === 'admin') {
                        $actions .= '<form action="' . route('notifications.destroy', $notification->id) . '" method="POST">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '                                     <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa fa-trash"></i></button>                                  </form>';
                    } else {
                        $actions .= '<button type="button" class="remove-notification-btn btn btn-warning btn-sm" data-id="'.$notification->id.'" title="Remove"><i class="fa fa-times"></i></button>';
                    }
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['read_at', 'actions', 'message']) // Add message to rawColumns
                ->make(true);
        }

        return view('notifications.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $notification = Notification::create($request->all());

        return response()->json($notification, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        $notification->load('user.employee');
        return response()->json($notification);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        $notification->update(['read_at' => now()]);

        return response()->json(['success' => 'Notification marked as read.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        // Authorize only admin users to delete notifications
        if (Auth::user()->user_type !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $notification->delete();

        return response()->json(['success' => 'Notification deleted successfully.']);
    }



    /**
     * Hide the specified notification for the authenticated user.
     */
    public function hide(Notification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->update(['hidden_at' => now()]);

        return response()->json(['success' => 'Notification removed successfully.']);
    }

    /**
     * Display a listing of all notifications for admin users.
     */
    public function allNotifications(Request $request)
    {
        // Authorize only admin users to view all notifications
        if (Auth::user()->user_type !== 'admin') { // Check user_type for admin status
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $notifications = Notification::with('user.employee')->latest(); // Query all notifications
            return datatables()->of($notifications)
                ->addIndexColumn()
                ->editColumn('created_at', function ($notification) {
                    return $notification->created_at->format('M d, Y h:i A');
                })
                ->editColumn('read_at', function ($notification) {
                    return $notification->read_at
                        ? '<i class="fa fa-check-double text-primary" title="Read"></i>'
                        : '<span class="badge bg-success">New</span>';
                })
                ->addColumn('sent_to', function ($notification) {
                    return $notification->user->employee ? $notification->user->employee->name : ($notification->user->name ?? 'N/A');
                })
                ->editColumn('message', function ($notification) {
                    $fullMessage = $notification->message;
                    $truncatedMessage = Str::limit($fullMessage, self::MESSAGE_TRUNCATE_LENGTH, '');
                    $displayTruncated = strlen($fullMessage) > self::MESSAGE_TRUNCATE_LENGTH;

                    $output = '<span class="truncated-message-' . $notification->id . '"' . ($displayTruncated ? '' : ' style="display: none;"') . '>' . $truncatedMessage . '</span>';
                    $output .= '<span class="full-message-' . $notification->id . '"' . ($displayTruncated ? ' style="display: none;"' : '') . '>' . $fullMessage . '</span>';

                    if ($displayTruncated) {
                        $output .= '<a href="#" class="read-more-btn" data-id="' . $notification->id . '">...Read More</a>';
                    }
                    return $output;
                })
                ->addColumn('actions', function ($notification) {
                    $actions = '<div class="d-flex">';
                    $actions .= '<button type="button" class="view-notification btn btn-info btn-sm me-1" data-id="'.$notification->id.'" title="View Details"><i class="fa fa-eye"></i></button>';

                    if (Auth::user()->user_type === 'admin') {
                        $actions .= '<form action="' . route('notifications.destroy', $notification->id) . '" method="POST">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa fa-trash"></i></button>
                                    </form>';
                    } else {
                        $actions .= '<button type="button" class="remove-notification-btn btn btn-warning btn-sm" data-id="'.$notification->id.'" title="Remove"><i class="fa fa-times"></i></button>';
                    }
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['read_at', 'actions', 'message'])
                ->make(true);
        }
    }

    public function getRecentNotifications(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 3); // Default to 3 recent notifications

        $notifications = $user->notifications()
                              ->orderBy('created_at', 'desc')
                              ->limit($limit)
                              ->get();

        $unreadCount = $user->notifications()->whereNull('read_at')->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
    }
}
