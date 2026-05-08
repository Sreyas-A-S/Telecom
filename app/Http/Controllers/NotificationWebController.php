<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationWebController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(15); // Fetch paginated notifications

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get a few recent notifications for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentNotifications(Request $request)
    {
        $userId = Auth::id();
        $limit = 5;

        // Fetch unread notifications first
        $unreadNotifications = Notification::where('user_id', $userId)
                                        ->whereNull('read_at')
                                        ->whereNull('hidden_at')
                                        ->latest()
                                        ->take($limit)
                                        ->get();

        $notifications = $unreadNotifications;

        // If we still need more notifications, fetch read ones
        if ($unreadNotifications->count() < $limit) {
            $readNotifications = Notification::where('user_id', $userId)
                                            ->whereNotNull('read_at')
                                            ->whereNull('hidden_at')
                                            ->latest()
                                            ->take($limit - $unreadNotifications->count())
                                            ->get();
            $notifications = $notifications->merge($readNotifications);
        }

        // Ensure unique and sorted by latest
        $notifications = $notifications->unique('id')->sortByDesc('created_at')->take($limit);

        return response()->json($notifications);
    }
}
