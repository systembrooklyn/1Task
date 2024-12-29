<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTaskRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_task_id',
        'user_id',
        'field_name',
        'old_value',
        'new_value',
    ];

    /**
     * Get the DailyTask associated with the revision.
     */
    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class);
    }

    /**
     * Get the User who made the revision.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
