<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department; // Import the Department model

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::firstOrCreate(['name' => 'Sales']);
        Department::firstOrCreate(['name' => 'Parts']);
        Department::firstOrCreate(['name' => 'Service']);
        Department::firstOrCreate(['name' => 'Human Resources']);
        Department::firstOrCreate(['name' => 'Administration']);
        Department::firstOrCreate(['name' => 'HR']);
        Department::firstOrCreate(['name' => 'Audit']);
        Department::firstOrCreate(['name' => 'Accounts']);
        Department::firstOrCreate(['name' => 'Operations']);  
        Department::firstOrCreate(['name' => 'IT']);
        Department::firstOrCreate(['name' => 'Workshop']);
    }
}
