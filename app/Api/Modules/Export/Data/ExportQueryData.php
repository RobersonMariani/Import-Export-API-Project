<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Data;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;

#[MapName(SnakeCaseMapper::class)]
class ExportQueryData extends Data
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
            'status' => ['nullable', 'string', Rule::in(ExportStatusEnum::values())],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
