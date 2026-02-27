<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\UseCases;

use App\Api\Modules\Export\Data\CreateExportData;
use App\Api\Modules\Export\Jobs\ProcessExportJob;
use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Export\UseCases\CreateExportUseCase;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class CreateExportUseCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    public function testExecuteShouldReturnExportWhenDataIsValid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = CreateExportData::validateAndCreate([]);

        $expectedExport = Export::factory()->create([
            'user_id' => $user->id,
            'status' => 'queued',
        ]);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($expectedExport, $user) {
                $mock->shouldReceive('create')
                    ->once()
                    ->with(Mockery::on(function (array $payload) use ($user) {
                        return $payload['user_id'] === $user->id
                            && $payload['status'] === 'queued'
                            && $payload['file_path'] === null
                            && $payload['total_records'] === 0
                            && $payload['compressed'] === false;
                    }))
                    ->andReturn($expectedExport);
            }),
        );

        // Act
        $useCase = app()->make(CreateExportUseCase::class);
        $result = $useCase->execute($data, $user->id);

        // Assert
        $this->assertInstanceOf(Export::class, $result);
        $this->assertEquals($expectedExport->id, $result->id);
    }

    public function testExecuteShouldDispatchProcessExportJobWhenExportCreated(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = CreateExportData::validateAndCreate([]);
        $expectedExport = Export::factory()->create(['user_id' => $user->id]);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($expectedExport) {
                $mock->shouldReceive('create')
                    ->once()
                    ->andReturn($expectedExport);
            }),
        );

        // Act
        $useCase = app()->make(CreateExportUseCase::class);
        $useCase->execute($data, $user->id);

        // Assert
        Bus::assertDispatched(ProcessExportJob::class, function ($job) use ($expectedExport) {
            return $job->exportId === $expectedExport->id;
        });
    }

    public function testExecuteShouldPassFiltersAndCompressedWhenProvided(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = CreateExportData::validateAndCreate([
            'filters' => ['search' => 'test', 'role' => 'admin'],
            'compressed' => true,
        ]);
        $expectedExport = Export::factory()->create(['user_id' => $user->id]);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($expectedExport) {
                $mock->shouldReceive('create')
                    ->once()
                    ->with(Mockery::on(function (array $payload) {
                        return $payload['compressed'] === true
                            && isset($payload['filters'])
                            && $payload['filters']['search'] === 'test'
                            && $payload['filters']['role'] === 'admin';
                    }))
                    ->andReturn($expectedExport);
            }),
        );

        // Act
        $useCase = app()->make(CreateExportUseCase::class);
        $result = $useCase->execute($data, $user->id);

        // Assert
        $this->assertInstanceOf(Export::class, $result);
    }
}
