<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static bool $enabled = true;

    public static ?int $actorId = null;

    public function log(string $event, Model $subject, string $description, array $properties = []): ?ActivityLog
    {
        if (! self::$enabled) {
            return null;
        }

        return ActivityLog::create([
            'user_id' => self::$actorId ?? auth()->id(),
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'event' => $event,
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
        ]);
    }
}
