<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $primaryKey = 'review_id';

    protected $fillable = [
        'reviewer_id',
        'reviewee_id',
        'job_id',
        'rating',
        
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'user_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id', 'user_id');
    }

    public function jobPost()
    {
        return $this->belongsTo(Jobpost::class, 'job_id', 'job_id');
    }
}
