<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReviewComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_review_id',
        'user_id',
        'comment',
    ];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, 'performance_review_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
