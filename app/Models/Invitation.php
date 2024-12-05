<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = ['inviter_id', 'email', 'token', 'is_accepted','expires_at'];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }
}
