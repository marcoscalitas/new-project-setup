<?php

namespace Shared\Data\Export;

final class ExportRequestData
{
    public function __construct(
        public readonly string $module,
        public readonly string $format,
        public readonly array $filters = [],
        public readonly int|string|null $userId = null,
    ) {}
}
