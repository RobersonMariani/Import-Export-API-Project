<?php

namespace App\Api\Modules\Import\Tests\Services;

use App\Api\Modules\Import\Jobs\ProcessImportJob;
use App\Api\Modules\Import\Services\CsvParserService;
use App\Api\Modules\Import\Services\ImportService;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testStartImportShouldDispatchProcessImportJobWhenNoConcurrencyLock(): void
    {
        // Arrange
        Bus::fake();
        $user = User::factory()->create();
        $import = Import::factory()->create(['user_id' => $user->id]);

        $csvParserMock = Mockery::mock(CsvParserService::class);

        $service = new ImportService(
            $csvParserMock,
            app(\App\Api\Modules\Import\Repositories\ImportRepository::class),
        );

        // Act
        $service->startImport($import);

        // Assert
        Bus::assertDispatched(ProcessImportJob::class, function (ProcessImportJob $job) use ($import) {
            return $job->importId === $import->id;
        });
    }

    public function testStartImportShouldThrowWhenImportAlreadyProcessing(): void
    {
        // Arrange
        Bus::fake();
        $user = User::factory()->create();
        $import = Import::factory()->create(['user_id' => $user->id]);
        Cache::put('import:processing:'.$import->id, true, 3600);

        $service = new ImportService(
            app(CsvParserService::class),
            app(\App\Api\Modules\Import\Repositories\ImportRepository::class),
        );

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Import já está em processamento');

        $service->startImport($import);
    }

    public function testGetChunksShouldDelegateToCsvParserService(): void
    {
        // Arrange
        $expectedChunks = [[['name' => 'Test', 'email' => 'test@test.com', 'password' => 'pass']]];
        $filePath = '/tmp/test.csv';

        $generator = (function () use ($expectedChunks): \Generator {
            foreach ($expectedChunks as $index => $chunk) {
                yield $index => $chunk;
            }
        })();

        $csvParserMock = Mockery::mock(CsvParserService::class, function (MockInterface $mock) use ($filePath, $generator) {
            $mock->shouldReceive('readChunks')
                ->once()
                ->with($filePath, 500)
                ->andReturn($generator);
        });

        $service = new ImportService(
            $csvParserMock,
            app(\App\Api\Modules\Import\Repositories\ImportRepository::class),
        );

        // Act
        $result = iterator_to_array($service->getChunks($filePath, 500));

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals('Test', $result[0][0]['name']);
    }

    public function testGetTotalRecordsShouldDelegateToCsvParserService(): void
    {
        // Arrange
        $filePath = '/tmp/test.csv';

        $csvParserMock = Mockery::mock(CsvParserService::class, function (MockInterface $mock) use ($filePath) {
            $mock->shouldReceive('countRecords')
                ->once()
                ->with($filePath)
                ->andReturn(100);
        });

        $service = new ImportService(
            $csvParserMock,
            app(\App\Api\Modules\Import\Repositories\ImportRepository::class),
        );

        // Act
        $result = $service->getTotalRecords($filePath);

        // Assert
        $this->assertEquals(100, $result);
    }
}
