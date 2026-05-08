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

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure related data exists
        // Designations and Departments are handled statically, so we assume they exist or are handled externally.
        // We will fetch them if they exist, otherwise, we'll handle nulls or provide defaults.
        $departments = Department::all();
        $roles = Role::all();
        $dealerships = Dealership::all();
        $zones = Zone::all();

        // Check if essential related data (roles, dealerships, zones) exists
        if ($roles->isEmpty() || $dealerships->isEmpty() || $zones->isEmpty()) {
            echo "Missing essential related data (roles, dealerships, zones) for EmployeeSeeder. Please run their seeders first.\n";
            return;
        }

        // Provide default ID if departments are empty (for static handling)
        $defaultDepartmentId = $departments->isNotEmpty() ? $departments->random()->id : null;

        // Create a few initial employees (e.g., managers) to be reported to
        // $manager1User = User::firstOrCreate(
        //     ['email' => 'manager1@example.com'],
        //     [
        //         'name' => 'Manager One',
        //         'password' => Hash::make('password'),
        //         'user_type' => 'employee',
        //         'profile_pic' => null,
        //     ]
        // );

        // $manager1 = Employee::firstOrCreate(
        //     ['user_id' => $manager1User->id],
        //     [
        //         'employee_id' => 'EMP-001',
        //         'name' => 'Manager One',
        //         'email' => 'manager1@example.com',
        //         'password' => Hash::make('password'), // Default password
        //         'profile_pic' => null,
        //         'designation' => 'Manager', // Changed from designation_id
        //         'department_id' => $departments->isNotEmpty() ? $departments->random()->id : $defaultDepartmentId,
        //         'role_id' => $roles->random()->id,
        //         'dealership_id' => $dealerships->random()->id,
        //         'zone_id' => $zones->random()->id,
        //         'country' => 'USA',
        //         'mobile' => '111-222-3333',
        //         'gender' => 'Male',
        //         'joining_date' => '2020-01-15',
        //         'dob' => '1985-05-20',
        //         'reporting_to' => null, // This is a top-level manager
        //         'address' => '123 Main St, Anytown, USA',
        //     ]
        // );


        // $manager2User = User::firstOrCreate(
        //     ['email' => 'manager2@example.com'],
        //     [
        //         'name' => 'Manager Two',
        //         'password' => Hash::make('password'),
        //         'user_type' => 'employee',
        //         'profile_pic' => null,
        //     ]
        // );

        // $manager2 = Employee::firstOrCreate(
        //     ['user_id' => $manager2User->id],
        //     [
        //         'employee_id' => 'EMP-002',
        //         'name' => 'Manager Two',
        //         'email' => 'manager2@example.com',
        //         'password' => Hash::make('password'), // Default password
        //         'profile_pic' => null,
        //         'designation' => 'Manager', // Changed from designation_id
        //         'department_id' => $departments->isNotEmpty() ? $departments->random()->id : $defaultDepartmentId,
        //         'role_id' => $roles->random()->id,
        //         'dealership_id' => $dealerships->random()->id,
        //         'zone_id' => $zones->random()->id,
        //         'country' => 'Canada',
        //         'mobile' => '444-555-6666',
        //         'gender' => 'Female',
        //         'joining_date' => '2019-03-10',
        //         'dob' => '1980-11-01',
        //         'reporting_to' => null,
        //         'address' => '456 Oak Ave, Otherville, Canada',
        //     ]
        // );

        // Store employees to update reporting_to later
        $employeesToUpdate = [];

        // Create more employees without setting reporting_to initially
        for ($i = 1; $i <= 5; $i++) {
            $employeeUser = User::firstOrCreate(
                ['email' => 'employee' . $i . '@example.com'],
                [
                    'name' => 'Test Employee ' . $i,
                    'password' => Hash::make('password'),
                    'user_type' => 'employee',
                    'profile_pic' => null,
                ]
            );

            $employee = Employee::firstOrCreate(
                ['user_id' => $employeeUser->id],
                [
                    

                    'employee_id' => 'EMP-' . str_pad($i + 2, 3, '0', STR_PAD_LEFT), // +2 because manager1 and manager2 are EMP-001 and EMP-002
                    'name' => 'Employee ' . $i,
                    'email' => 'employee' . $i . '@example.com',
                    'password' => Hash::make('password'), // Default password
                    'profile_pic' => null,
                    'designation' => 'Software Engineer', // Changed from designation_id
                    'department_id' => $departments->isNotEmpty() ? $departments->random()->id : $defaultDepartmentId,
                    'role_id' => $roles->random()->id,
                    //dealership shall be only where the brand is 1
                    'dealership_id' => $dealerships->where('brand', 1)->random()->id,
                    'zone_id' => $zones->random()->id,
                    'country' => ($i % 2 == 0) ? 'USA' : 'Canada',
                    'mobile' => '555-123-456' . $i,
                    'gender' => ($i % 2 == 0) ? 'Female' : 'Male',
                    'joining_date' => now()->subDays(rand(100, 1000))->format('Y-m-d'),
                    'dob' => now()->subYears(rand(25, 50))->subDays(rand(1, 365))->format('Y-m-d'),
                    'reporting_to' => null, // Set to null initially
                    'address' => rand(1, 999) . ' Elm St, City ' . $i . ', Country',
                ]
            );
            $employeesToUpdate[] = $employee;
        }

        // Now update the reporting_to field for the created employees
        // foreach ($employeesToUpdate as $employee) {
        //     // Randomly assign reporting_to to one of the managers
        //     $reportingToManager = ($employee->id % 2 == 0) ? $manager1->id : $manager2->id;
        //     $employee->update(['reporting_to' => $reportingToManager]);
        // }
    }
}
