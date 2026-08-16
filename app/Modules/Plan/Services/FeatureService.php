<?php

namespace App\Modules\Plan\Services;

use App\Models\Feature;
use App\Modules\Plan\Repositories\Contracts\FeatureRepositoryInterface;

class FeatureService
{
    public function __construct(
        protected FeatureRepositoryInterface $featureRepo
    ) {}

    public function getAllFeatures(): array
    {
        return $this->featureRepo->getAll()->toArray();
    }

    public function createFeature(array $data): Feature
    {
        return $this->featureRepo->create($data);
    }
}
