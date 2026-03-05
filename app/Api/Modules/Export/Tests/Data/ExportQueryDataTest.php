<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Data;

use App\Api\Modules\Export\Data\ExportQueryData;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class ExportQueryDataTest extends TestCase
{
    public static function validData(): array
    {
        return [
            'empty' => [[]],
            'status_queued' => [['status' => 'queued']],
            'status_processing' => [['status' => 'processing']],
            'status_completed' => [['status' => 'completed']],
            'status_failed' => [['status' => 'failed']],
            'page_only' => [['page' => 2]],
            'per_page_only' => [['per_page' => 25]],
            'all_params' => [['status' => 'completed', 'page' => 1, 'per_page' => 10]],
            'per_page_min' => [['per_page' => 1]],
            'per_page_max' => [['per_page' => 100]],
        ];
    }

    public static function invalidData(): array
    {
        return [
            'status_invalid' => [['status' => 'invalid_status'], 'status'],
            'status_not_string' => [['status' => 123], 'status'],
            'page_zero' => [['page' => 0], 'page'],
            'page_negative' => [['page' => -1], 'page'],
            'per_page_zero' => [['per_page' => 0], 'per_page'],
            'per_page_negative' => [['per_page' => -5], 'per_page'],
            'per_page_exceeds_max' => [['per_page' => 101], 'per_page'],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = ExportQueryData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(ExportQueryData::class, $result);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Arrange & Act & Assert
        $this->expectException(ValidationException::class);

        try {
            ExportQueryData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());

            throw $e;
        }
    }

    public function testShouldSetDefaultPerPageWhenNotProvided(): void
    {
        // Arrange & Act
        $result = ExportQueryData::validateAndCreate([]);

        // Assert
        $this->assertEquals(ExportQueryData::PER_PAGE_DEFAULT, $result->perPage);
    }

    public function testShouldSetDefaultPageWhenNotProvided(): void
    {
        // Arrange & Act
        $result = ExportQueryData::validateAndCreate([]);

        // Assert
        $this->assertEquals(1, $result->page);
    }

    public function testShouldPreserveStatusWhenProvided(): void
    {
        // Arrange & Act
        $result = ExportQueryData::validateAndCreate(['status' => 'completed']);

        // Assert
        $this->assertEquals('completed', $result->status);
    }

    public function testShouldSetStatusNullWhenNotProvided(): void
    {
        // Arrange & Act
        $result = ExportQueryData::validateAndCreate([]);

        // Assert
        $this->assertNull($result->status);
    }
}
