<?php

declare(strict_types=1);

namespace App\Models;

use App\Api\Support\Traits\Auditable;
use Database\Factories\ExportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @use HasFactory<ExportFactory>
 *
 * @property string                       $id
 * @property int                          $user_id
 * @property string                       $status
 * @property string|null                  $file_path
 * @property array<array-key, mixed>|null $filters
 * @property int                          $total_records
 * @property bool                         $compressed
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 *
 * @method static \Database\Factories\ExportFactory                    factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereCompressed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereTotalRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Export whereUserId($value)
 *
 * @mixin \Eloquent
 */
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Export extends Model
{
    use Auditable, HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'status',
        'file_path',
        'filters',
        'total_records',
        'compressed',
        'expires_at',
        'started_at',
        'finished_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'compressed' => 'boolean',
            'total_records' => 'integer',
            'expires_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
