<?php

namespace App\Api\Modules\User\Tests\Data;

use App\Api\Modules\User\Data\CreateUserData;
use App\Api\Modules\User\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class CreateUserDataTest extends TestCase
{
    use RefreshDatabase;

    private static function validPayload(): array
    {
        return [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];
    }

    public static function validData(): array
    {
        return [
            'all_required_fields' => [self::validPayload()],
            'name_max_length' => [array_merge(self::validPayload(), [
                'name' => str_repeat('a', 255),
                'email' => 'maxlength@example.com',
            ])],
            'with_optional_fields' => [array_merge(self::validPayload(), [
                'email' => 'optional@example.com',
                'phone' => '11999999999',
                'address' => 'Rua Teste, 123',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567',
                'birth_date' => '1990-01-15',
                'role' => RoleEnum::Admin->value,
            ])],
            'role_manager' => [array_merge(self::validPayload(), [
                'email' => 'manager@example.com',
                'role' => RoleEnum::Manager->value,
            ])],
            'role_user' => [array_merge(self::validPayload(), [
                'email' => 'user@example.com',
                'role' => RoleEnum::User->value,
            ])],
        ];
    }

    public static function invalidData(): array
    {
        return [
            'name_null' => [array_merge(self::validPayload(), ['name' => null]), 'name'],
            'name_empty' => [array_merge(self::validPayload(), ['name' => '']), 'name'],
            'name_too_long' => [array_merge(self::validPayload(), [
                'name' => str_repeat('a', 256),
                'email' => 'toolong@example.com',
            ]), 'name'],
            'name_not_string' => [array_merge(self::validPayload(), ['name' => 123]), 'name'],
            'email_null' => [array_merge(self::validPayload(), ['email' => null]), 'email'],
            'email_empty' => [array_merge(self::validPayload(), ['email' => '']), 'email'],
            'email_invalid' => [array_merge(self::validPayload(), ['email' => 'invalid-email']), 'email'],
            'email_not_unique' => [array_merge(self::validPayload(), ['email' => 'existing@example.com']), 'email'],
            'password_null' => [array_merge(self::validPayload(), ['password' => null]), 'password'],
            'password_empty' => [array_merge(self::validPayload(), ['password' => '']), 'password'],
            'password_too_short' => [array_merge(self::validPayload(), [
                'password' => 'short',
                'email' => 'short@example.com',
            ]), 'password'],
            'role_invalid' => [array_merge(self::validPayload(), [
                'role' => 'invalid_role',
                'email' => 'invalidrole@example.com',
            ]), 'role'],
            'state_too_long' => [array_merge(self::validPayload(), [
                'state' => 'SPP',
                'email' => 'statelong@example.com',
            ]), 'state'],
            'birth_date_invalid' => [array_merge(self::validPayload(), [
                'birth_date' => 'not-a-date',
                'email' => 'baddate@example.com',
            ]), 'birth_date'],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = CreateUserData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(CreateUserData::class, $result);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Arrange
        if ($expectedField === 'email' && ($invalidItem['email'] ?? '') === 'existing@example.com') {
            User::factory()->create(['email' => 'existing@example.com']);
        }

        // Act & Assert
        $this->expectException(ValidationException::class);

        try {
            CreateUserData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());
            throw $e;
        }
    }
}
