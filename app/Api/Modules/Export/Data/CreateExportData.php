<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Data;

use App\Api\Modules\User\Enums\RoleEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;

#[MapName(SnakeCaseMapper::class)]
class CreateExportData extends Data
{
    public function __construct(
        public ?ExportFiltersData $filters = null,
        public bool $compressed = false,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'filters' => ['nullable', 'array'],
            'filters.search' => ['nullable', 'string', 'max:255'],
            'filters.role' => ['nullable', 'string', Rule::in(RoleEnum::values())],
            'filters.state' => ['nullable', 'string', 'max:2'],
            'filters.city' => ['nullable', 'string', 'max:100'],
            'compressed' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, mixed> */
    public function toArrayModel(): array
    {
        return [
            'filters' => $this->filters?->toArray(),
            'compressed' => $this->compressed,
        ];
    }
}
