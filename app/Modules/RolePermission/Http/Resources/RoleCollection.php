<?php

namespace App\Modules\RolePermission\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RoleCollection extends ResourceCollection
{
    public $collects = RoleResource::class;

    public function toArray($request)
    {
        return $this->collection;
    }
}
