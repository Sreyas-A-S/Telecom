<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserGpsTrace;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class UserGpsTraceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ensure a user exists for seeding GPS traces
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'GPS Trace User',
                'email' => 'gps.user@example.com',
                'password' => bcrypt('password'),
                'user_type' => 'employee',
            ]);
            Log::info('UserGpsTraceSeeder: Created dummy user.', ['userId' => $user->id]);
        }

        $userId = $user->id;

        Log::info('UserGpsTraceSeeder: Seeding GPS traces for userId.', ['userId' => $userId]);
      
        // Seed UserGpsTrace data for Visit 1
        $visitId1 = 1; // Use integer for visit_id
        $baseLat1 = 28.6139; // Base latitude for visit 1
        $baseLng1 = 77.2090; // Base longitude for visit 1
        $startTime1 = Carbon::now()->subHours(3);
        for ($i = 0; $i < 5; $i++) {
            UserGpsTrace::create([
                'user_id' => $userId,
                'visit_id' => $visitId1,
                'latitude' => $baseLat1 + (mt_rand(-100, 100) / 10000.0),
                'longitude' => $baseLng1 + (mt_rand(-100, 100) / 10000.0),
                'recorded_at' => $startTime1->addMinutes(5)->toDateTimeString(),
            ]);
        }

        // Seed UserGpsTrace data for Visit 2
        $visitId2 = 2; // Use integer for visit_id
        $baseLat2 = 28.7041; // Base latitude for visit 2
        $baseLng2 = 77.1025; // Base longitude for visit 2
        $startTime2 = Carbon::now()->subHours(1);
        for ($i = 0; $i < 7; $i++) {
            UserGpsTrace::create([
                'user_id' => $userId,
                'visit_id' => $visitId2,
                'latitude' => $baseLat2 + (mt_rand(-100, 100) / 10000.0),
                'longitude' => $baseLng2 + (mt_rand(-100, 100) / 10000.0),
                'recorded_at' => $startTime2->addMinutes(7)->toDateTimeString(),
            ]);
        }
    }
}
