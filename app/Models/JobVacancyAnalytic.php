<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JobVacancy;
use App\Models\User;

class JobVacancyAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_vacancy_id',
        'user_id',
        'referrer_id',
        'action',
        'ip_address',
        'user_agent',
    ];

    public function jobVacancy()
    {
        return $this->belongsTo(JobVacancy::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
