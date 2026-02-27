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
class UpdateUserData extends Data
{
    public function __construct(
        public ?int $userId = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zipCode = null,
        public ?string $birthDate = null,
        public ?string $role = null,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        $payload = is_array($context->payload) ? $context->payload : [];
        $userId = $payload['user_id'] ?? $payload['userId'] ?? null;

        return [
            'user_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'birth_date' => ['nullable', 'date'],
            'role' => ['nullable', 'string', Rule::in(RoleEnum::values())],
        ];
    }

    /** @return array<string, mixed> */
    public function toArrayModel(): array
    {
        $data = array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zipCode,
            'birth_date' => $this->birthDate,
            'role' => $this->role,
        ], fn ($value) => $value !== null);

        return $data;
    }
}
