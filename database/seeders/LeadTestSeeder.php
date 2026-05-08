<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Dealership;
use App\Models\User;
use App\Models\LeadSource;
use App\Models\LeadCategory;
use App\Models\Employee;
use App\Models\Followup;
use App\Models\Task;
use App\Models\FSRReport;
use App\Models\TaskFollowup;
use App\Models\TaskLog;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeadTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        $keralaDistricts = [
            'Alappuzha', 'Ernakulam', 'Idukki', 'Kannur', 'Kasaragod',
            'Kollam', 'Kottayam', 'Kozhikode', 'Malappuram', 'Palakkad',
            'Pathanamthitta', 'Thiruvananthapuram', 'Thrissur', 'Wayanad'
        ];

        $dealerships = Dealership::all();
        $users = User::all();
        $sources = LeadSource::all();
        $categories = LeadCategory::all();
        $products = Product::all();
        $employees = Employee::all();

        if ($dealerships->isEmpty() || $users->isEmpty() || $sources->isEmpty() || $categories->isEmpty() || $products->isEmpty() || $employees->isEmpty()) {
            $this->command->error('Required base data missing. Please run base seeders first.');
            return;
        }

        $this->command->info('Cleaning up old test data (Leads, Followups, Tasks, FSRs)...');
        
        // Comprehensive cleanup
        DB::table('fsr_reports')->delete();
        DB::table('task_followups')->delete();
        DB::table('task_logs')->delete();
        DB::table('tasks')->delete();
        DB::table('followups')->delete();
        DB::table('lead_items')->delete();
        Lead::query()->delete();

        $this->command->info('Seeding 10 fresh test leads with items, followups, and tasks...');

        for ($i = 0; $i < 10; $i++) {
            $employee = $employees->random();
            $user = $users->random();
            $primaryProduct = $products->random();
            $primaryModel = ProductModel::where('product_id', $primaryProduct->id)->inRandomOrder()->first();

            $lead = Lead::create([
                'salutation' => $faker->randomElement(['Mr.', 'Mrs.', 'Ms.']),
                'name' => $faker->name,
                'company' => $faker->company,
                'email' => $faker->unique()->safeEmail,
                'phone_number' => $faker->phoneNumber,
                'alternate_contact_number' => $faker->phoneNumber,
                'location' => $faker->randomElement($keralaDistricts),
                'lead_source_id' => $sources->random()->id,
                'lead_category_id' => $categories->random()->id,
                'lead_value' => 0, // Updated by items
                'allow_follow_up' => true,
                'status' => $faker->randomElement(['pending', 'in progress', 'win', 'lost', 'positive']),
                'chance_of_success' => $faker->numberBetween(10, 90),
                'product_id' => $primaryProduct->id,
                'product_model_id' => $primaryModel ? $primaryModel->id : null,
                'quantity' => $faker->numberBetween(1, 3),
                'dealership_id' => $employee->dealership_id ?? $dealerships->random()->id,
                'user_id' => $user->id,
                'employee_id' => $employee->id,
                'created_at' => Carbon::now()->subDays(rand(5, 30)),
            ]);

            // 1. Add Multiple Items (40% chance)
            $itemCount = $faker->boolean(40) ? rand(2, 3) : 1; 
            $totalValue = 0;
            for ($j = 0; $j < $itemCount; $j++) {
                $p = $products->random();
                $m = ProductModel::where('product_id', $p->id)->inRandomOrder()->first();
                $qty = rand(1, 2);
                $price = $p->price ?? rand(10000, 50000);
                $lead->items()->create([
                    'product_id' => $p->id,
                    'product_model_id' => $m ? $m->id : null,
                    'quantity' => $qty,
                    'price' => $price,
                ]);
                $totalValue += ($qty * $price);
            }
            $lead->update(['lead_value' => $totalValue]);

            // 2. Add Lead Follow-ups
            $followupCount = rand(1, 3);
            for ($j = 0; $j < $followupCount; $j++) {
                Followup::create([
                    'lead_id' => $lead->id,
                    'user_id' => $users->random()->id,
                    'next_follow_up_date' => Carbon::parse($lead->created_at)->addDays($j + 1),
                    'new_status' => $lead->status,
                    'remarks' => 'Follow up #' . ($j + 1) . ': ' . $faker->sentence,
                    'created_at' => Carbon::parse($lead->created_at)->addDays($j),
                ]);
            }

            // 3. Create Associated Task (70% chance)
            if ($faker->boolean(70)) {
                $taskStatus = ($lead->status === 'win') ? 'completed' : $faker->randomElement(['pending', 'in_progress', 'hold']);
                
                $task = Task::create([
                    'title' => 'Task for ' . $lead->name,
                    'type' => 'client_based',
                    'description' => 'Initial inquiry and machine demonstration for ' . $lead->company,
                    'assigned_to' => $lead->employee_id,
                    'dealership_id' => $lead->dealership_id,
                    'location' => $lead->location,
                    'latitude' => $faker->latitude,
                    'longitude' => $faker->longitude,
                    'status' => $taskStatus,
                    'due_date' => Carbon::now()->addDays(rand(-2, 10)),
                    'user_id' => $lead->user_id,
                    'lead_id' => $lead->id,
                    'is_service' => 0,
                    'total_elapsed_time' => rand(1800, 7200), // 30m - 2h
                    'created_at' => Carbon::parse($lead->created_at)->addHour(),
                ]);

                // Initial Task Log
                TaskLog::create([
                    'task_id' => $task->id,
                    'employee_id' => $lead->employee_id,
                    'action_type' => 'assigned',
                    'action_time' => $task->created_at,
                ]);

                // 4. Add Task Follow-ups
                if ($taskStatus !== 'pending') {
                    for ($k = 0; $j < rand(1, 3); $j++) {
                        TaskFollowup::create([
                            'task_id' => $task->id,
                            'user_id' => $users->random()->id,
                            'notes' => 'Work progress update: ' . $faker->sentence,
                            'latitude' => $task->latitude,
                            'longitude' => $task->longitude,
                            'created_at' => Carbon::parse($task->created_at)->addHours($k + 1),
                        ]);
                    }
                }

                // 5. Create FSR if task is completed
                if ($taskStatus === 'completed') {
                    FSRReport::create([
                        'task_id' => $task->id,
                        'on_site_assessment' => 'Lead demo successful. Client impressed with ' . $primaryProduct->name,
                        'analysis_of_cause' => 'Standard pre-sales demonstration and requirement gathering.',
                        'actions_taken' => 'Demonstrated machine capabilities, provided quote, and discussed financing.',
                        'submitted_by_user_id' => $lead->user_id,
                        'status' => 'approved',
                        'payment_status' => 'pending',
                        'created_at' => Carbon::parse($task->created_at)->addDays(1),
                    ]);
                }
            }
        }

        $this->command->info('10 test leads with all relationships seeded successfully!');
    }
}
