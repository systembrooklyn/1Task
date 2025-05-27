<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    protected $fillable = ['plan_id', 'feature_id', 'value', 'resettable', 'reset_frequency'];
    public $incrementing = false;
    protected $keyType = 'int';
    protected $casts = [
        'resettable' => 'boolean'
    ];
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
