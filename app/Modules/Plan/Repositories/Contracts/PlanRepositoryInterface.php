<?php

namespace App\Modules\Plan\Repositories\Contracts;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

interface PlanRepositoryInterface
{
    public function create(array $data): Plan;
    public function getActiveWithFeatures(): Collection;
    public function getActiveWithFeaturesAndPriceMin(float $price): Collection;
    public function getAllWithFeatures(): Collection;
    public function findByName(string $name): ?Plan;
    public function attachFeatures(Plan $plan, array $features): void;
}
