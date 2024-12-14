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
        'edited_by',
        'company_id',
        'leader_id',
        'dept_id',
        'created_by',
    ];

    protected $appends = [
        'company_name',
        'department_name',
        'created_by_name',
        'edited_by_name',
        'leader_name'
    ];

    // Define relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id')->select('id', 'name');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id')->select('id', 'name');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->select('id', 'name');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by')->select('id', 'name');
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id')->select('id', 'name');
    }

    public function getCompanyNameAttribute()
    {
        return $this->company ? $this->company->name : null;
    }

    public function getDepartmentNameAttribute()
    {
        return $this->department ? $this->department->name : null;
    }

    public function getCreatedByNameAttribute()
    {
        return $this->createdBy ? $this->createdBy->name : null;
    }

    public function getEditedByNameAttribute()
    {
        return $this->editedBy ? $this->editedBy->name : null;
    }

    public function getLeaderNameAttribute()
    {
        return $this->leader ? $this->leader->name : null;
    }
}
