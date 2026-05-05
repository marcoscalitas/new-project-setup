<?php

namespace Shared\Data\AuditLog;

final class AuditLogData
{
    public function __construct(
        public readonly string $action,
        public readonly ?string $description = null,
        public readonly ?string $actorType = null,
        public readonly int|string|null $actorId = null,
        public readonly ?string $subjectType = null,
        public readonly int|string|null $subjectId = null,
        public readonly array $oldValues = [],
        public readonly array $newValues = [],
        public readonly array $metadata = [],
        public readonly ?string $logName = null,
    ) {}
}
