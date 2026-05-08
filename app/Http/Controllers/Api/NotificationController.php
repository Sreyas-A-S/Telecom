<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="API Endpoints for Notifications"
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/notifications",
     *     summary="Get a list of notifications for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             minimum=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of notifications per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             minimum=1,
     *             maximum=100
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Notification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 15);

        $notifications = $user->notifications()->paginate($perPage);

        return response()->json($notifications);
    }

    /**
     * @OA\Get(
     *     path="/notifications/recent",
     *     summary="Get a limited number of recent notifications for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of recent notifications to retrieve",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             minimum=1,
     *             default=5
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Notification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getRecentNotifications(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 5); // Default to 5 recent notifications

        $notifications = $user->notifications()
                              ->orderBy('created_at', 'desc')
                              ->limit($limit)
                              ->get();

        return response()->json($notifications);
    }

    /**
     * @OA\Get(
     *     path="/notifications/unread-count",
     *     summary="Get the count of unread notifications for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="unread_count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $unreadCount = $user->notifications()->whereNull('read_at')->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    /**
     * @OA\Post(
     *     path="/notifications/{id}/mark-as-read",
     *     summary="Mark a specific notification as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the notification to mark as read",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification marked as read.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found or does not belong to the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification not found.")
     *         )
     *     )
     * )
     */
    public function markAsRead(Notification $notification)
    {
        $user = Auth::user();

        if ($user->id !== $notification->user_id) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }
    

    /**
     * @OA\Post(
     *     path="/notifications/mark-all-as-read",
     *     summary="Mark all notifications as read for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All notifications marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="All notifications marked as read.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    /**
     * @OA\Delete(
     *     path="/notifications/{id}",
     *     summary="Delete a specific notification",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the notification to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found or does not belong to the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification not found.")
     *         )
     *     )
     * )
     */
    public function destroy(Notification $notification)
    {
        $user = Auth::user();

        if ($user->id !== $notification->user_id) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully.']);
    }
}
