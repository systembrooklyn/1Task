<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Company extends Model
{
    protected $fillable = ['name'];

    public function owners()
    {
        return $this->belongsToMany(User::class, 'owners', 'company_id', 'owner_id');
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function departments()
    {
        return $this->hasMany(Department::class);
    }
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
