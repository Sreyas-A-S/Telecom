<?php

namespace Database\Factories;

use App\Models\Followup;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Followup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'lead_id' => Lead::factory(), // Automatically create a lead if one doesn't exist
            'next_follow_up_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'new_status' => $this->faker->randomElement(['pending', 'in progress', 'win', 'lost', 'positive']),
            'remarks' => $this->faker->sentence,
        ];
    }
}