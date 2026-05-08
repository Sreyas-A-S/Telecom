<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadCategory;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Agent;
use App\Models\Dealership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class LeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $agentType = $this->faker->randomElement(['App\Models\Employee', 'App\Models\Agent']);
        $agentId = null;

        if ($agentType === 'App\Models\Employee') {
            // Assuming you have employees marked as brokers
            $employee = Employee::where('is_broker', 1)->inRandomOrder()->first();
            if ($employee) {
                $agentId = $employee->id;
            } else {
                // Fallback if no brokers exist, create one or use a regular employee
                $agentId = Employee::factory()->create(['is_broker' => 1])->id;
            }
        } else {
            $agent = Agent::inRandomOrder()->first();
            if ($agent) {
                $agentId = $agent->id;
            } else {
                $agentId = Agent::factory()->create()->id;
            }
        }

        $faker = Faker::create();
        $keralaDistricts = [
            'Alappuzha', 'Ernakulam', 'Idukki', 'Kannur', 'Kasaragod',
            'Kollam', 'Kottayam', 'Kozhikode', 'Malappuram', 'Palakkad',
            'Pathanamthitta', 'Thiruvananthapuram', 'Thrissur', 'Wayanad'
        ];

        return [
            'salutation' => $this->faker->randomElement(['Mr.', 'Mrs.', 'Ms.']),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone_number' => $this->faker->phoneNumber,
            'location' => function (array $attributes) use ($keralaDistricts) {
                return $keralaDistricts[array_rand($keralaDistricts)];
            },
            'dealership_id' => function () {
                // Assuming DealershipSeeder has run or dealerships exist
                return Dealership::inRandomOrder()->first()->id ?? Dealership::factory()->create()->id;
            },
            'user_id' => function () {
                // Assuming UserSeeder has run or users exist
                return User::inRandomOrder()->first()->id ?? User::factory()->create()->id;
            },
            'agent_id' => $agentId,
            'agent_type' => $agentType,
            'lead_source_id' => function () {
                // Assuming LeadSourceSeeder has run or lead sources exist
                return LeadSource::inRandomOrder()->first()->id ?? LeadSource::factory()->create()->id;
            },
            'lead_category_id' => function () {
                // Assuming LeadCategorySeeder has run or lead categories exist
                return LeadCategory::inRandomOrder()->first()->id ?? LeadCategory::factory()->create()->id;
            },
            'lead_value' => $this->faker->randomFloat(2, 100, 10000),
            'allow_follow_up' => $this->faker->boolean,
            'status' => $this->faker->randomElement(['pending', 'in progress', 'win', 'lost', 'positive']),
            'chance_of_success' => $this->faker->numberBetween(0, 100),
            'remarks' => $this->faker->sentence,
            'product_id' => function () {
                // Assuming ProductSeeder has run or products exist
                return Product::inRandomOrder()->first()->id ?? Product::factory()->create()->id;
            },
        ];
    }
}
