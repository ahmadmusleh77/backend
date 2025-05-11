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
    ];
}
