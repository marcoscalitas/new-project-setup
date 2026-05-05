<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class Notification extends DatabaseNotification
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->id = (string) Str::ulid();
        });
    }
}
