<?php

namespace Modules\ActivityLog\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Http\Resources\BaseResource;

class ActivityLogResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge($this->base(), [
            'log_name'    => $this->log_name,
            'description' => $this->description,
            'subject'     => $this->when($this->subject_type, [
                'type' => $this->subject_type,
                'id'   => $this->subject_id,
            ]),
            'causer'      => $this->when($this->causer_id, [
                'id'   => $this->causer_id,
                'type' => $this->causer_type,
            ]),
            'properties'  => $this->properties,
        ]);
    }
}
