<?php

namespace Database\Factories;

use App\Models\Dealership;
use App\Models\Interview;
use Illuminate\Database\Eloquent\Factories\Factory;

class InterviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Interview::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'post_applied_for' => $this->faker->jobTitle,
            'dealership_id' => Dealership::inRandomOrder()->first()->id,
            'candidate_name' => $this->faker->name,
            'contact_number' => $this->faker->phoneNumber,
            'email_id' => $this->faker->unique()->safeEmail,
            'educational_qualification' => $this->faker->randomElement(['High School', 'Bachelors', 'Masters', 'PhD']),
            'years_of_experience' => $this->faker->numberBetween(0, 20),
            'current_employer' => $this->faker->company,
            'last_current_ctc' => $this->faker->numberBetween(200000, 2000000),
            'expected_ctc' => $this->faker->numberBetween(250000, 2500000),
            'notice_period' => $this->faker->numberBetween(0, 90),
            'communication_skills_rating' => $this->faker->numberBetween(1, 5),
            'communication_skills_remarks' => $this->faker->sentence,
            'technical_knowledge_rating' => $this->faker->numberBetween(1, 5),
            'technical_knowledge_remarks' => $this->faker->sentence,
            'problem_solving_ability_rating' => $this->faker->numberBetween(1, 5),
            'problem_solving_ability_remarks' => $this->faker->sentence,
            'knowledge_of_heavy_equipments_rating' => $this->faker->numberBetween(1, 5),
            'knowledge_of_heavy_equipments_remarks' => $this->faker->sentence,
            'relevant_work_experience_rating' => $this->faker->numberBetween(1, 5),
            'relevant_work_experience_remarks' => $this->faker->sentence,
            'attitude_and_confidence_rating' => $this->faker->numberBetween(1, 5),
            'attitude_and_confidence_remarks' => $this->faker->sentence,
            'adaptability_flexibility_rating' => $this->faker->numberBetween(1, 5),
            'adaptability_flexibility_remarks' => $this->faker->sentence,
            'teamwork_collaboration_rating' => $this->faker->numberBetween(1, 5),
            'teamwork_collaboration_remarks' => $this->faker->sentence,
            'leadership_potential_rating' => $this->faker->numberBetween(1, 5),
            'leadership_potential_remarks' => $this->faker->sentence,
            'willingness_to_travel_relocate_rating' => $this->faker->numberBetween(1, 5),
            'willingness_to_travel_relocate_remarks' => $this->faker->sentence,
            'interviewer_recommendation' => $this->faker->randomElement(['hire', 'do not hire', 'consider']),
            'salary_offered' => $this->faker->numberBetween(200000, 2500000),
            'da' => $this->faker->randomFloat(2, 0, 1000),
            'ta' => $this->faker->randomFloat(2, 0, 1000),
            'location' => $this->faker->city,
            'category' => $this->faker->word,
        ];
    }
}