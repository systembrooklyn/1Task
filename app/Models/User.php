<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\NewResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new NewResetPasswordNotification($token));
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'fireToken',
        'company_id',
        'google_id',
        'is_deleted',
        'deleted_at'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'owners', 'owner_id', 'company_id');
    }
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_user');
    }

    public function managedDepartments()
    {
        return $this->hasMany(Department::class, 'user_id');
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
    public function projectsEdited()
    {
        return $this->hasMany(Project::class, 'edited_by');
    }

    public function projectsCreated()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function projectsLed()
    {
        return $this->hasMany(Project::class, 'leader_id');
    }
    public function createdTasks()
    {
        return $this->hasMany(DailyTask::class, 'created_by');
    }
    public function assignedPermissions()
    {
        return $this->roles->map->permissions->flatten()->unique();
    }

    public function hasAssignedPermission($permissionName)
    {
        return $this->assignedPermissions()->contains('name', $permissionName);
    }

    /**
     * Get the tasks assigned to the user.
     */
    public function assignedTasks()
    {
        return $this->hasMany(DailyTask::class, 'assigned_to');
    }
    public function submittedReports()
    {
        return $this->hasMany(DailyTaskReport::class, 'submitted_by');
    }

    public function taskComments()
    {
        return $this->belongsToMany(TaskComment::class, 'task_comment_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }
    public function taskCommentReplies()
    {
        return $this->belongsToMany(TaskCommentReply::class, 'task_comment_reply_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function profile()
    {
        return $this->hasOne(UsersProfile::class);
    }

    public function phones()
    {
        return $this->hasMany(UsersPhone::class);
    }

    public function links()
    {
        return $this->hasMany(UsersLink::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
        'google_id',
        'deleted_at',
        'is_deleted',
        'email_verified_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function getCreatedAtAttribute($value)
    {
        return $this->attributes['created_at'] ? \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->attributes['updated_at'] ? \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }
}
