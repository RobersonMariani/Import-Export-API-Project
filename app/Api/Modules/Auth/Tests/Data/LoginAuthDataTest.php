<?php

namespace App\Api\Modules\Auth\Tests\Data;

use App\Api\Modules\Auth\Data\LoginAuthData;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class LoginAuthDataTest extends TestCase
{
    private static function validPayload(): array
    {
        return [
            'email' => 'test@example.com',
            'password' => 'password',
        ];
    }

    public static function validData(): array
    {
        return [
            'all_required_fields' => [self::validPayload()],
            'email_with_subdomain' => [array_merge(self::validPayload(), ['email' => 'user@mail.example.com'])],
        ];
    }

    public static function invalidData(): array
    {
        return [
            'email_null' => [array_merge(self::validPayload(), ['email' => null]), 'email'],
            'email_empty' => [array_merge(self::validPayload(), ['email' => '']), 'email'],
            'email_invalid' => [array_merge(self::validPayload(), ['email' => 'invalid-email']), 'email'],
            'email_not_string' => [array_merge(self::validPayload(), ['email' => 123]), 'email'],
            'password_null' => [array_merge(self::validPayload(), ['password' => null]), 'password'],
            'password_empty' => [array_merge(self::validPayload(), ['password' => '']), 'password'],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = LoginAuthData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(LoginAuthData::class, $result);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Arrange & Act & Assert
        $this->expectException(ValidationException::class);

        try {
            LoginAuthData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());
            throw $e;
        }
    }
}
