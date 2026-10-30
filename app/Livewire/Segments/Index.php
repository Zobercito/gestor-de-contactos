<?php

namespace App\Livewire\Segments;

use App\Models\Segment;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $color = '#C65D3B';

    public ?string $description = null;

    public bool $showForm = false;

    public function create(): void
    {
        Gate::authorize('create', Segment::class);

        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $segmentId): void
    {
        $segment = Segment::query()->findOrFail($segmentId);
        Gate::authorize('update', $segment);

        $this->editingId = $segment->id;
        $this->name = $segment->name;
        $this->color = $segment->color;
        $this->description = $segment->description;
        $this->showForm = true;
    }

    public function save(): void
    {
        Gate::authorize($this->editingId === null ? 'create' : 'update', $this->editingId === null ? Segment::class : Segment::findOrFail($this->editingId));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('segments', 'name')->ignore($this->editingId)],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'description' => ['nullable', 'string', 'max:180'],
        ]);

        if ($this->editingId === null) {
            Segment::create($validated);
            Flux::toast(text: 'Segmento creado.', variant: 'success');
        } else {
            Segment::query()->whereKey($this->editingId)->firstOrFail()->update($validated);
            Flux::toast(text: 'Segmento actualizado.', variant: 'success');
        }

        $this->resetForm();
    }

    public function delete(int $segmentId): void
    {
        $segment = Segment::query()->withCount('contacts')->findOrFail($segmentId);
        Gate::authorize('delete', $segment);

        $segment->delete();

        Flux::toast(text: 'Segmento eliminado. Los contactos se conservaron.', variant: 'success');

        if ($this->editingId === $segmentId) {
            $this->resetForm();
        }
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'description', 'showForm');
        $this->color = '#C65D3B';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.segments.index', [
            'segments' => Segment::query()->withCount('contacts')->orderBy('name')->get(),
            'palette' => ['#C65D3B', '#7A8B6F', '#D9A441', '#8C6A9E', '#4F7C8A', '#B56576', '#5C7A5C', '#A9784E'],
        ])->layout('components.layouts.app');
    }
}
