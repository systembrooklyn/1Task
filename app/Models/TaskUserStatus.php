<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskUserStatus extends Model
{
    protected $fillable = ['task_id', 'user_id', 'is_archived', 'is_starred'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
