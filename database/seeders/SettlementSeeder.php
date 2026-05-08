<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Settlement;
use Illuminate\Database\Seeder;

class SettlementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeIds = Employee::pluck('id')->toArray();

        if (empty($employeeIds)) {
            $this->command->info('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        Settlement::factory()->count(50)->create()->each(function ($settlement) use ($employeeIds) {
            $settlement->remarks()->createMany(
                \App\Models\SettlementRemark::factory()->count(rand(1, 3))->make([
                    'manager_id' => fake()->randomElement($employeeIds),
                ])->toArray()
            );
        });
    }
}
