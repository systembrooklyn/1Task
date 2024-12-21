<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskRevision extends Model
{
    public $timestamps = false;
    protected $fillable = ['task_id','user_id','field','old_value','new_value','created_at'];

    public function task() { return $this->belongsTo(Task::class); }
    public function user() { return $this->belongsTo(User::class); }
}
