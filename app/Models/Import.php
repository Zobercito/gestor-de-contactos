<?php

namespace App\Models;

use App\Enums\ImportStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $filename
 * @property string $original_name
 * @property string|null $path
 * @property ImportStatus $status
 * @property string|null $delimiter
 * @property string|null $encoding
 * @property int $total_rows
 * @property int|null $created_count
 * @property int|null $skipped_count
 * @property int|null $duplicate_count
 * @property int|null $error_count
 * @property array<string, string|null>|null $mapping
 * @property array<int, array<string, mixed>>|null $errors
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property-read string $summary
 * @property-read User $user
 */
class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'original_name',
        'path',
        'status',
        'delimiter',
        'encoding',
        'total_rows',
        'created_count',
        'skipped_count',
        'duplicate_count',
        'error_count',
        'mapping',
        'errors',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportStatus::class,
            'mapping' => 'array',
            'errors' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFinished(): bool
    {
        return $this->status->isFinished();
    }

    public function progress(): int
    {
        if ($this->total_rows === 0) {
            return $this->isFinished() ? 100 : 0;
        }

        $processed = $this->created_count + $this->skipped_count + $this->error_count;

        return $this->isFinished() ? 100 : (int) min(99, round(($processed / $this->total_rows) * 100));
    }

    public function getSummaryAttribute(): string
    {
        return sprintf(
            '%d creados · %d omitidos · %d errores',
            $this->created_count,
            $this->skipped_count,
            $this->error_count,
        );
    }
}
