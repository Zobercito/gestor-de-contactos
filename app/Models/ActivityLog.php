<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string $event
 * @property string $description
 * @property array<string, mixed>|null $properties
 * @property-read string $icon
 * @property-read User|null $user
 * @property-read Model|null $subject
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'event',
        'description',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getIconAttribute(): string
    {
        return match ($this->event) {
            'created' => 'plus',
            'updated' => 'pencil',
            'deleted' => 'trash',
            'imported' => 'arrow-up-tray',
            'note_added' => 'chat-bubble-left-ellipsis',
            'login' => 'key',
            default => 'ellipsis-horizontal',
        };
    }
}
