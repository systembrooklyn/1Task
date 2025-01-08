<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DigitalCardUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'desc',
        'name',
        'email',
        'profile_pic_url',
        'back_pic_link',
        'user_code',
    ];
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
