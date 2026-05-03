<?php

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->ulid,
            'name'       => $this->name,
            'email'      => $this->email,
            'roles'      => $this->whenLoaded('roles', fn () => $this->roles->map(fn ($role) => [
                'id'   => $role->id,
                'name' => $role->name,
            ])),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
