<?php

namespace Database\Seeders;

use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoanRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $statuses = ['pending', 'approved', 'rejected', 'processed'];

        foreach ($users as $user) {
            for ($i = 0; $i < 2; $i++) { // Create 2 requests per user
                LoanRequest::create([
                    'user_id' => $user->id,
                    'amount' => rand(1000, 100000),
                    'status' => $statuses[array_rand($statuses)],
                    'requested_on' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
