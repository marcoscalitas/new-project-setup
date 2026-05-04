<?php

namespace Modules\User\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Permission\Http\Resources\RoleResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->ulid,
            'name'       => $this->name,
            'email'      => $this->email,
            'avatar_url' => $this->getAvatarUrl(),
            'roles'      => RoleResource::collection($this->whenLoaded('roles')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
