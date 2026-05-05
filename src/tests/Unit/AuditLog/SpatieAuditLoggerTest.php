<?php

namespace Tests\Unit\AuditLog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AuditLog\Models\AuditLog;
use Modules\AuditLog\Services\SpatieAuditLogger;
use Modules\User\Models\User;
use Shared\Contracts\AuditLog\AuditLogger;
use Shared\Data\AuditLog\AuditLogData;
use Tests\TestCase;

class SpatieAuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_audit_log_data(): void
    {
        $actor = User::factory()->create();
        $subject = User::factory()->create();

        app(AuditLogger::class)->record(new AuditLogData(
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

        $activity = AuditLog::query()
            ->where('event', 'user.updated')
            ->firstOrFail();

        $this->assertInstanceOf(SpatieAuditLogger::class, app(AuditLogger::class));
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
