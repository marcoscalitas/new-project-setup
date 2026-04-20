<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use SoftDeletes;
}
