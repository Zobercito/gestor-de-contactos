<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use App\Models\Segment;
use App\Services\ContactQuery;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $sort = 'name';

    public string $direction = 'asc';

    public int $perPage = 10;

    public bool $showFilters = false;

    /**
     * @var array<string, string>
     */
    public array $filters = [
        'segment' => '',
        'type' => '',
        'gender' => '',
        'city' => '',
        'province' => '',
        'country' => '',
        'business_type' => '',
        'source' => '',
        'birthday_month' => '',
    ];

    public function mount(): void
    {
        $segment = request()->query('segment');

        if (is_scalar($segment) && Segment::query()->whereKey($segment)->exists()) {
            $this->filters['segment'] = (string) $segment;
        }
    }

    public function updated(string $property): void
    {
        if ($property === 'perPage') {
            $this->perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;
        }

        if ($property !== 'showFilters') {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->filters = array_fill_keys(array_keys($this->filters), '');
        $this->search = '';
        $this->sort = 'name';
        $this->direction = 'asc';
        $this->resetPage();
    }

    public function removeFilter(string $key): void
    {
        if (array_key_exists($key, $this->filters)) {
            $this->filters[$key] = '';
        }

        $this->resetPage();
    }

    public function delete(int $contactId): void
    {
        $contact = Contact::query()->findOrFail($contactId);
        Gate::authorize('delete', $contact);

        $contact->delete();

        Flux::toast(text: 'Contacto eliminado. Sus segmentos y notas también se retiraron.', variant: 'success');
        $this->resetPage();
    }

    public function exportUrl(): string
    {
        return route('contacts.export', array_filter([
            'search' => $this->search,
            'sort' => $this->sort,
            'direction' => $this->direction,
            'segment' => $this->filters['segment'],
            'type' => $this->filters['type'],
            'gender' => $this->filters['gender'],
            'city' => $this->filters['city'],
            'province' => $this->filters['province'],
            'country' => $this->filters['country'],
            'business_type' => $this->filters['business_type'],
            'source' => $this->filters['source'],
            'birthday_month' => $this->filters['birthday_month'],
        ], fn ($value) => $value !== null && $value !== ''));
    }

    public function render()
    {
        $queryService = app(ContactQuery::class);
        $query = $queryService->build($this->filters, $this->search, $this->sort, $this->direction);
        $contacts = $query->with('segments')->paginate($this->perPage);

        $activeFilters = collect($this->filters)->filter(fn ($value) => $value !== '' && $value !== null);
        $segmentNames = Segment::query()->pluck('name', 'id');

        return view('livewire.contacts.index', [
            'contacts' => $contacts,
            'segments' => Segment::query()->orderBy('name')->get(),
            'options' => $queryService->filterOptions(),
            'activeFilters' => $activeFilters,
            'segmentNames' => $segmentNames,
            'exportUrl' => $this->exportUrl(),
        ])->layout('components.layouts.app');
    }
}
