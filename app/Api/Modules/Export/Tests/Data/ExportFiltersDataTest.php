<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Data;

use App\Api\Modules\Export\Data\ExportFiltersData;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class ExportFiltersDataTest extends TestCase
{
    public static function validData(): array
    {
        return [
            'empty' => [[]],
            'all_fields' => [
                [
                    'search' => 'john',
                    'role' => 'admin',
                    'state' => 'SP',
                    'city' => 'São Paulo',
                ],
            ],
            'search_only' => [['search' => 'test']],
            'role_only' => [['role' => 'user']],
            'state_only' => [['state' => 'RJ']],
            'city_only' => [['city' => 'Rio']],
            'search_max_length' => [['search' => str_repeat('a', 255)]],
            'state_max_length' => [['state' => 'SP']],
            'city_max_length' => [['city' => str_repeat('a', 100)]],
        ];
    }

    public static function invalidData(): array
    {
        return [
            'search_too_long' => [['search' => str_repeat('a', 256)], 'search'],
            'role_invalid' => [['role' => 'invalid'], 'role'],
            'state_too_long' => [['state' => 'SPP'], 'state'],
            'city_too_long' => [['city' => str_repeat('a', 101)], 'city'],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = ExportFiltersData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(ExportFiltersData::class, $result);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Arrange & Act & Assert
        $this->expectException(ValidationException::class);

        try {
            ExportFiltersData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());

            throw $e;
        }
    }

    public function testToArrayShouldFilterNullAndEmptyValues(): void
    {
        // Arrange
        $data = ExportFiltersData::from([
            'search' => 'test',
            'role' => null,
            'state' => '',
            'city' => 'São Paulo',
        ]);

        // Act
        $result = $data->toArray();

        // Assert
        $this->assertEquals(['search' => 'test', 'city' => 'São Paulo'], $result);
    }
}
