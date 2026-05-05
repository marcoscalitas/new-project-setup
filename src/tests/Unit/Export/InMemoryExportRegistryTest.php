<?php

namespace Tests\Unit\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Modules\Export\Services\InMemoryExportRegistry;
use Shared\Contracts\Export\Exporter;
use Tests\TestCase;

class InMemoryExportRegistryTest extends TestCase
{
    public function test_it_registers_and_returns_exporters_by_key(): void
    {
        $registry = new InMemoryExportRegistry;
        $exporter = $this->fakeExporter('users');

        $registry->register($exporter);

        $this->assertTrue($registry->has('users'));
        $this->assertSame($exporter, $registry->get('users'));
        $this->assertSame(['users' => $exporter], $registry->all());
    }

    public function test_it_fails_when_exporter_is_not_registered(): void
    {
        $registry = new InMemoryExportRegistry;

        $this->expectException(\InvalidArgumentException::class);

        $registry->get('missing');
    }

    private function fakeExporter(string $key): Exporter
    {
        return new class($key) implements Exporter
        {
            public function __construct(private readonly string $key) {}

            public function key(): string
            {
                return $this->key;
            }

            public function allowedFormats(): array
            {
                return ['csv'];
            }

            public function getQuery(array $filters = []): Builder
            {
                throw new \BadMethodCallException;
            }

            public function getExportClass(array $filters = []): FromQuery
            {
                throw new \BadMethodCallException;
            }

            public function getPdfView(): string
            {
                throw new \BadMethodCallException;
            }

            public function getFilename(): string
            {
                return $this->key;
            }

            public function getPdfData(array $filters = []): array
            {
                return [];
            }
        };
    }
}
