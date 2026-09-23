<?php

namespace App\Modules\Task\Models;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $fillable = ['task_id', 'uploaded_by_user_id', 'file_path', 'file_size', 'file_name', 'download_url', 'is_main'];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }
    public function scopeRegular($query)
    {
        return $query->where('is_main', false);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}