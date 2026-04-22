<?php

namespace Modules\ActivityLog\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
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
            'created_at'  => $this->created_at,
        ];
    }
}
