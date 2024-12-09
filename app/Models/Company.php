<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Company extends Model
{
    protected $fillable = ['name'];

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
}
