<?php

namespace Shared\Contracts\Export;

interface ExportRegistry
{
    public function register(Exporter $exporter): void;

    public function get(string $key): Exporter;

    public function has(string $key): bool;

    /**
     * @return array<string, Exporter>
     */
    public function all(): array;
}
