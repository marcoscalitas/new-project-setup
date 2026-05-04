<?php

namespace Shared\Media\Contracts;

use Illuminate\Database\Eloquent\Model;

interface MediaRemover
{
    public function remove(Model $model, string $collection): void;
}
