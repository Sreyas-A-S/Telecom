<?php

namespace Database\Factories;

use App\Models\Dealership;
use App\Models\Settlement;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettlementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Settlement::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'employee_code' => $this->faker->unique()->numerify('EMP-####'),
            'employee_name' => $this->faker->name,
            'age' => $this->faker->numberBetween(20, 60),
            'department' => $this->faker->jobTitle,
            'head_office_branch' => $this->faker->city,
            'designation' => $this->faker->jobTitle,
            'date_of_joining' => $this->faker->date(),
            'date_of_resignation' => $this->faker->optional()->date(),
            'reason_for_resignation' => $this->faker->optional()->sentence,
            'dealership_id' => Dealership::inRandomOrder()->first()->id,
        ];
    }
}
