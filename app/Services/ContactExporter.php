<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ContactExporter
{
    /**
     * @param  Builder<Contact>  $query
     * @param  resource  $output
     */
    public function write(Builder $query, $output, ?int $limit = null): int
    {
        $limit ??= (int) config('contacts.export.max_rows');

        fwrite($output, "\xEF\xBB\xBF");

        $this->put($output, [
            'Nombre', 'Correo', 'Teléfono', 'Empresa', 'Cargo', 'Tipo de cliente',
            'Tipo de negocio', 'Género', 'Ciudad', 'Provincia', 'País',
            'Fecha de nacimiento', 'Origen', 'Marca asociada', 'Empresa asociada', 'Segmentos',
        ]);

        $count = 0;

        foreach ($query->with('segments')->limit($limit)->cursor() as $contact) {
            $this->put($output, [
                $contact->name,
                $contact->email,
                $contact->phone,
                $contact->company,
                $contact->job_title,
                $contact->contact_type?->label(),
                $contact->business_type,
                $contact->gender,
                $contact->city,
                $contact->province,
                $contact->country,
                $contact->birthdate?->format('d/m/Y'),
                $contact->source,
                $contact->associated_brand,
                $contact->associated_company,
                $contact->segments->pluck('name')->implode(', '),
            ]);

            $count++;
        }

        return $count;
    }

    public function filename(string $suffix = ''): string
    {
        $suffix = Str::slug($suffix);

        return 'contactos'
            .($suffix !== '' ? '-'.$suffix : '')
            .'-'.Carbon::now()->format('Y-m-d-Hi')
            .'.csv';
    }

    /**
     * @param  resource  $output
     * @param  array<int, string|null>  $values
     */
    private function put($output, array $values): void
    {
        fputcsv($output, array_map(fn ($value) => $this->escape((string) $value), $values), ',', '"', '');
    }

    private function escape(string $value): string
    {
        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
