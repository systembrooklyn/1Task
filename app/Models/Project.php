<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'desc',
        'status',
        'deadline',
        'company_id',
        'created_by',
        'leader_id',
        'edited_by',
        'start_date'
    ];

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'project_dept');
    }

    // Define the relationship with the company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Define the relationship with the createdBy (user)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Define the relationship with the editedBy (user)
    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // Define the relationship with the project leader
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    // Accessor to get the department name for response
    public function getDepartmentNameAttribute()
    {
        return $this->departments->isEmpty() ? [] : $this->departments->pluck('name')->toArray();
    }

    // Accessor to get the company name for response
    public function getCompanyNameAttribute()
    {
        return $this->company ? $this->company->name : 'No Company Assigned';
    }

    // Accessor to get the created_by name for response
    public function getCreatedByNameAttribute()
    {
        return $this->createdBy ? $this->createdBy->name : 'No Creator Assigned';
    }

    // Accessor to get the edited_by name for response
    public function getEditedByNameAttribute()
    {
        return $this->editedBy ? $this->editedBy->name : null;
    }

    // Accessor to get the leader name for response
    public function getLeaderNameAttribute()
    {
        return $this->leader ? $this->leader->name : 'No Leader Assigned';
    }
    public function revisions()
    {
        return $this->hasMany(ProjectRevision::class);
    }
    public function dailyTasks()
    {
        return $this->hasMany(DailyTask::class, 'project_id');
    }
    // Modify attributes to append in the response
    protected $appends = [
        'company_name',
        'department_name',
        'created_by_name',
        'edited_by_name',
        'leader_name',
    ];
}
