<?php

namespace App\Api\Modules\Import\Data;

use App\Api\Modules\Import\Enums\ImportStatusEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;

#[MapName(SnakeCaseMapper::class)]
class ImportQueryData extends Data
{
    public const PER_PAGE_DEFAULT = 15;

    public function __construct(
        public ?string $status = null,
        public ?int $page = 1,
        public ?int $perPage = null,
    ) {
        $this->perPage ??= self::PER_PAGE_DEFAULT;
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(ImportStatusEnum::values())],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
