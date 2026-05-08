<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'updated_by',
        'employee_id',
        'review_date',
        'review_period',
        'review_year',
        'final_report_pdf',
        'communication_skills_rating',
        'communication_skills_remarks',
        'technical_knowledge_rating',
        'technical_knowledge_remarks',
        'problem_solving_ability_rating',
        'problem_solving_ability_remarks',
        'teamwork_collaboration_rating',
        'teamwork_collaboration_remarks',
        'leadership_potential_rating',
        'leadership_potential_remarks',
        'adaptability_flexibility_rating',
        'adaptability_flexibility_remarks',
        'attitude_and_confidence_rating',
        'attitude_and_confidence_remarks',
        'punctuality_rating',
        'punctuality_remarks',
        'productivity_rating',
        'productivity_remarks',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function comments()
    {
        return $this->hasMany(PerformanceReviewComment::class);
    }
}
