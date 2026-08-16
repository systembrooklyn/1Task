<?php

namespace App\Modules\Plan\Repositories\Eloquent;

use App\Models\Plan;
use App\Modules\Plan\Repositories\Contracts\PlanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentPlanRepository implements PlanRepositoryInterface
{
    public function create(array $data): Plan
    {
        return Plan::create($data);
    }

    public function getActiveWithFeatures(): Collection
    {
        return Plan::where('is_active', true)
            ->with(['features' => fn($q) => $q->withPivot('value')])
            ->get();
    }

    public function getActiveWithFeaturesAndPriceMin(float $price): Collection
    {
        return Plan::where('is_active', true)
            ->where('price', '>=', $price)
            ->with(['features' => fn($q) => $q->withPivot('value')])
            ->get();
    }

    public function getAllWithFeatures(): Collection
    {
        return Plan::with(['features' => fn($q) => $q->withPivot('value')])
            ->get();
    }

    public function findByName(string $name): ?Plan
    {
        return Plan::where('name', $name)->first();
    }

    public function attachFeatures(Plan $plan, array $features): void
    {
        $plan->features()->sync($features);
    }
}
