<?php

namespace App\Api\Modules\User\Tests\Data;

use App\Api\Modules\User\Data\UserQueryData;
use App\Api\Modules\User\Enums\RoleEnum;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class UserQueryDataTest extends TestCase
{
    public static function validData(): array
    {
        return [
            'empty_query' => [[]],
            'search_only' => [['search' => 'john']],
            'role_filter' => [['role' => RoleEnum::Admin->value]],
            'state_filter' => [['state' => 'SP']],
            'city_filter' => [['city' => 'São Paulo']],
            'pagination' => [['page' => 2, 'per_page' => 10]],
            'sort_by_name_asc' => [['sort_by' => 'name', 'sort_order' => 'asc']],
            'sort_by_email_desc' => [['sort_by' => 'email', 'sort_order' => 'desc']],
            'sort_by_created_at' => [['sort_by' => 'created_at']],
            'all_filters' => [[
                'search' => 'test',
                'role' => RoleEnum::Manager->value,
                'state' => 'RJ',
                'city' => 'Rio de Janeiro',
                'page' => 1,
                'per_page' => 25,
                'sort_by' => 'name',
                'sort_order' => 'asc',
            ]],
        ];
    }

    public static function invalidData(): array
    {
        return [
            'role_invalid' => [['role' => 'invalid_role'], 'role'],
            'state_too_long' => [['state' => 'SPP'], 'state'],
            'city_too_long' => [['city' => str_repeat('a', 101)], 'city'],
            'page_zero' => [['page' => 0], 'page'],
            'page_negative' => [['page' => -1], 'page'],
            'per_page_zero' => [['per_page' => 0], 'per_page'],
            'per_page_too_high' => [['per_page' => 101], 'per_page'],
            'sort_by_invalid' => [['sort_by' => 'invalid_column'], 'sort_by'],
            'sort_order_invalid' => [['sort_order' => 'invalid'], 'sort_order'],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = UserQueryData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(UserQueryData::class, $result);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Act & Assert
        $this->expectException(ValidationException::class);

        try {
            UserQueryData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());
            throw $e;
        }
    }

    public function testShouldApplyDefaultValuesWhenNotProvided(): void
    {
        // Arrange & Act
        $result = UserQueryData::validateAndCreate([]);

        // Assert
        $this->assertEquals(1, $result->page);
        $this->assertEquals(UserQueryData::PER_PAGE_DEFAULT, $result->perPage);
        $this->assertEquals(UserQueryData::SORT_CREATED_AT, $result->sortBy);
        $this->assertEquals(UserQueryData::ORDER_DESC, $result->sortOrder);
    }
}
