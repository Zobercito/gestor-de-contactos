<?php

namespace App\Livewire\Activity;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $event = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', ActivityLog::class);
    }

    public function updated(string $property): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::query()
            ->with(['user', 'subject'])
            ->when($this->event !== '', fn ($query) => $query->where('event', $this->event))
            ->latest()
            ->paginate(25);

        $events = ActivityLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return view('livewire.activity.index', [
            'logs' => $logs,
            'events' => $events,
        ])->layout('components.layouts.app');
    }
}
