<?php

declare(strict_types=1);

namespace App\Models;

use App\Api\Support\Traits\Auditable;
use Database\Factories\ImportFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/** @use HasFactory<ImportFactory> */
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string                    $id
 * @property int                       $user_id
 * @property string                    $status
 * @property int                       $progress
 * @property int                       $total_records
 * @property int                       $success_count
 * @property int                       $failure_count
 * @property string                    $file_path
 * @property string                    $original_filename
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null               $started_at
 * @property Carbon|null               $finished_at
 * @property Carbon|null               $created_at
 * @property Carbon|null               $updated_at
 * @property-read Collection<int, ImportFailure> $failures
 * @property-read int|null $failures_count
 * @property-read User $user
 *
 * @method static \Database\Factories\ImportFactory                    factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFailureCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereOriginalFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereSuccessCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereTotalRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Import extends Model
{
    use Auditable, HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'status',
        'progress',
        'total_records',
        'success_count',
        'failure_count',
        'file_path',
        'original_filename',
        'metadata',
        'started_at',
        'finished_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'progress' => 'integer',
            'total_records' => 'integer',
            'success_count' => 'integer',
            'failure_count' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ImportFailure, $this> */
    public function failures(): HasMany
    {
        return $this->hasMany(ImportFailure::class);
    }
}
