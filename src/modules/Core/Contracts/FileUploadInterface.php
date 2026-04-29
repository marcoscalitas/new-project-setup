<?php

namespace Modules\Core\Contracts;

use Illuminate\Database\Eloquent\Model;

interface FileUploadInterface
{
    public function upload(mixed $file, string $collection, Model $model): string;

    public function delete(Model $model, string $collection): void;

    public function getUrl(Model $model, string $collection): ?string;
}
