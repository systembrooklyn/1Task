<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $fillable = ['name','company_id','user_id'];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'department_user');
    }
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_dept');
    }
    public function tasks()
    {
        return $this->hasMany(DailyTask::class, 'dept_id');  // Assuming 'dept_id' in 'tasks' table
    }
}
