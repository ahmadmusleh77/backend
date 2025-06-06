<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey ='id';

    public $timestamps=true;
    protected $fillable=[
        'user_id',
        'data',
        'type',
        'is_read'

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
