<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use App\Models\Segment;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Contact $contact = null;

    public string $name = '';

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $company = null;

    public ?string $job_title = null;

    public string $contact_type = '';

    public ?string $business_type = null;

    public ?string $gender = null;

    public ?string $city = null;

    public ?string $province = null;

    public ?string $country = 'Panamá';

    public ?string $birthdate = null;

    public ?string $source = null;

    public ?string $associated_brand = null;

    public ?string $associated_company = null;

    /** @var array<int, int|string> */
    public array $segmentIds = [];

    public function mount(?Contact $contact = null): void
    {
        $this->contact = $contact;

        if ($contact === null) {
            Gate::authorize('create', Contact::class);

            return;
        }

        Gate::authorize('update', $contact);

        $this->fill([
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'company' => $contact->company,
            'job_title' => $contact->job_title,
            'contact_type' => $contact->contact_type->value ?? '',
            'business_type' => $contact->business_type,
            'gender' => $contact->gender,
            'city' => $contact->city,
            'province' => $contact->province,
            'country' => $contact->country,
            'birthdate' => $contact->birthdate?->format('Y-m-d'),
            'source' => $contact->source,
            'associated_brand' => $contact->associated_brand,
            'associated_company' => $contact->associated_company,
        ]);

        $this->segmentIds = $contact->segments->pluck('id')->all();
    }

    public function save(): void
    {
        Gate::authorize($this->contact === null ? 'create' : 'update', $this->contact ?? Contact::class);

        $this->email = filled($this->email) ? mb_strtolower(trim($this->email)) : null;
        $this->phone = filled($this->phone) ? preg_replace('/[^\d+]/', '', trim($this->phone)) : null;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:190', Rule::unique('contacts', 'email')->ignore($this->contact?->id)],
            'phone' => ['nullable', 'string', 'max:40', Rule::unique('contacts', 'phone')->ignore($this->contact?->id)],
            'company' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'contact_type' => ['nullable', Rule::in(['B2B', 'B2C'])],
            'business_type' => ['nullable', 'string', 'max:80'],
            'gender' => ['nullable', 'string', 'max:40'],
            'city' => ['nullable', 'string', 'max:80'],
            'province' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'birthdate' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'source' => ['nullable', 'string', 'max:80'],
            'associated_brand' => ['nullable', 'string', 'max:120'],
            'associated_company' => ['nullable', 'string', 'max:150'],
            'segmentIds' => ['array'],
            'segmentIds.*' => ['integer', 'exists:segments,id'],
        ]);

        if (blank($validated['email']) && blank($validated['phone'])) {
            $this->addError('email', 'Añade al menos un correo o un teléfono.');

            return;
        }

        $attributes = collect($validated)
            ->except('segmentIds')
            ->map(fn ($value) => $value === '' ? null : $value)
            ->all();

        if ($this->contact === null) {
            $attributes['created_by'] = auth()->id();
            $attributes['updated_by'] = auth()->id();
            $contact = Contact::create($attributes);
            $message = 'Contacto creado.';
        } else {
            $attributes['updated_by'] = auth()->id();
            $this->contact->update($attributes);
            $contact = $this->contact->refresh();
            $message = 'Contacto actualizado.';
        }

        $contact->segments()->sync($this->segmentIds);

        Flux::toast(text: $message, variant: 'success');

        $this->redirectRoute('contacts.show', $contact, navigate: true);
    }

    public function delete(): void
    {
        if ($this->contact === null) {
            return;
        }

        Gate::authorize('delete', $this->contact);

        $this->contact->delete();
        Flux::toast(text: 'Contacto eliminado.', variant: 'success');

        $this->redirectRoute('contacts.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.contacts.form', [
            'segments' => Segment::query()->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
