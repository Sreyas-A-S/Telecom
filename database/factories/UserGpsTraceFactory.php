<?php

namespace Database\Factories;

use App\Models\UserGpsTrace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGpsTraceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserGpsTrace::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'visit_id' => $this->faker->numberBetween(1, 10), // Add a default visit_id for factory
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'recorded_at' => $this->faker->dateTimeThisMonth(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
