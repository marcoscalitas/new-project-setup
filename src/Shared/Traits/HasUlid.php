<?php

namespace Shared\Traits;

use Illuminate\Support\Str;

trait HasUlid
{
    protected function initializeHasUlid(): void
    {
        if (empty($this->ulid)) {
            $this->ulid = (string) Str::ulid();
        }
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
