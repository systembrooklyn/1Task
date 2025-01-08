<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{
    protected $fillable = ['id','name', 'company_id','guard_name'];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }



    
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($role) {
            if ($role->getOriginal('name') === 'agent') {
                abort(403, 'You cannot modify the "agent" role.');
            }
        });

        static::deleting(function ($role) {
            if ($role->name === 'agent') {
                abort(403, 'You cannot delete the "agent" role.');
            }
        });
    }
    public function syncPermissions($permissions)
    {
        if ($this->name === 'agent') {
            abort(403, 'You cannot modify permissions of the "agent" role.');
        }

        return parent::syncPermissions($permissions);
    }

    public function givePermissionTo(...$permissions)
    {
        if ($this->name === 'agent') {
            abort(403, 'You cannot modify permissions of the "agent" role.');
        }

        return parent::givePermissionTo(...$permissions);
    }

    public function revokePermissionTo(...$permissions)
    {
        if ($this->name === 'agent') {
            abort(403, 'You cannot modify permissions of the "agent" role.');
        }

        return parent::revokePermissionTo(...$permissions);
    }
}
