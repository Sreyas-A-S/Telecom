<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\SettlementRemark;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettlementRemarkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SettlementRemark::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'department' => $this->faker->randomElement(['HR', 'Finance', 'Operations', 'Sales']),
            'remark' => $this->faker->optional()->sentence,
            'signature' => $this->faker->optional()->imageUrl(), // Placeholder for signature image URL
            'is_filled' => $this->faker->boolean,
            'manager_id' => Employee::inRandomOrder()->first()->id,
        ];
    }
}
