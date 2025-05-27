<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUsage extends Model
{
    protected $fillable = ['company_id', 'feature_id', 'used', 'reset_date'];
    protected $dates = ['reset_date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
