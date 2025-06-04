<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{
    protected $fillable = ['id','name', 'company_id','guard_name','is_deleted','deleted_at'];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
