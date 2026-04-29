<?php

namespace Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Http\Resources\BaseResource;
use Modules\Permission\Http\Resources\RoleResource;

class UserResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge($this->base(), [
            'name'       => $this->name,
            'email'      => $this->email,
            'avatar_url' => $this->getFirstMediaUrl('avatar') ?: null,
            'roles'      => RoleResource::collection($this->whenLoaded('roles')),
        ]);
    }
}
