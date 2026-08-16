<?php

namespace App\Modules\Plan\Repositories\Contracts;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Collection;

interface FeatureRepositoryInterface
{
    public function create(array $data): Feature;
    public function getAll(): Collection;
    public function findById(int $id): ?Feature;
}
