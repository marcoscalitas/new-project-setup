<?php

namespace Tests\Unit\ActivityLog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ActivityLog\Models\ActivityLog;
use Modules\ActivityLog\Services\SpatieActivityLogger;
use Modules\User\Models\User;
use Shared\Contracts\ActivityLog\ActivityLogger;
use Shared\Data\ActivityLog\ActivityLogData;
use Tests\TestCase;

class SpatieActivityLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_activity_log_data(): void
    {
        $actor = User::factory()->create();
        $subject = User::factory()->create();

        app(ActivityLogger::class)->record(new ActivityLogData(
            action: 'user.updated',
            description: 'User updated',
            actorType: User::class,
            actorId: $actor->id,
            subjectType: User::class,
            subjectId: $subject->id,
            oldValues: ['name' => 'Old Name'],
            newValues: ['name' => 'New Name'],
            metadata: ['source' => 'test'],
            logName: 'users',
        ));

        $activity = ActivityLog::query()
            ->where('event', 'user.updated')
            ->firstOrFail();

        $this->assertInstanceOf(SpatieActivityLogger::class, app(ActivityLogger::class));
        $this->assertSame('users', $activity->log_name);
        $this->assertSame('user.updated', $activity->event);
        $this->assertSame('User updated', $activity->description);
        $this->assertSame(User::class, $activity->causer_type);
        $this->assertSame($actor->id, $activity->causer_id);
        $this->assertSame(User::class, $activity->subject_type);
        $this->assertSame($subject->id, $activity->subject_id);
        $this->assertSame(['name' => 'Old Name'], $activity->properties->get('old'));
        $this->assertSame(['name' => 'New Name'], $activity->properties->get('new'));
        $this->assertSame(['source' => 'test'], $activity->properties->get('metadata'));
    }
}
