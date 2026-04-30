<?php

namespace Modules\Core\Traits;

use Illuminate\Support\Str;

trait HasUlid
{
    protected static function bootHasUlid(): void
    {
        static::creating(function ($model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
