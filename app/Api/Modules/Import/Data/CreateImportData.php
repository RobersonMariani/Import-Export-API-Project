<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;

#[MapName(SnakeCaseMapper::class)]
class CreateImportData extends Data
{
    public function __construct(
        public UploadedFile $file,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:51200'],
        ];
    }
}
