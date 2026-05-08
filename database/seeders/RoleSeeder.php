<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['role' => 'ceo']);
        Role::firstOrCreate(['role' => 'Sales Manager']);
         Role::firstOrCreate(['role' => 'Sales Engineer']);
        Role::firstOrCreate(['role' => 'service_manager']);
        Role::firstOrCreate(['role' => 'service_engineer']);
        Role::firstOrCreate(['role' => 'user']);
    }
}
