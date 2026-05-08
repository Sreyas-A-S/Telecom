<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FSRReport;
use App\Models\Task;
use App\Models\User;
use Faker\Factory as Faker;

class FSRReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $tasks = Task::all();
        $users = User::all();

        if ($tasks->isEmpty() || $users->isEmpty()) {
            $this->command->info('Skipping FSRReportSeeder: No tasks or users found. Please run TaskSeeder and UserSeeder first.');
            return;
        }

        foreach ($tasks as $task) {
            FSRReport::create([
                'task_id' => $task->id,
                'on_site_assessment' => $faker->paragraph,
                'analysis_of_cause' => $faker->paragraph,
                'actions_taken' => $faker->paragraph,
                'submitted_by_user_id' => $users->random()->id,
            ]);
        }
    }
}
