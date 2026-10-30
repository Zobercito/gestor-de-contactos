<?php

namespace App\Observers;

use App\Models\Segment;
use App\Services\ActivityLogger;

class SegmentObserver
{
    public function __construct(private ActivityLogger $logger) {}

    public function created(Segment $segment): void
    {
        $this->logger->log('created', $segment, "Creó el segmento {$segment->name}");
    }

    public function updated(Segment $segment): void
    {
        $changes = collect($segment->getChanges())->except(['updated_at'])->all();

        if ($changes === []) {
            return;
        }

        $this->logger->log('updated', $segment, "Actualizó el segmento {$segment->name}", [
            'changes' => $changes,
        ]);
    }

    public function deleted(Segment $segment): void
    {
        $this->logger->log('deleted', $segment, "Eliminó el segmento {$segment->name}");
    }
}
