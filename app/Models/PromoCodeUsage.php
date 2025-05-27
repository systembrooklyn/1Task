<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeUsage extends Model
{
    protected $fillable = ['promo_code_id', 'company_id'];
    protected $dates = ['used_at'];

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
