<?php

namespace Modules\Permission\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Http\Resources\BaseResource;

class RoleResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge($this->base(), [
            'name'        => $this->name,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
        ]);
    }
}
