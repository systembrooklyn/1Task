<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class Task extends Model
// {
//     protected $fillable = [
//         'company_id', 'project_id', 'department_id', 'creator_user_id', 'assigned_user_id', 'consult_user_id' ,  'inform_user_id', 
//         'supervisor_user_id', 'title', 'description', 'start_date', 'deadline',  'priority', 'status'
//     ];

//     public function company() { return $this->belongsTo(Company::class); }
//     public function project() { return $this->belongsTo(Project::class); }
//     public function department() { return $this->belongsTo(Department::class); }
//     public function creator() { return $this->belongsTo(User::class, 'creator_user_id'); }
//     public function assignedUser() { return $this->belongsTo(User::class, 'assigned_user_id'); }
//     public function supervisor() { return $this->belongsTo(User::class, 'supervisor_user_id'); }
//     public function consult() { return $this->belongsTo(User::class, 'consult_user_id'); }
//     public function informer() { return $this->belongsTo(User::class, 'inform_user_id'); }

//     public function comments() { return $this->hasMany(TaskComment::class); }
//     public function attachments() { return $this->hasMany(TaskAttachment::class); }
//     public function userStatuses() { return $this->hasMany(TaskUserStatus::class); }
//     public function revisions() { return $this->hasMany(TaskRevision::class); }
// }




namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'company_id',
        'project_id',
        'department_id',
        'creator_user_id',
        'supervisor_user_id',
        'title', 'description', 'start_date', 'deadline', 'priority', 'status'
    ];

    public function creator() { return $this->belongsTo(User::class, 'creator_user_id'); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_user_id'); }

    public function company() { return $this->belongsTo(Company::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function assignedUser() { return $this->belongsTo(User::class, 'assigned_user_id'); }
    public function consult() { return $this->belongsTo(User::class, 'consult_user_id'); }
    public function informer() { return $this->belongsTo(User::class, 'inform_user_id'); }

    public function comments() { return $this->hasMany(TaskComment::class); }
    public function attachments() { return $this->hasMany(TaskAttachment::class); }
    public function userStatuses() { return $this->hasMany(TaskUserStatus::class); }
    public function revisions() { return $this->hasMany(TaskRevision::class); }
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function assignedUsers()
    {
        return $this->users()->wherePivot('role', 'assigned');
    }

    public function consultUsers()
    {
        return $this->users()->wherePivot('role', 'consult');
    }

    public function informerUsers()
    {
        return $this->users()->wherePivot('role', 'informer');
    }

    const ROLE_ASSIGNED = 'assigned';
    const ROLE_CONSULT = 'consult';
    const ROLE_INFORMER = 'informer';

    public static $validRoles = [
        self::ROLE_ASSIGNED,
        self::ROLE_CONSULT,
        self::ROLE_INFORMER,
    ];
}