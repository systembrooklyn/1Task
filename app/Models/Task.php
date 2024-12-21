<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'company_id', 'project_id', 'department_id', 'creator_user_id', 'assigned_user_id', 
        'supervisor_user_id', 'title', 'description', 'start_date', 'deadline', 
        'is_urgent', 'priority', 'status'
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function creator() { return $this->belongsTo(User::class, 'creator_user_id'); }
    public function assignedUser() { return $this->belongsTo(User::class, 'assigned_user_id'); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_user_id'); }

    public function comments() { return $this->hasMany(TaskComment::class); }
    public function attachments() { return $this->hasMany(TaskAttachment::class); }
    public function userStatuses() { return $this->hasMany(TaskUserStatus::class); }
    public function revisions() { return $this->hasMany(TaskRevision::class); }
}
