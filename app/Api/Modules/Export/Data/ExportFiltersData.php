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
class ExportFiltersData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $role = null,
        public ?string $state = null,
        public ?string $city = null,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', Rule::in(RoleEnum::values())],
            'state' => ['nullable', 'string', 'max:2'],
            'city' => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'search' => $this->search,
            'role' => $this->role,
            'state' => $this->state,
            'city' => $this->city,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
