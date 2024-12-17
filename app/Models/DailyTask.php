<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_no', 'task_name', 'description', 'start_date', 'task_type',
        'recurrent_days', 'day_of_month', 'from', 'to', 'company_id',
        'dept_id', 'created_by', 'assigned_to', 'note', 'status', 'updated_by'
    ];

    protected $casts = [
        'recurrent_days' => 'array', // Cast recurrent_days to an array for weekly tasks
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user the task is assigned to.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the department associated with the task.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Ensure the task number is unique and auto-generated
    public static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            $task->task_no = 'TASK-' . strtoupper(uniqid());
        });
    }
}
