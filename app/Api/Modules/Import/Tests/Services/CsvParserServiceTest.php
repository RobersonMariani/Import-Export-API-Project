<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\Services;

use App\Api\Modules\Import\Services\CsvParserService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class CsvParserServiceTest extends TestCase
{
    private CsvParserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CsvParserService;
    }

    public function testSanitizeCellShouldPrefixFormulaCharactersWhenValueStartsWithFormulaChar(): void
    {
        // Act & Assert
        $this->assertEquals("'=SUM(A1)", $this->service->sanitizeCell('=SUM(A1)'));
        $this->assertEquals("'+123", $this->service->sanitizeCell('+123'));
        $this->assertEquals("'-456", $this->service->sanitizeCell('-456'));
        $this->assertEquals("'@mention", $this->service->sanitizeCell('@mention'));
    }

    public function testSanitizeCellShouldReturnTrimmedValueWhenNoFormulaChar(): void
    {
        // Act & Assert
        $this->assertEquals('normal text', $this->service->sanitizeCell('  normal text  '));
        $this->assertEquals('email@test.com', $this->service->sanitizeCell('email@test.com'));
    }

    public function testSanitizeCellShouldReturnEmptyWhenEmpty(): void
    {
        // Act & Assert
        $this->assertEquals('', $this->service->sanitizeCell(''));
        $this->assertEquals('', $this->service->sanitizeCell('   '));
    }

    public function testNormalizeHeaderShouldConvertToLowercaseWithUnderscores(): void
    {
        // Act & Assert
        $this->assertEquals('first_name', $this->service->normalizeHeader('First Name'));
        $this->assertEquals('email', $this->service->normalizeHeader('Email'));
        $this->assertEquals('zip_code', $this->service->normalizeHeader('Zip Code'));
    }

    public function testReadChunksShouldReturnChunkedRecordsWhenFileIsValid(): void
    {
        // Arrange
        $csvPath = $this->createValidCsvFile();

        // Act
        $chunks = iterator_to_array($this->service->readChunks($csvPath, 2));

        // Assert
        $this->assertIsArray($chunks);
        $this->assertCount(3, $chunks);
        $this->assertCount(2, $chunks[0]);
        $this->assertCount(2, $chunks[1]);
        $this->assertCount(1, $chunks[2]);
        $this->assertArrayHasKey('name', $chunks[0][0]);
        $this->assertArrayHasKey('email', $chunks[0][0]);
        $this->assertArrayHasKey('password', $chunks[0][0]);
    }

    public function testReadChunksShouldThrowWhenRequiredHeaderMissing(): void
    {
        // Arrange
        $csvPath = $this->createCsvWithMissingHeader();

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cabeçalho obrigatório ausente');

        iterator_to_array($this->service->readChunks($csvPath, 1000));
    }

    public function testCountRecordsShouldReturnCorrectCountWhenFileIsValid(): void
    {
        // Arrange
        $csvPath = $this->createValidCsvFile();

        // Act
        $count = $this->service->countRecords($csvPath);

        // Assert
        $this->assertEquals(5, $count);
    }

    public function testSanitizeRowShouldApplySanitizationToEachCell(): void
    {
        // Act
        $result = $this->service->sanitizeRow([
            'name' => '  John  ',
            'email' => 'john@test.com',
            'value' => '=1+1',
        ]);

        // Assert
        $this->assertEquals('John', $result['name']);
        $this->assertEquals('john@test.com', $result['email']);
        $this->assertEquals("'=1+1", $result['value']);
    }

    private function createValidCsvFile(): string
    {
        $content = "name,email,password\n";

        for ($i = 1; $i <= 5; $i++) {
            $content .= "User $i,user$i@test.com,pass$i\n";
        }

        $path = sys_get_temp_dir().'/test_import_'.uniqid().'.csv';
        file_put_contents($path, $content);

        return $path;
    }

    private function createCsvWithMissingHeader(): string
    {
        $content = "name,phone\n";
        $content .= "John,11999999999\n";

        $path = sys_get_temp_dir().'/test_import_missing_'.uniqid().'.csv';
        file_put_contents($path, $content);

        return $path;
    }
}
