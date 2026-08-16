<?php

namespace App\Modules\Plan\Services;

use App\Models\Plan;
use App\Models\User;
use App\Modules\Plan\Repositories\Contracts\PlanRepositoryInterface;
use App\Modules\Plan\Repositories\Contracts\FeatureRepositoryInterface;
use App\Exceptions\DuplicateDataException;

class PlanService
{
    public function __construct(
        protected PlanRepositoryInterface $planRepo,
        protected FeatureRepositoryInterface $featureRepo
    ) {}

    public function getAllPlans(): array
    {
        $plans = $this->planRepo->getActiveWithFeatures();
        return $this->formatPlans($plans);
    }

    public function getPlansForUser(User $user): array
    {
        $price = $user->company->plan->price ?? 0;
        $plans = $this->planRepo->getActiveWithFeaturesAndPriceMin($price);
        return $this->formatPlans($plans);
    }

    public function getAdminPlans(): array
    {
        $plans = $this->planRepo->getAllWithFeatures();
        return $this->formatPlans($plans);
    }

    public function createPlan(array $data): Plan
    {
        $existing = $this->planRepo->findByName($data['name']);
        if ($existing) {
            throw new DuplicateDataException('Plan Already Exists with the same name', 409);
        }
        return $this->planRepo->create($data);
    }

    public function attachFeatures(Plan $plan, array $features): void
    {
        $featuresData = collect($features)->mapWithKeys(function ($item) {
            $feature = $this->featureRepo->findById($item['id']);
            return [
                $item['id'] => [
                    'value' => $item['value'],
                    'resettable' => $item['resettable'] ?? true,
                    'reset_frequency' => $feature->reset_frequency
                ]
            ];
        })->all();

        $this->planRepo->attachFeatures($plan, $featuresData);
    }

    protected function formatPlans($plans): array
    {
        return $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $plan->price,
                'currency' => $plan->currency,
                'features' => $plan->features->map(function ($feature) {
                    return [
                        'name' => optional($feature)->name,
                        'value' => optional($feature->pivot)->value,
                    ];
                }),
            ];
        })->toArray();
    }
}
