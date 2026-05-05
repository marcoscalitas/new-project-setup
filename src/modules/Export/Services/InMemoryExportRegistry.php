<?php

namespace Modules\Export\Services;

use InvalidArgumentException;
use Shared\Contracts\Export\Exporter;
use Shared\Contracts\Export\ExportRegistry;

class InMemoryExportRegistry implements ExportRegistry
{
    /**
     * @var array<string, Exporter>
     */
    private array $exporters = [];

    public function register(Exporter $exporter): void
    {
        $this->exporters[$exporter->key()] = $exporter;
    }

    public function get(string $key): Exporter
    {
        return $this->exporters[$key]
            ?? throw new InvalidArgumentException("Exporter [{$key}] is not registered.");
    }

    public function has(string $key): bool
    {
        return isset($this->exporters[$key]);
    }

    public function all(): array
    {
        return $this->exporters;
    }
}
