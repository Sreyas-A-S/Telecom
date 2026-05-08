<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $roles = Role::all();

        // Assign random roles to users with is_active true
        foreach ($users as $user) {
            $user->roles()->attach($roles->random()->id, ['is_active' => true]);
        }

        // Example: Assign 'admin' role to the first user with is_active true
        $adminRole = Role::where('role', 'admin')->first();
        if ($adminRole) {
            $firstUser = User::first();
            if ($firstUser) {
                $firstUser->roles()->syncWithoutDetaching([$adminRole->id => ['is_active' => true]]);
            }
        }

        // Example: Assign 'editor' role to the second user with is_active false
        $editorRole = Role::where('role', 'editor')->first();
        if ($editorRole) {
            $secondUser = User::skip(1)->first();
            if ($secondUser) {
                $secondUser->roles()->syncWithoutDetaching([$editorRole->id => ['is_active' => false]]);
            }
        }
    }
}
