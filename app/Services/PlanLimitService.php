<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyUsage;
use App\Models\Feature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PlanLimitService
{
    /**
     * Check feature access and track usage dynamically
     *
     * @param int $companyId
     * @param string $featureSlug
     * @param int|null $fileSizeKB Optional: used for storage features
     * @return array
     */
    public function checkFeatureAccess(int $companyId, string $featureSlug, ?float $fileSizeKB = null): array
    {
        $company = Company::find($companyId);

        if (!$company || !$company->plan) {
            return [
                'allowed' => false,
                'message' => 'No active plan found.',
            ];
        }

        $feature = Feature::where('slug', $featureSlug)->first();

        if (!$feature) {
            return [
                'allowed' => false,
                'message' => 'Feature not found.',
            ];
        }

        
        $planFeature = $company->plan->features()
            ->where('feature_id', $feature->id)
            ->first();

        if (!$planFeature) {
            return [
                'allowed' => false,
                'message' => 'Feature not available in your plan.',
            ];
        }
        $isResettable = $planFeature->resettable;
        $resetFrequency = $planFeature->reset_frequency;
        $unit = $feature->unit;

        $resetDate = $this->getResetDate($resetFrequency, $isResettable);

        return DB::transaction(function () use ($company, $isResettable, $resetFrequency, $feature, $planFeature, $resetDate, $unit, $fileSizeKB) {
            
            $usage = CompanyUsage::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'feature_id' => $feature->id,
                    'reset_date' => $resetDate
                ],
                ['used' => 0]
            );

            
            $increment = $this->getIncrementValue($unit, $fileSizeKB);

            if ($usage->used + $increment > $planFeature->pivot->value) {
                return [
                    'allowed' => false,
                    'message' => "Usage limit exceeded for {$feature->name}.",
                    'feature' => $feature->name,
                    'limit' => $planFeature->pivot->value,
                    'used' => $usage->used,
                    'unit' => $unit,
                    'resettable' => $isResettable,
                    'reset_frequency' => $resetFrequency,
                ];
            }

            
            $usage->increment('used', $increment);

            return [
                'allowed' => true,
                'feature' => $feature->name,
                'used' => $usage->used + $increment,
                'limit' => $planFeature->pivot->value,
                'unit' => $unit,
                'resettable' => $isResettable,
                'reset_frequency' => $resetFrequency,
                'reset_date' => $resetDate,
            ];
        });
    }

    /**
     * Calculate reset date based on frequency
     *
     * @param string|null $frequency
     * @param bool $resettable
     * @return \Carbon\Carbon|null
     */
    protected function getResetDate(?string $frequency, ?bool $resettable): ?string
    {
        
        $resettable = $resettable ?? false;

        if (!$resettable) return null;

        $now = now();

        switch ($frequency) {
            case 'daily':
                return $now->startOfDay()->toDateString();
            case 'weekly':
                return $now->startOfWeek()->toDateString();
            case 'monthly':
                return $now->startOfMonth()->toDateString();
            default:
                return $now->startOfDay()->toDateString(); // fallback
        }
    }

    /**
     * Get actual byte size based on feature unit
     *
     * @param string $unit
     * @param int|null $fileSizeKB
     * @return int
     */
    protected function getIncrementValue(string $unit, ?float $fileSizeKB): float
    {
        
        if ($unit == 'kb') {return $fileSizeKB;}
        if ($unit == 'mb') {return $fileSizeKB / 1024;}
        return 1;
    }
}
