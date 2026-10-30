<?php

namespace App\Livewire\Imports;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Services\Csv\ContactImportService;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Wizard extends Component
{
    use WithFileUploads;

    public $file = null;

    public int $step = 1;

    public ?int $importId = null;

    /** @var array<int, string> */
    public array $headers = [];

    /** @var array<int, array<string, string|null>> */
    public array $preview = [];

    /** @var array<string, string|null> */
    public array $mapping = [];

    /** @var array<string, array{label: string, aliases: array<int, string>}> */
    public array $fields = [];

    /** @var array{counts: array<string, int>, rows: array<int, array<string, mixed>>}|null */
    public ?array $analysis = null;

    public string $delimiterLabel = '';

    public string $encoding = '';

    public int $totalRows = 0;

    public string $errorMessage = '';

    public function mount(): void
    {
        Gate::authorize('create', Import::class);
    }

    public function upload(ContactImportService $service): void
    {
        Gate::authorize('create', Import::class);

        $this->reset('errorMessage');

        $maxKb = (int) config('contacts.import.max_file_kb');
        $maxKb = config('demo.enabled') ? min($maxKb, (int) config('demo.max_upload_kb')) : $maxKb;

        $this->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:'.$maxKb],
        ], [
            'file.required' => 'Selecciona un archivo CSV.',
            'file.mimes' => 'El archivo debe ser CSV o TXT.',
            'file.max' => 'El archivo no puede superar '.number_format($maxKb / 1024, 1).' MB.',
        ]);

        try {
            $import = $service->createImport(auth()->user(), $this->file);
            $inspection = $service->inspect($import);

            $this->importId = $import->id;
            $this->headers = $inspection['headers'];
            $this->preview = $inspection['preview'];
            $this->mapping = $inspection['mapping'];
            $this->fields = $inspection['fields'];
            $this->delimiterLabel = $inspection['delimiter_label'];
            $this->encoding = $inspection['encoding'];
            $this->totalRows = $inspection['total_rows'];
            $this->step = 2;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        }
    }

    public function analyze(ContactImportService $service): void
    {
        Gate::authorize('create', Import::class);

        $this->reset('errorMessage');

        if (blank($this->mapping['name'] ?? null)) {
            $this->addError('mapping.name', 'Empareja la columna que contiene el nombre.');

            return;
        }

        if (blank($this->mapping['email'] ?? null) && blank($this->mapping['phone'] ?? null)) {
            $this->addError('mapping.email', 'Empareja al menos una columna de correo o de teléfono.');

            return;
        }

        $import = $this->import();

        try {
            $import->update(['mapping' => $this->mapping]);
            $this->analysis = $service->analyze($import, $this->mapping);
            $this->step = 3;
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        }
    }

    public function confirm(ContactImportService $service): void
    {
        Gate::authorize('create', Import::class);

        $import = $this->import();

        if ($this->analysis === null) {
            $this->step = 2;

            return;
        }

        try {
            if ($this->analysis['counts']['ready'] === 0) {
                $this->addError('analysis', 'No hay filas listas para importar con este emparejamiento.');

                return;
            }

            $import->update([
                'mapping' => $this->mapping,
                'status' => ImportStatus::Pending,
                'errors' => null,
            ]);

            $queued = $service->queueOrProcess($import);

            Flux::toast(
                text: $queued ? 'Importación en cola. Te mostramos el avance.' : 'Importación completada.',
                variant: 'success',
            );

            $this->redirectRoute('imports.show', $import, navigate: true);
        } catch (Throwable $exception) {
            Flux::toast(text: 'No se pudo completar la importación.', variant: 'danger');
            $this->redirectRoute('imports.show', $import, navigate: true);
        }
    }

    public function back(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function cancel(): void
    {
        if ($this->importId !== null) {
            $import = Import::query()->find($this->importId);

            if ($import && $import->user_id === auth()->id() && $import->status === ImportStatus::Pending) {
                Storage::disk(config('contacts.import.disk'))->delete($import->path);
                $import->delete();
            }
        }

        $this->reset();
    }

    public function removeFile(): void
    {
        $this->reset('file', 'errorMessage');
    }

    private function import(): Import
    {
        $import = Import::query()->findOrFail($this->importId);

        abort_unless($import->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        return $import;
    }

    public function render()
    {
        return view('livewire.imports.wizard', [
            'queueThreshold' => (int) config('contacts.import.queue_threshold'),
            'maxRows' => config('demo.enabled')
                ? min((int) config('contacts.import.max_rows'), (int) config('demo.max_import_rows'))
                : (int) config('contacts.import.max_rows'),
        ])->layout('components.layouts.app');
    }
}
