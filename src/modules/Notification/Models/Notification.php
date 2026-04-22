<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Notification extends DatabaseNotification
{
    use LogsActivity, SoftDeletes;

    protected static $recordEvents = ['updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['read_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
