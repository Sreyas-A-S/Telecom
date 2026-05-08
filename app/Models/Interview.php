<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'interview_date',
        'status',
        'employee_id',
        'client_id',
        'dealership_id',
        'post_applied_for',
        'candidate_name',
        'contact_number',
        'email_id',
        'educational_qualification',
        'years_of_experience',
        'current_employer',
        'last_current_ctc',
        'expected_ctc',
        'notice_period',
        'communication_skills_rating',
        'communication_skills_remarks',
        'technical_knowledge_rating',
        'technical_knowledge_remarks',
        'problem_solving_ability_rating',
        'problem_solving_ability_remarks',
        'knowledge_of_heavy_equipments_rating',
        'knowledge_of_heavy_equipments_remarks',
        'relevant_work_experience_rating',
        'relevant_work_experience_remarks',
        'attitude_and_confidence_rating',
        'attitude_and_confidence_remarks',
        'adaptability_flexibility_rating',
        'adaptability_flexibility_remarks',
        'teamwork_collaboration_rating',
        'teamwork_collaboration_remarks',
        'leadership_potential_rating',
        'leadership_potential_remarks',
        'willingness_to_travel_relocate_rating',
        'willingness_to_travel_relocate_remarks',
        'interviewer_recommendation',
        'salary_offered',
        'da',
        'ta',
        'location',
        'category',
        'uuid',
        'job_vacancy_id',
        'referrer_id',
        'custom_form_responses',
        'resume',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $casts = [
        'interview_date' => 'datetime',
        'custom_form_responses' => 'array',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function dealership()
    {
        return $this->belongsTo(Dealership::class);
    }

    public function comments()
    {
        return $this->hasMany(InterviewComment::class);
    }

    public function jobVacancy()
    {
        return $this->belongsTo(JobVacancy::class);
    }
}
