<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersProfile extends Model
{
    protected $fillable = [
        'user_id', 'ppUrl', 'position', 'country', 'city', 'state'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
