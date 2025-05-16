<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $table = 'users';

    protected $primaryKey = 'user_id';
    protected $fillable = [
        'name',
        'user_type',
        'email',
        'password',
        'role_id',
        'status',
    ];
public function role()
{
    return $this->belongsTo(Role::class,'role_id');
}

public function jobposts()
{
return $this->hasMany(Jobpost::class,'user_id');
}


public function bids()
{
return $this->hasMany(Bid::class,'artisan_id');
}

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }


    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }













    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
