<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class DigitalCardUser extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'user_code', 'title', 'desc', 'profile_pic_url', 'back_pic_link'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Send the verification email notification
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification());
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            do {
                $randomCode = strtoupper(Str::random(8));
            } while (self::where('user_code', $randomCode)->exists());

            $model->user_code = $randomCode;
        });
    }
    public function socialLinks()
    {
        return $this->hasMany(DigitalCardSocialLink::class, 'user_id');
    }
    public function phones()
    {
        return $this->hasMany(DigitalCardUsersPhone::class, 'user_id');
    }
    
}
