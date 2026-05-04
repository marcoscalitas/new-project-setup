<?php

namespace Shared\Media\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Shared\Media\Contracts\MediaUploader;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SpatieMediaUploader implements MediaUploader
{
    public function upload(Model $model, UploadedFile $file, string $collection, ?string $disk = null): Media
    {
        $media = $model->addMedia($file);

        return $disk === null
            ? $media->toMediaCollection($collection)
            : $media->toMediaCollection($collection, $disk);
    }
}
