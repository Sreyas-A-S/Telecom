<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait OneSignalNotificationTrait
{
    /**
     * Send a OneSignal notification to one or more users.
     *
     * @param User|iterable $recipients User instance or collection/array of User instances
     * @param string $title Notification heading
     * @param string $message Notification content
     * @param array $payloadData Extra data for the notification
     * @return void
     */
    public function sendOneSignalNotification($recipients, string $title, string $message, array $payloadData = [])
    {
        if ($recipients instanceof User) {
            $recipients = collect([$recipients]);
        }

        foreach ($recipients as $recipient) {
            try {
                // Generate a unique notification ID for our tracking
                do {
                    $notificationId = (string) Str::uuid();
                } while (Notification::where('notification_id', $notificationId)->exists());

                $payloadData['notification_id'] = $notificationId;

                // Prepare OneSignal target
                // We prefer player_id if available, fallback to external_id (email)
                $notificationPayload = [
                    'app_id' => env('ONESIGNAL_APP_ID'),
                    'headings' => ['en' => $title],
                    'contents' => ['en' => $message],
                    'data' => $payloadData,
                    'target_channel' => 'push',
                    'priority' => 10,
                    'android_visibility' => 1,
                ];

                if (!empty($recipient->player_id)) {
                    $notificationPayload['include_player_ids'] = [$recipient->player_id];
                } else {
                    $notificationPayload['include_aliases'] = [
                        'external_id' => [$recipient->email],
                    ];
                }

                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
                    'Content-Type' => 'application/json',
                ])->post('https://onesignal.com/api/v1/notifications', $notificationPayload);

                // Log the notification in the local database
                Notification::create([
                    'notification_id' => $notificationId,
                    'user_id' => $recipient->id,
                    'title' => $title,
                    'message' => $message,
                    'data' => $payloadData,
                ]);

                Log::info("OneSignal notification sent to {$recipient->email}.", [
                    'recipient_id' => $recipient->id,
                    'response' => $response->json(),
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send OneSignal notification to {$recipient->email}.", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
