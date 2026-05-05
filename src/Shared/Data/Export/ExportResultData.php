<?php

namespace Shared\Data\Export;

final class ExportResultData
{
    public function __construct(
        public readonly string $ulid,
        public readonly string $status,
    ) {}

    public function toArray(): array
    {
        return [
            'ulid' => $this->ulid,
            'status' => $this->status,
        ];
    }
}
