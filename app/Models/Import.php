<?php

namespace App\Models;

use App\Api\Support\Traits\Auditable;
use Database\Factories\ImportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @use HasFactory<ImportFactory> */
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property int $user_id
 * @property string $status
 * @property int $progress
 * @property int $total_records
 * @property int $success_count
 * @property int $failure_count
 * @property string $file_path
 * @property string $original_filename
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
