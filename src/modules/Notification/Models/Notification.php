<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Notification extends DatabaseNotification
{
    use LogsActivity, SoftDeletes;

    protected static $recordEvents = ['updated', 'deleted'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->id = (string) Str::ulid();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['read_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
