<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use App\Models\Dealership;
use App\Models\Zone;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();
        $employeeRole = Role::where('role', 'Employee')->first();
        $dealerships = Dealership::all();
        $zones = Zone::all();

        if (!$employeeRole || $dealerships->isEmpty() || $zones->isEmpty()) {
            echo "Missing essential related data ('Employee' role, dealerships, or zones) for EmployeeSeeder2. Please run their seeders first.\n";
            return;
        }

        $defaultDepartmentId = $departments->isNotEmpty() ? $departments->random()->id : null;

        for ($i = 1; $i <= 5; $i++) {
            $email = 'test_employee' . $i . '@example.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Test Employee ' . $i,
                    'password' => Hash::make('password'),
                    'user_type' => 'employee',
                ]
            );

            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => 'EMP-S2-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => Hash::make('password'),
                    'designation' => 'Software Engineer',
                    'department_id' => $departments->isNotEmpty() ? $departments->random()->id : $defaultDepartmentId,
                    'role_id' => $employeeRole->id,
                    'dealership_id' => $dealerships->random()->id,
                    'zone_id' => $zones->random()->id,
                    'country' => 'India',
                    'mobile' => '987654321' . $i,
                    'gender' => ($i % 2 == 0) ? 'Female' : 'Male',
                    'joining_date' => now()->format('Y-m-d'),
                    'dob' => '1990-01-01',
                    'address' => 'Test Address ' . $i,
                ]
            );
        }
    }
}
