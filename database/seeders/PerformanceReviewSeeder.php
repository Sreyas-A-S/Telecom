<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PerformanceReview;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class PerformanceReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees
        $employees = Employee::whereNotNull('user_id')->get();

        if ($employees->count() < 2) {
            $this->command->info('Not enough employees to seed performance reviews. Need at least 2.');
            return;
        }

        // Pick one employee to be the manager
        $manager = $employees->first();
        
        // Assign the rest to report to this manager if they don't have a manager
        $subordinates = $employees->where('id', '!=', $manager->id);
        
        foreach ($subordinates as $subordinate) {
            if (!$subordinate->reporting_to) {
                $subordinate->update(['reporting_to' => $manager->id]);
            }
        }

        // Refresh subordinates list to ensure we have the updated data
        $subordinates = Employee::where('reporting_to', $manager->id)->whereNotNull('user_id')->get();

        foreach ($subordinates as $employee) {
            $reviewerId = $manager->user_id;
            $employeeId = $employee->user_id;

            // Create a review for the last quarter
            PerformanceReview::create([
                'reviewer_id' => $reviewerId,
                'employee_id' => $employeeId,
                'review_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'review_period' => 'Q' . Carbon::now()->subMonths(1)->quarter . ' ' . Carbon::now()->subMonths(1)->year,
                'communication_skills_rating' => rand(3, 5),
                'communication_skills_remarks' => 'Good communication skills.',
                'technical_knowledge_rating' => rand(3, 5),
                'technical_knowledge_remarks' => 'Strong technical understanding.',
                'problem_solving_ability_rating' => rand(3, 5),
                'problem_solving_ability_remarks' => 'Effective problem solver.',
                'teamwork_collaboration_rating' => rand(3, 5),
                'teamwork_collaboration_remarks' => 'Works well with the team.',
                'leadership_potential_rating' => rand(2, 5),
                'leadership_potential_remarks' => 'Shows potential for leadership.',
                'adaptability_flexibility_rating' => rand(3, 5),
                'adaptability_flexibility_remarks' => 'Adaptable to changes.',
                'attitude_and_confidence_rating' => rand(3, 5),
                'attitude_and_confidence_remarks' => 'Positive attitude.',
                'punctuality_rating' => rand(3, 5),
                'punctuality_remarks' => 'Always punctual.',
                'productivity_rating' => rand(3, 5),
                'productivity_remarks' => 'High productivity.',
            ]);
        }
    }
}
