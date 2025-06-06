<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $primaryKey = 'bids_id';
    protected $fillable = ['artisan_id', 'job_id','user_name', 'price_estimate', 'timeline', 'status'];

    public function artisan()
    {
        return $this->belongsTo(User::class, 'artisan_id','user_id');

    }
    public function user()
{
    return $this->belongsTo(User::class);
}

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_id');
    }



}

