<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Dealership;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // First super admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@korps.com'],
            [
                'name' => 'Ganesh C V',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
            ]
        );

        $adminRole = Role::where('role', 'Admin')->first();
        if ($adminRole) {
            $adminUser->roles()->attach($adminRole);
        }

        // Second super admin
        $secondAdminUser = User::firstOrCreate(
            ['email' => 'nagarajan@korps.com'],
            [
                'name' => 'Nagarajan A',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
                'reporting_to' => $adminUser->id,
            ]
        );
        if ($adminRole) {
            $secondAdminUser->roles()->attach($adminRole);
        }

        $roles = Role::all();
        $dealerships = Dealership::with('zones')->get();
        $departments = Department::all();

        if ($dealerships->isEmpty()) {
            echo "No dealerships found. Please run DealershipSeeder first.\n";
            return;
        }

        if ($departments->isEmpty()) {
            echo "No departments found. Please run DepartmentSeeder first.\n";
            return;
        }

        foreach ($roles as $role) {
            if ($role->role === 'Admin') {
                continue;
            }

            $userType = (($role->role === 'service_manager' || $role->role === 'Service Manager') || $role->role === 'service_engineer') ? 'employee' : 'user';

            $user = User::firstOrCreate(
                ['email' => strtolower(str_replace(' ', '', $role->role)) . '@example.com'],
                [
                    'name' => $role->role . ' User',
                    'password' => Hash::make('password'),
                    'user_type' => $userType,
                ]
            );

            if ($userType === 'employee') {
                $dealership = $dealerships->random();
                $zone = $dealership->zones->random();
                $department = $departments->random();

                // Create an employee record and link it to the user
                $employee = \App\Models\Employee::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'email' => $user->email,
                        'name' => $user->name,
                        'password' => Hash::make('password'), // Provide a default password
                        'designation' => $role->role,
                        'department_id' => $department->id, // Corrected assignment
                        'role_id' => $role->id,
                        'mobile' => '1234567890', // Default mobile
                        'joining_date' => now(),
                        'employee_id' => 'EMP-' . strtoupper(Str::random(5)), // Generate a unique employee_id
                        'dealership_id' => $dealership->id,
                        'zone_id' => $zone->id,
                    ]
                );
                //save employee_id to users table
                // $user->employee_id = $employee->employee_id;
                // $user->save();
            }

            $user->roles()->attach($role);
        }
    }
}