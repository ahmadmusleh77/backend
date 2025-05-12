<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class notification extends Model
{
    protected $primaryKey ='notification-id';
    protected $fillable=[
        'user_id',
        'content',
        'notification_type',
        'is_read'

    ];

    public $timestamps=true;
}
