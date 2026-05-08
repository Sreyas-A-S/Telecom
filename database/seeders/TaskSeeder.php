<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Service;
use App\Models\Dealership;
use App\Models\Employee;
use Faker\Factory as Faker;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $users = User::all();
        $clients = Client::all();
        $services = Service::all();
        $dealerships = Dealership::all();
        $leads = \App\Models\Lead::all();
        $employees = Employee::all();

        if ($users->isEmpty() || $dealerships->isEmpty() || $employees->isEmpty()) {
            $this->command->info('Skipping TaskSeeder: No users, dealerships or employees found. Please run UserSeeder, DealershipSeeder and EmployeeSeeder first.');
            return;
        }

        for ($i = 0; $i < 20; $i++) { // Create 20 tasks
            $lead = $leads->isEmpty() ? null : $leads->random();
            Task::create([
                'title' => $faker->sentence(3),
                'type' => $faker->randomElement(['Installation', 'Repair', 'Maintenance', 'Inspection']),
                'description' => $faker->paragraph,
                'entry_id' => $services->isEmpty() ? null : $services->random()->id,
                'assigned_to' => $employees->random()->id,
                'dealership_id' => $dealerships->random()->id,
                'location' => $faker->address,
                'latitude' => $faker->latitude,
                'longitude' => $faker->longitude,
                'status' => $faker->randomElement(['pending', 'in_progress', 'completed']),
                'due_date' => $faker->dateTimeBetween('now', '+1 month'),
                'start_date_time' => $faker->dateTimeBetween('-1 week', 'now'),
                'end_date_time' => $faker->dateTimeBetween('now', '+1 week'),
                'sm_approved_early_action_date' => $faker->dateTimeBetween('-1 month', 'now'),
                'user_id' => $users->random()->id,
                'lead_id' => $lead ? $lead->id : null,
                'timer_started_at' => $faker->dateTimeBetween('-1 day', 'now'),
                'timer_paused_at' => $faker->dateTimeBetween('-1 day', 'now'),
                'total_elapsed_time' => $faker->numberBetween(0, 36000),
            ]);
        }
    }
}
