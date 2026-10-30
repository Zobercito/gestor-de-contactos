<?php

namespace App\Livewire\Imports;

use App\Enums\ImportStatus;
use App\Models\Import;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updated(string $property): void
    {
        $this->resetPage();
    }

    public function delete(int $importId): void
    {
        $import = Import::query()->findOrFail($importId);
        Gate::authorize('delete', $import);

        Storage::disk(config('contacts.import.disk'))->delete($import->path);
        $import->delete();

        Flux::toast(text: 'Importación eliminada del historial.', variant: 'success');
        $this->resetPage();
    }

    public function render()
    {
        $imports = Import::query()
            ->with('user')
            ->when($this->search !== '', fn ($query) => $query->whereRaw('lower(original_name) like ?', ['%'.mb_strtolower($this->search).'%']))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);

        $finished = Import::query()->where('status', ImportStatus::Completed);

        return view('livewire.imports.index', [
            'imports' => $imports,
            'hasActive' => Import::query()->whereIn('status', [ImportStatus::Pending, ImportStatus::Processing])->exists(),
            'stats' => [
                'completed' => (clone $finished)->count(),
                'created' => (int) (clone $finished)->sum('created_count'),
                'duplicates' => (int) (clone $finished)->sum('duplicate_count'),
                'errors' => (int) (clone $finished)->sum('error_count'),
            ],
        ])->layout('components.layouts.app');
    }
}
