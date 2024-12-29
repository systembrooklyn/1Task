<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTaskReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'daily_task_id',
        'submitted_by',
        'notes',
        'status',
    ];

    /**
     * Get the daily task that owns the report.
     */
    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class);
    }

    /**
     * Get the user who submitted the report.
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
