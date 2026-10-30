<?php

namespace App\Jobs;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Services\Csv\ContactImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessContactImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 900;

    public function __construct(public Import $import) {}

    public function handle(ContactImportService $service): void
    {
        if ($this->import->status !== ImportStatus::Pending) {
            return;
        }

        $service->process($this->import);
    }

    public function failed(?Throwable $exception): void
    {
        $this->import->update([
            'status' => ImportStatus::Failed,
            'errors' => [[
                'row' => null,
                'type' => 'error',
                'message' => $exception?->getMessage() ?? 'La importación no pudo completarse.',
            ]],
            'finished_at' => now(),
        ]);
    }
}
