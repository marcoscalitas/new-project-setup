<?php

namespace Modules\Export\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Export\Models\Export;
use Modules\User\Models\User;

class ExportFactory extends Factory
{
    protected $model = Export::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'module' => $this->faker->randomElement(['users', 'audit_log']),
            'format' => $this->faker->randomElement(['csv', 'xlsx', 'pdf']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'path' => null,
            'filename' => null,
            'error' => null,
            'expires_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'path' => 'exports/test.csv',
            'filename' => 'test.csv',
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function failed(): static
    {
        return $this->state(['status' => 'failed', 'error' => 'Export failed.']);
    }
}
