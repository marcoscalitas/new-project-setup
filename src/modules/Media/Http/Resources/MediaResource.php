<?php

namespace Modules\Media\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Http\Resources\BaseResource;

class MediaResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge($this->base(), [
            'id'         => $this->id,  // Spatie Media has no ulid — expose int PK
            'name'       => $this->name,
            'file_name'  => $this->file_name,
            'mime_type'  => $this->mime_type,
            'size'       => $this->size,
            'collection' => $this->collection_name,
        ]);
    }
}
