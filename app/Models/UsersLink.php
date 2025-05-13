<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersLink extends Model
{
    protected $fillable = [
        'user_id', 'icon', 'link', 'link_name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
