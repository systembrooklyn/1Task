<?php

namespace App\Services;

use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Illuminate\Support\Facades\Auth;

class PromoCodeService
{
    /**
     * Validate promo code against plan, expiry, and usage limits.
     *
     * @param string $code
     * @param int $companyId
     * @param int|null $planId
     * @return array
     */
    public function isValid($code, $companyId, $planId = null)
    {
        $now = now();
        $promo = PromoCode::where('code', $code)
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $now);
            })
            ->first();

        if (!$promo) {
            return ['valid' => false, 'message' => 'Promo code is invalid or expired.'];
        }
        if ($planId && !$promo->plans->contains('id', $planId)) {
            return ['valid' => false, 'message' => 'Promo code does not apply to this plan.'];
        }
        if ($promo->max_uses > 0 && $promo->used_count >= $promo->max_uses) {
            return ['valid' => false, 'message' => 'Promo code has reached its usage limit.'];
        }
        $alreadyUsed = PromoCodeUsage::where([
            ['promo_code_id', $promo->id],
            ['company_id', $companyId],
        ])->exists();

        if ($alreadyUsed) {
            return ['valid' => false, 'message' => 'You have already used this promo code.'];
        }

        return ['valid' => true, 'promo' => $promo];
    }

    /**
     * Apply promo code and track usage.
     *
     * @param PromoCode $promo
     * @param int $companyId
     * @return void
     */
    public function applyPromo($promo, $companyId)
    {
        PromoCodeUsage::create([
            'promo_code_id' => $promo->id,
            'company_id' => $companyId,
        ]);
        $promo->increment('used_count');
    }
}
