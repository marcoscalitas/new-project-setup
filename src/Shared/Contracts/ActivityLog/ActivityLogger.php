<?php

namespace Shared\Contracts\ActivityLog;

use Shared\Data\ActivityLog\ActivityLogData;

interface ActivityLogger
{
    public function record(ActivityLogData $data): void;
}
