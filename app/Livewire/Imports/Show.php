<?php

namespace App\Livewire\Imports;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Services\Csv\ContactImportService;
use App\Services\Csv\HeaderMapper;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Throwable;

class Show extends Component
{
    public Import $import;

    public function mount(Import $import): void
    {
        Gate::authorize('view', $import);
        $this->import = $import;
    }

    public function refresh(): void
    {
        $this->import->refresh();
    }

    public function retry(ContactImportService $service): void
    {
        Gate::authorize('create', Import::class);
        abort_unless($this->import->user_id === auth()->id() || auth()->user()->isAdmin(), 403);
        abort_unless($this->import->status === ImportStatus::Failed, 422);

        $this->import->update([
            'status' => ImportStatus::Pending,
            'created_count' => 0,
            'skipped_count' => 0,
            'duplicate_count' => 0,
            'error_count' => 0,
            'errors' => null,
            'started_at' => null,
            'finished_at' => null,
        ]);

        try {
            $service->queueOrProcess($this->import);
            Flux::toast(text: 'Importación reiniciada.', variant: 'success');
        } catch (Throwable) {
            Flux::toast(text: 'La importación volvió a fallar.', variant: 'danger');
        }

        $this->import->refresh();
    }

    public function delete(): void
    {
        Gate::authorize('delete', $this->import);

        Storage::disk(config('contacts.import.disk'))->delete($this->import->path);
        $this->import->delete();

        Flux::toast(text: 'Importación eliminada del historial.', variant: 'success');
        $this->redirectRoute('imports.index', navigate: true);
    }

    public function render()
    {
        $mapping = collect((array) $this->import->mapping)
            ->mapWithKeys(fn ($header, $field) => [
                HeaderMapper::fields()[$field]['label'] ?? $field => $header,
            ]);

        $issues = collect((array) $this->import->errors);

        return view('livewire.imports.show', [
            'mapping' => $mapping,
            'issues' => $issues,
            'warningsCount' => $issues->where('type', 'warning')->count(),
        ])->layout('components.layouts.app');
    }
}
