<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Helpers\TaskNumberGenerator;
use Illuminate\Support\Facades\Log;

class DailyTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_no',
        'task_name',
        'description',
        'start_date',
        'task_type',
        'recurrent_days',
        'day_of_month',
        'from',
        'to',
        'company_id',
        'dept_id',
        'project_id',
        'created_by',
        'assigned_to',
        'note',
        'status',
        'updated_by',
        'active',
        'submitted_by',
        'priority',
    ];

    protected $casts = [
        'recurrent_days' => 'array',
        'active' => 'boolean',
    ];

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
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
    public function revisions()
    {
        return $this->hasMany(DailyTaskRevision::class);
    }
    public function reports()
    {
        return $this->hasMany(DailyTaskReport::class);
    }
    public function todayReport()
    {
        return $this->hasOne(DailyTaskReport::class)
            ->whereDate('created_at', now()->toDateString());
    }
    public function getTodayReportStatusAttribute()
    {
        return $this->todayReport ? $this->todayReport->status : null;
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function evaluations()
    {
        return $this->hasMany(DailyTaskEvaluation::class);
    }
    protected static function booted()
    {
        static::creating(function ($task) {
            $task->task_no = TaskNumberGenerator::generateTaskNo($task->company_id);
        });

        static::updating(function ($task) {
            // Retrieve all changed attributes
            $changes = $task->getChanges();
            $original = $task->getOriginal();

            foreach ($changes as $field => $newValue) {
                if ($field === 'updated_at') {
                    continue; // Skip timestamps
                }

                $oldValue = $original[$field] ?? null;

                // Only log if the value has actually changed
                if ($oldValue !== $newValue) {
                    // Get the authenticated user ID
                    $userId = Auth::id();

                    // Ensure user ID is available
                    if (!$userId) {
                        Log::warning("Revision not logged for DailyTask ID {$task->id} because no authenticated user was found.");
                        continue;
                    }

                    // Create a revision record for each changed field
                    try {
                        $task->revisions()->create([
                            'user_id'    => $userId,
                            'field_name' => $field,
                            'old_value'  => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
                            'new_value'  => is_array($newValue) ? json_encode($newValue) : $newValue,
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to create revision for DailyTask ID {$task->id}: " . $e->getMessage());
                    }
                }
            }
        });
    }
}
