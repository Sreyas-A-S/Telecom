<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use App\Models\Employee;
use App\Models\Dealership;
use App\Models\FSRReport;
use App\Models\TaskFollowup;
use App\Models\TaskLog;
use App\Models\Lead;
use App\Models\Service;
use Faker\Factory as Faker;
use Carbon\Carbon;

class TaskReportTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder creates specific scenarios to test the Task Report module.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Ensure we have some base data
        $employees = Employee::all();
        $dealerships = Dealership::all();
        $users = User::all();
        $leads = Lead::all();
        $services = Service::all();

        if ($employees->isEmpty() || $dealerships->isEmpty() || $users->isEmpty()) {
            $this->command->error('Base data missing. Please run DealershipSeeder, EmployeeSeeder, and UserSeeder first.');
            return;
        }

        $this->command->info('Creating test tasks for reporting...');

        // 1. Pending Task
        $this->createTaskScenario($faker, 'pending', 'Standard Pending Task', $employees, $dealerships, $users);

        // 2. Ongoing (In Progress) Task
        $this->createTaskScenario($faker, 'in_progress', 'Ongoing Service Task', $employees, $dealerships, $users, true);

        // 3. On Hold Task
        $this->createTaskScenario($faker, 'hold', 'Task on Hold for Parts', $employees, $dealerships, $users);

        // 4. Completed Task - Approved
        $task = $this->createTaskScenario($faker, 'completed', 'Completed & Approved Repair', $employees, $dealerships, $users, true);
        $this->createFSR($faker, $task, 'approved', 'paid');

        // 5. Completed Task - Awaiting Approval
        $task = $this->createTaskScenario($faker, 'completed', 'Completed Awaiting Approval', $employees, $dealerships, $users, true);
        $this->createFSR($faker, $task, 'pending', 'pending');

        // 6. Completed Task - Rejected
        $task = $this->createTaskScenario($faker, 'completed', 'Completed but Rejected', $employees, $dealerships, $users, true);
        $this->createFSR($faker, $task, 'rejected', 'pending');

        // 7. Completed Task - Settled (Paid)
        $task = $this->createTaskScenario($faker, 'completed', 'Fully Settled Maintenance', $employees, $dealerships, $users, true);
        $this->createFSR($faker, $task, 'approved', 'paid');

        // 8. Lead Based Task
        $leadTask = $this->createTaskScenario($faker, 'pending', 'New Lead Followup', $employees, $dealerships, $users);
        if ($leads->isNotEmpty()) {
            $leadTask->update(['lead_id' => $leads->random()->id, 'is_service' => 0]);
        }

        // 9. Task with multiple followups
        $task = $this->createTaskScenario($faker, 'in_progress', 'Task with Work History', $employees, $dealerships, $users);
        for ($i = 0; $i < 3; $i++) {
            TaskFollowup::create([
                'task_id' => $task->id,
                'user_id' => $users->random()->id,
                'notes' => $faker->sentence(10),
                'latitude' => $task->latitude,
                'longitude' => $task->longitude,
            ]);
        }

        $this->command->info('Task Report test data seeded successfully!');
    }

    private function createTaskScenario($faker, $status, $title, $employees, $dealerships, $users, $isService = false)
    {
        $employee = $employees->random();
        $task = Task::create([
            'title' => $title,
            'type' => $faker->randomElement(['client_based', 'open']),
            'description' => $faker->paragraph,
            'assigned_to' => $employee->id,
            'dealership_id' => $employee->dealership_id ?? $dealerships->random()->id,
            'location' => $faker->address,
            'latitude' => $faker->latitude,
            'longitude' => $faker->longitude,
            'status' => $status,
            'due_date' => Carbon::now()->addDays(rand(-5, 5)),
            'user_id' => $users->random()->id,
            'is_service' => $isService ? 1 : 0,
            'total_elapsed_time' => rand(3600, 18000), // 1-5 hours
            'created_at' => Carbon::now()->subDays(rand(1, 10)),
        ]);

        // Create some logs
        TaskLog::create([
            'task_id' => $task->id,
            'employee_id' => $employee->id,
            'action_type' => 'created',
            'action_time' => $task->created_at,
        ]);

        return $task;
    }

    private function createFSR($faker, $task, $status, $paymentStatus)
    {
        return FSRReport::create([
            'task_id' => $task->id,
            'on_site_assessment' => 'Assessment for ' . $task->title . ': ' . $faker->paragraph,
            'analysis_of_cause' => 'Cause Analysis: ' . $faker->paragraph,
            'actions_taken' => 'Actions Taken: ' . $faker->paragraph,
            'submitted_by_user_id' => $task->user_id,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'created_at' => $task->created_at->addHours(2),
        ]);
    }
}
