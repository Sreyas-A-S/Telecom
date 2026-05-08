<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $leaveTypes = ['casual', 'sick', 'paid', 'unpaid'];
        $statuses = ['pending', 'approved', 'rejected', 'cancelled', 'cancelled by admin', 'approved and forwarded'];

        return [
            'user_id' => User::factory(),
            'leave_type' => $this->faker->randomElement($leaveTypes),
            'start_date' => $startDate = $this->faker->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween($startDate, (new \DateTime($startDate))->modify('+10 days'))->format('Y-m-d'),
            'status' => $this->faker->randomElement($statuses),
            'attachment' => null,
            'reason' => $this->faker->sentence(),
        ];
    }
}
