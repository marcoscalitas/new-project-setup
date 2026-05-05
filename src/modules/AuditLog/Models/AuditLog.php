<?php

namespace Modules\AuditLog\Models;

use Spatie\Activitylog\Models\Activity;

class AuditLog extends Activity
{
    protected $table = 'activity_log';
}
