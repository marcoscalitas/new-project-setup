<?php

namespace Shared\Media\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaUploader
{
    public function upload(Model $model, UploadedFile $file, string $collection, ?string $disk = null): Media;
}
