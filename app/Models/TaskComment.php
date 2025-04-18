<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    protected $fillable = ['task_id', 'user_id', 'comment_text'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function replies()
    {
        return $this->hasMany(TaskCommentReply::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_comment_user')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }
}
