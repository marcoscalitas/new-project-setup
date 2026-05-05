<?php

namespace Modules\ActivityLog\Services;

use Shared\Contracts\ActivityLog\ActivityLogger;
use Shared\Data\ActivityLog\ActivityLogData;

class SpatieActivityLogger implements ActivityLogger
{
    public function record(ActivityLogData $data): void
    {
        activity()
            ->event($data->action)
            ->when($data->logName !== null, fn ($logger) => $logger->useLog($data->logName))
            ->when($data->actorType === null && $data->actorId !== null, fn ($logger) => $logger->causedBy($data->actorId))
            ->withProperties([
                'old' => $data->oldValues,
                'new' => $data->newValues,
                'metadata' => $data->metadata,
            ])
            ->tap(function ($activity) use ($data): void {
                if ($data->actorType !== null) {
                    $activity->causer_type = $data->actorType;
                }

                if ($data->actorId !== null) {
                    $activity->causer_id = $data->actorId;
                }

                if ($data->subjectType !== null) {
                    $activity->subject_type = $data->subjectType;
                }

                if ($data->subjectId !== null) {
                    $activity->subject_id = $data->subjectId;
                }
            })
            ->log($data->description ?? $data->action);
    }
}
