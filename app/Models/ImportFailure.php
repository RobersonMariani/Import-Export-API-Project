<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int                     $id
 * @property string                  $import_id
 * @property int                     $line_number
 * @property array<array-key, mixed> $payload
 * @property string                  $error_message
 * @property Carbon|null             $created_at
 * @property Carbon|null             $updated_at
 * @property-read Import $import
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereLineNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportFailure whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ImportFailure extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'import_id',
        'line_number',
        'payload',
        'error_message',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'line_number' => 'integer',
        ];
    }

    /** @return BelongsTo<Import, $this> */
    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
