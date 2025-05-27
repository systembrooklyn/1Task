<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['name', 'price', 'currency', 'is_active'];

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot(['value', 'resettable', 'reset_frequency']);
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function promoCodes()
    {
        return $this->belongsToMany(PromoCode::class, 'promo_code_plans');
    }

    public function getFeatureValueBySlug($slug)
    {
        return $this->features()
            ->where('slug', $slug)
            ->first()?->pivot ?? null;
    }
}
