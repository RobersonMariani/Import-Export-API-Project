<?php

namespace App\Api\Modules\Import\Tests\Data;

use App\Api\Modules\Import\Data\CreateImportData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class CreateImportDataTest extends TestCase
{
    use RefreshDatabase;

    private static function validFile(): UploadedFile
    {
        return UploadedFile::fake()->create('users.csv', 100, 'text/csv');
    }

    public static function validData(): array
    {
        return [
            'csv_file' => [['file' => self::validFile()]],
            'txt_file' => [['file' => UploadedFile::fake()->create('data.txt', 50, 'text/plain')]],
        ];
    }

    public static function invalidData(): array
    {
        return [
            'file_null' => [['file' => null], 'file'],
            'file_empty' => [['file' => ''], 'file'],
            'file_not_file' => [['file' => 'not-a-file'], 'file'],
            'file_wrong_mime' => [
                ['file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')],
                'file',
            ],
            'file_too_large' => [
                ['file' => UploadedFile::fake()->create('huge.csv', 51201, 'text/csv')],
                'file',
            ],
        ];
    }

    #[DataProvider('validData')]
    public function testShouldPassValidationWhenDataIsValid(array $validItem): void
    {
        // Arrange & Act
        $result = CreateImportData::validateAndCreate($validItem);

        // Assert
        $this->assertInstanceOf(CreateImportData::class, $result);
        $this->assertInstanceOf(UploadedFile::class, $result->file);
    }

    #[DataProvider('invalidData')]
    public function testShouldFailValidationWhenDataIsInvalid(array $invalidItem, string $expectedField): void
    {
        // Arrange
        if (! isset($invalidItem['file']) || $invalidItem['file'] !== 'not-a-file') {
            $invalidItem['file'] = $invalidItem['file'] ?? null;
        }

        // Act & Assert
        $this->expectException(ValidationException::class);

        try {
            CreateImportData::validateAndCreate($invalidItem);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());
            throw $e;
        }
    }

    public function testShouldFailValidationWhenFileIsMissing(): void
    {
        // Act & Assert
        $this->expectException(ValidationException::class);

        try {
            CreateImportData::validateAndCreate([]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('file', $e->errors());
            throw $e;
        }
    }
}
