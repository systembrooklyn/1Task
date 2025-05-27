<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'valid_from',
        'valid_to',
        'max_uses',
        'used_count',
        'is_active'
    ];
    protected $dates = ['valid_from', 'valid_to'];

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'promo_code_plans');
    }

    public function usages()
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereNull('valid_from')
            ->orWhere('valid_from', '<=', now())
            ->whereNull('valid_to')
            ->orWhere('valid_to', '>=', now());
    }
}
