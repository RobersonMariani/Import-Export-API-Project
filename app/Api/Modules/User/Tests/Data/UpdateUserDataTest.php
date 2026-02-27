<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Tests\Data;

use App\Api\Modules\User\Data\UpdateUserData;
use App\Api\Modules\User\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class UpdateUserDataTest extends TestCase
{
    use RefreshDatabase;

    public static function validData(): array
    {
        return [
            'empty_payload' => [[]],
            'name_only' => [['name' => 'Updated Name']],
            'email_only' => [['email' => 'updated@example.com']],
            'password_only' => [['password' => 'newpassword123']],
            'all_optional_fields' => [[
                'name' => 'Full Update',
                'email' => 'fullupdate@example.com',
                'password' => 'newpass123',
                'phone' => '11988887777',
                'address' => 'Rua Nova, 456',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'zip_code' => '20000-000',
                'birth_date' => '1985-05-20',
                'role' => RoleEnum::Manager->value,
            ]],
            'role_admin' => [['role' => RoleEnum::Admin->value]],
            'role_user' => [['role' => RoleEnum::User->value]],
        ];
    }

    public static function invalidData(): array
    {
        return [
            'name_too_long' => [['name' => str_repeat('a', 256)], 'name'],
            'name_not_string' => [['name' => 123], 'name'],
            'email_invalid' => [['email' => 'invalid-email'], 'email'],
            'password_too_short' => [['password' => 'short'], 'password'],
            'role_invalid' => [['role' => 'invalid_role'], 'role'],
            'state_too_long' => [['state' => 'SPP'], 'state'],
            'birth_date_invalid' => [['birth_date' => 'not-a-date'], 'birth_date'],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = UpdateUserData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(UpdateUserData::class, $result);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Act & Assert
        $this->expectException(ValidationException::class);

        try {
            UpdateUserData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());

            throw $e;
        }
    }

    public function testShouldPassValidationWhenEmailSameAsCurrentUser(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'same@example.com']);
        $payload = [
            'user_id' => $user->id,
            'email' => 'same@example.com',
        ];

        // Act
        $result = UpdateUserData::validateAndCreate($payload);

        // Assert
        $this->assertInstanceOf(UpdateUserData::class, $result);
    }

    public function testShouldFailValidationWhenEmailBelongsToAnotherUser(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'current@example.com']);
        User::factory()->create(['email' => 'other@example.com']);
        $payload = [
            'user_id' => $user->id,
            'email' => 'other@example.com',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);

        try {
            UpdateUserData::validateAndCreate($payload);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());

            throw $e;
        }
    }
}
