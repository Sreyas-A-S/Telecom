<?php

namespace Database\Factories;

use App\Models\Dealership;
use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class PipelineFactory extends Factory
{
    protected $model = Pipeline::class;

    public function definition(): array
    {
        return [
            'dealership_id' => Dealership::inRandomOrder()->first()->id,
            'source' => $this->faker->word,
            'inquiry_received_date' => $this->faker->date(),
            'date' => $this->faker->date(),
            'customer_name' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber,
            'location' => $this->faker->city,
            'model' => $this->faker->word,
            'quantity' => $this->faker->numberBetween(1, 5),
            'probability' => $this->faker->randomElement(['100%', '75%', '50%', '30%']),
            'financier' => $this->faker->company,
            'type' => $this->faker->randomElement(['FTB', 'FTU', 'Retail', 'Strategic']),
            'login_status' => $this->faker->word,
            'stage' => $this->faker->word,
            'remarks' => $this->faker->sentence,
            'billing' => $this->faker->word,
            'plan' => $this->faker->word,
            'month' => $this->faker->monthName() . ' ' . $this->faker->year(),
            'current_status' => $this->faker->word,
        ];
    }
}