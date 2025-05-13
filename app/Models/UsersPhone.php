<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersPhone extends Model
{
    protected $fillable = [
        'user_id', 'CC', 'phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
