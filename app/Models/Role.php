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
}
