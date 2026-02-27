<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\Data;

use App\Api\Modules\Import\Data\ImportQueryData;
use App\Api\Modules\Import\Enums\ImportStatusEnum;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class ImportQueryDataTest extends TestCase
{
    public static function validData(): array
    {
        return [
            'empty_query' => [[]],
            'status_queued' => [['status' => ImportStatusEnum::Queued->value]],
            'status_processing' => [['status' => ImportStatusEnum::Processing->value]],
            'status_completed' => [['status' => ImportStatusEnum::Completed->value]],
            'status_failed' => [['status' => ImportStatusEnum::Failed->value]],
            'status_partial' => [['status' => ImportStatusEnum::Partial->value]],
            'pagination' => [['page' => 2, 'per_page' => 10]],
            'per_page_max' => [['per_page' => 100]],
            'all_filters' => [[
                'status' => ImportStatusEnum::Completed->value,
                'page' => 1,
                'per_page' => 25,
            ]],
        ];
    }

    public static function invalidData(): array
    {
        return [
            'status_invalid' => [['status' => 'invalid_status'], 'status'],
            'page_zero' => [['page' => 0], 'page'],
            'page_negative' => [['page' => -1], 'page'],
            'per_page_zero' => [['per_page' => 0], 'per_page'],
            'per_page_too_high' => [['per_page' => 101], 'per_page'],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = ImportQueryData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(ImportQueryData::class, $result);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Act & Assert
        $this->expectException(ValidationException::class);

        try {
            ImportQueryData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());

            throw $e;
        }
    }

    public function testShouldApplyDefaultValuesWhenNotProvided(): void
    {
        // Arrange & Act
        $result = ImportQueryData::validateAndCreate([]);

        // Assert
        $this->assertEquals(1, $result->page);
        $this->assertEquals(ImportQueryData::PER_PAGE_DEFAULT, $result->perPage);
    }
}
