<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Tests\Data;

use App\Api\Modules\Auth\Data\RegisterAuthData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class RegisterAuthDataTest extends TestCase
{
    use RefreshDatabase;

    private static function validPayload(): array
    {
        return [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
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
                'password_confirmation' => 'short',
                'email' => 'short@example.com',
            ]), 'password'],
            'password_not_confirmed' => [array_merge(self::validPayload(), [
                'password_confirmation' => 'different123',
                'email' => 'mismatch@example.com',
            ]), 'password'],
            'password_confirmation_null' => [array_merge(self::validPayload(), [
                'password_confirmation' => null,
                'email' => 'noconfirm@example.com',
            ]), 'password_confirmation'],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = RegisterAuthData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(RegisterAuthData::class, $result);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Arrange
        if ($expectedField === 'email' && $invalidItem['email'] === 'existing@example.com') {
            User::factory()->create(['email' => 'existing@example.com']);
        }

        // Act & Assert
        $this->expectException(ValidationException::class);

        try {
            RegisterAuthData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());

            throw $e;
        }
    }
}
