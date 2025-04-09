<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCommentReply extends Model
{
    protected $fillable = ['task_comment_id', 'user_id', 'reply_text'];

    public function taskComment()
    {
        return $this->belongsTo(TaskComment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_comment_reply_user')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }
}
