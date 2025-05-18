<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $primaryKey = 'setting_id';

    protected $fillable = [
        'user_id',
        'name',
        'country',
        'phone',
        'address',
        'birthday',
        'gender',
        'about',
        'languages',
        'email',
        'password',
        'visibility',
        'language',
        'theme',
        'skills',
        'experience',
        'education',
    ];

    protected $casts = [
        'skills' => 'array', 
        'birthday' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
