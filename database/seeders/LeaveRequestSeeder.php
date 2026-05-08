<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\User; // Import the User model
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing user IDs
        $userIds = User::pluck('id')->all();

        // If no users exist, create a default one or handle as appropriate
        if (empty($userIds)) {
            $user = User::firstOrCreate(
                ['email' => 'testuser@example.com'],
                [
                    'name' => 'Test User',
                    'password' => bcrypt('password'),
                    'user_type' => 'employee',
                    'employee_id' => null,
                    'duration' => 'full_day', // Default duration
                ]
            );
            $userIds[] = $user->id;
        }

        // Create 50 random leave requests, assigning a random existing user_id
        LeaveRequest::factory()->count(50)->create(function (array $attributes) use ($userIds) {
            return [
                'user_id' => $userIds[array_rand($userIds)],
            ];
        });

        // Remove the specific test user seeding as it's now covered by random assignment
        // and the firstOrCreate ensures a test user exists if no others.
    }
}