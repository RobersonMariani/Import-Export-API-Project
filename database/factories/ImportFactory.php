<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Import>
 */
class ImportFactory extends Factory
{
    protected $model = Import::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => ImportStatusEnum::Queued->value,
            'progress' => 0,
            'total_records' => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'file_path' => 'imports/'.fake()->uuid().'.csv',
            'original_filename' => fake()->word().'.csv',
            'metadata' => null,
            'started_at' => null,
            'finished_at' => null,
        ];
    }

    public function queued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImportStatusEnum::Queued->value,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImportStatusEnum::Processing->value,
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImportStatusEnum::Completed->value,
            'progress' => 10,
            'total_records' => 10,
            'success_count' => 10,
            'failure_count' => 0,
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImportStatusEnum::Failed->value,
            'started_at' => now()->subMinutes(2),
            'finished_at' => now(),
        ]);
    }
}
