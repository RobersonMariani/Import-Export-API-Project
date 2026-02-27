<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\UseCases;

use App\Api\Modules\Import\Data\CreateImportData;
use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Api\Modules\Import\Services\ImportService;
use App\Api\Modules\Import\UseCases\CreateImportUseCase;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\TestCase;

#[Group('import')]
class CreateImportUseCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function testExecuteShouldReturnImportWhenDataIsValid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('users.csv', 100, 'text/csv');
        $data = CreateImportData::validateAndCreate(['file' => $file]);

        $expectedImport = Import::factory()->create([
            'user_id' => $user->id,
            'original_filename' => 'users.csv',
        ]);

        $this->instance(
            ImportRepository::class,
            Mockery::mock(ImportRepository::class, function (MockInterface $mock) use ($expectedImport, $user) {
                $mock->shouldReceive('create')
                    ->once()
                    ->with(Mockery::on(function (array $data) use ($user) {
                        return $data['user_id'] === $user->id
                            && $data['original_filename'] === 'users.csv'
                            && isset($data['file_path'])
                            && isset($data['status']);
                    }))
                    ->andReturn($expectedImport);
            }),
        );

        $this->instance(
            ImportService::class,
            Mockery::mock(ImportService::class, function (MockInterface $mock) use ($expectedImport) {
                $mock->shouldReceive('startImport')
                    ->once()
                    ->with($expectedImport);
            }),
        );

        // Act
        $useCase = app()->make(CreateImportUseCase::class);
        $result = $useCase->execute($data, $user->id);

        // Assert
        $this->assertInstanceOf(Import::class, $result);
        $this->assertEquals($expectedImport->original_filename, $result->original_filename);
    }

    public function testExecuteShouldThrowWhenUserIdIsInvalid(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('users.csv', 100, 'text/csv');
        $data = CreateImportData::validateAndCreate(['file' => $file]);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Usuário não autenticado.');

        $useCase = app()->make(CreateImportUseCase::class);
        $useCase->execute($data, 0);
    }
}
