<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExpenseRequest;
use App\Models\User;

class ExpenseRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there are users to associate with expense requests
        if (User::count() === 0) {
            $this->call(UserSeeder::class);
        }

        $users = User::all();

        if ($users->isEmpty()) {
            echo "No users found to associate with expense requests. Please run UserSeeder first.\n";
            return;
        }

        $statuses = ['pending', 'approved', 'rejected'];
        $expenseTypes = ['Travel', 'Food', 'Accommodation', 'Office Supplies', 'Miscellaneous'];

        foreach ($users as $user) {
            // Create 2-5 expense requests for each user
            for ($i = 0; $i < rand(2, 5); $i++) {
                ExpenseRequest::create([
                    'user_id' => $user->id,
                    'expense_type' => $expenseTypes[array_rand($expenseTypes)],
                    'amount' => rand(100, 5000) / 100, // Random amount between 1.00 and 50.00
                    'date' => now()->subDays(rand(1, 30))->format('Y-m-d'), // Random date within the last 30 days
                    'status' => $statuses[array_rand($statuses)],
                    // 'attachment' => null, // Assuming attachment is nullable
                ]);
            }
        }
    }
}
