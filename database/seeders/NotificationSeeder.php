<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            echo "No users found. Please seed users first.\n";
            return;
        }

        foreach ($users as $user) {
            // Create 5 unread notifications
            for ($i = 0; $i < 5; $i++) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Unread Notification ' . ($i + 1) . ' for ' . $user->name,
                    'message' => 'This is an unread notification message for ' . $user->name . '.',
                    'data' => ['key' => 'value', 'id' => $i + 1],
                    'read_at' => null,
                ]);
            }

            // Create 3 read notifications
            for ($i = 0; $i < 3; $i++) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Read Notification ' . ($i + 1) . ' for ' . $user->name,
                    'message' => 'This is a read notification message for ' . $user->name . '.',
                    'data' => ['key' => 'value', 'id' => $i + 1],
                    'read_at' => Carbon::now()->subDays(rand(1, 7)),
                ]);
            }
        }
    }
}
