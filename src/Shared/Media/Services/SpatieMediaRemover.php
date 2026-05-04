<?php

namespace Shared\Media\Services;

use Illuminate\Database\Eloquent\Model;
use Shared\Media\Contracts\MediaRemover;

class SpatieMediaRemover implements MediaRemover
{
    public function remove(Model $model, string $collection): void
    {
        $model->clearMediaCollection($collection);
    }
}
