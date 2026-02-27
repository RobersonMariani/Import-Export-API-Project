<?php

namespace App\Api\Modules\Import\Tests\Integrations;

use App\Api\Modules\Import\Jobs\ProcessImportJob;
use App\Api\Modules\Import\Tests\Assertables\ImportAssertableJson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class CreateImportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/imports';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Bus::fake();
    }

    public function testShouldReturnAcceptedWhenFileIsValid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $file = UploadedFile::fake()->create('users.csv', 100, 'text/csv');

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->post(self::ENDPOINT, ['file' => $file], ['Accept' => 'application/json'])
            ->assertStatus(202)
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    ImportAssertableJson::schema($json);
                })->etc();
            });

        Bus::assertDispatched(ProcessImportJob::class);
    }

    public function testShouldReturnUnprocessableWhenFileIsMissing(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT, [])
            ->assertUnprocessable();
    }

    public function testShouldReturnUnprocessableWhenFileHasWrongMimeType(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->post(self::ENDPOINT, ['file' => $file], ['Accept' => 'application/json'])
            ->assertUnprocessable();
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('users.csv', 100, 'text/csv');

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->post(self::ENDPOINT, ['file' => $file])
            ->assertUnauthorized();
    }
}
