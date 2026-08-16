<?php

namespace App\Modules\Plan\Repositories\Eloquent;

use App\Models\Feature;
use App\Modules\Plan\Repositories\Contracts\FeatureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentFeatureRepository implements FeatureRepositoryInterface
{
    public function create(array $data): Feature
    {
        return Feature::create($data);
    }

    public function getAll(): Collection
    {
        return Feature::all();
    }

    public function findById(int $id): ?Feature
    {
        return Feature::find($id);
    }
}
