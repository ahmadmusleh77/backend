<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $primaryKey = 'bids_id';
    protected $fillable = ['artisan_id', 'job_post_id', 'price_estimate', 'timeline', 'status'];
}
