<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersProfile extends Model
{
    protected $fillable = [
        'user_id', 'ppUrl', 'ppPath', 'position', 'country', 'city', 'state', 'ppSize'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
