<?php

namespace Modules\Notification\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Http\Resources\BaseResource;

class NotificationResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge($this->base(), [
            'type'    => $this->type,
            'data'    => $this->data,
            'read_at' => $this->read_at,
        ]);
    }
}
