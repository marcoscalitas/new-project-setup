<?php

namespace Modules\Media\Services;

use Illuminate\Database\Eloquent\Model;
use Shared\Contracts\FileUploadInterface;

class MediaService implements FileUploadInterface
{
    public function upload(mixed $file, string $collection, Model $model): string
    {
        $media = $model->addMedia($file)->toMediaCollection($collection);

        return $media->getUrl();
    }

    public function delete(Model $model, string $collection): void
    {
        $model->clearMediaCollection($collection);
    }

    public function getUrl(Model $model, string $collection): ?string
    {
        return $model->getFirstMediaUrl($collection) ?: null;
    }
}
