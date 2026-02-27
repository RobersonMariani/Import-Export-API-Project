<?php

namespace App\Api\Modules\Export\Tests\Data;

use App\Api\Modules\Export\Data\CreateExportData;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class CreateExportDataTest extends TestCase
{

    private static function validPayload(): array
    {
        return [];
    }

    public static function validData(): array
    {
        return [
            'empty_payload' => [[]],
            'all_optional_fields' => [
                [
                    'filters' => [
                        'search' => 'test',
                        'role' => 'admin',
                        'state' => 'SP',
                        'city' => 'São Paulo',
                    ],
                    'compressed' => true,
                ],
            ],
            'filters_null' => [['filters' => null]],
            'compressed_false' => [['compressed' => false]],
            'compressed_true' => [['compressed' => true]],
        ];
    }

    public static function invalidData(): array
    {
        return [
            'compressed_not_boolean' => [['compressed' => 'yes'], 'compressed'],
            'filters_search_too_long' => [
                ['filters' => ['search' => str_repeat('a', 256)]],
                'filters.search',
            ],
            'filters_role_invalid' => [['filters' => ['role' => 'invalid_role']], 'filters.role'],
            'filters_state_too_long' => [['filters' => ['state' => 'SPP']], 'filters.state'],
            'filters_city_too_long' => [
                ['filters' => ['city' => str_repeat('a', 101)]],
                'filters.city',
            ],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = CreateExportData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(CreateExportData::class, $result);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Arrange & Act & Assert
        $this->expectException(ValidationException::class);

        try {
            CreateExportData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());
            throw $e;
        }
    }
}
