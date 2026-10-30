<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;

class ContactQuery
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function build(array $filters = [], ?string $search = null, string $sort = 'name', string $direction = 'asc'): Builder
    {
        $query = Contact::query()
            ->search($search)
            ->when($filters['segment'] ?? null, fn (Builder $query, $segment) => $query->whereHas(
                'segments',
                fn (Builder $segments) => $segments->whereKey($segment),
            ))
            ->when($filters['type'] ?? null, fn (Builder $query, $type) => $query->where('contact_type', $type))
            ->when($filters['gender'] ?? null, fn (Builder $query, $gender) => $query->where('gender', $gender))
            ->when($filters['city'] ?? null, fn (Builder $query, $city) => $query->where('city', $city))
            ->when($filters['province'] ?? null, fn (Builder $query, $province) => $query->where('province', $province))
            ->when($filters['country'] ?? null, fn (Builder $query, $country) => $query->where('country', $country))
            ->when($filters['business_type'] ?? null, fn (Builder $query, $type) => $query->where('business_type', $type))
            ->when($filters['source'] ?? null, fn (Builder $query, $source) => $query->where('source', $source))
            ->when($filters['birthday_month'] ?? null, fn (Builder $query, $month) => $query->whereNotNull('birthdate')->whereMonth('birthdate', $month));

        $sort = in_array($sort, ['name', 'company', 'city', 'birthdate', 'created_at'], true) ? $sort : 'name';
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, ['birthdate', 'company'], true)) {
            $query->orderByRaw("{$sort} is null");
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('name');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function filterOptions(): array
    {
        return [
            'cities' => $this->distinct('city'),
            'provinces' => $this->distinct('province'),
            'countries' => $this->distinct('country'),
            'business_types' => $this->distinct('business_type'),
            'genders' => $this->distinct('gender'),
            'sources' => $this->distinct('source'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function distinct(string $column): array
    {
        return Contact::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }
}
