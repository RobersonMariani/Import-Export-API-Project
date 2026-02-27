<?php

namespace App\Models;

use App\Api\Support\Traits\Auditable;
use Database\Factories\ExportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @use HasFactory<ExportFactory> */
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
