<?php

namespace Shared\Contracts\AuditLog;

use Shared\Data\AuditLog\AuditLogData;

interface AuditLogger
{
    public function record(AuditLogData $data): void;
}
