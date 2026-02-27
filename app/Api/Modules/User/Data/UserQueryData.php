<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Data;

use App\Api\Modules\User\Enums\RoleEnum;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;

#[MapName(SnakeCaseMapper::class)]
class UserQueryData extends Data
{
    public const PER_PAGE_DEFAULT = 15;

    public const SORT_NAME = 'name';

    public const SORT_EMAIL = 'email';

    public const SORT_CREATED_AT = 'created_at';

    public const ORDER_ASC = 'asc';

    public const ORDER_DESC = 'desc';

    public function __construct(
        public ?string $search = null,
        public ?string $role = null,
        public ?string $state = null,
        public ?string $city = null,
        public ?int $page = 1,
        public ?int $perPage = null,
        public ?string $sortBy = null,
        public ?string $sortOrder = null,
    ) {
        $this->perPage ??= self::PER_PAGE_DEFAULT;
        $this->sortBy ??= self::SORT_CREATED_AT;
        $this->sortOrder ??= self::ORDER_DESC;
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', Rule::in(RoleEnum::values())],
            'state' => ['nullable', 'string', 'max:2'],
            'city' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_by' => [
                'nullable',
                'string',
                Rule::in([self::SORT_NAME, self::SORT_EMAIL, self::SORT_CREATED_AT]),
            ],
            'sort_order' => ['nullable', 'string', Rule::in([self::ORDER_ASC, self::ORDER_DESC])],
        ];
    }
}
