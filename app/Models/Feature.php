<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $fillable = ['name', 'slug', 'unit', 'reset_frequency'];
    protected $unique = ['slug'];

    public function planFeatures()
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot(['value', 'resettable', 'reset_frequency']);
    }
}
