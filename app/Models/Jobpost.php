<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jobpost extends Model
{
    use HasFactory;

    protected $primaryKey = 'job_id';

    protected $fillable = [
        'title',
        'description',
        'budget',
        'location',
        'deadline',
        'user_id',
        'image',
        'status',
        'current_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class, 'job_id');
    }

    public function reviews()
    {
        return $this->hasOne(Review::class, 'job_id');
    }
}
