<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Export>
 */
class ExportFactory extends Factory
{
    protected $model = Export::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => ExportStatusEnum::Queued->value,
            'file_path' => null,
            'filters' => null,
            'total_records' => 0,
            'compressed' => false,
            'expires_at' => null,
            'started_at' => null,
            'finished_at' => null,
        ];
    }

    public function queued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExportStatusEnum::Queued->value,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExportStatusEnum::Processing->value,
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExportStatusEnum::Completed->value,
            'file_path' => 'exports/'.fake()->uuid().'.csv',
            'total_records' => 10,
            'expires_at' => now()->addHour(),
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExportStatusEnum::Failed->value,
            'started_at' => now()->subMinutes(2),
            'finished_at' => now(),
        ]);
    }
}
