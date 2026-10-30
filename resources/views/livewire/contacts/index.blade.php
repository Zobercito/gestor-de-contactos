<div class="mx-auto w-full max-w-7xl space-y-6">
    <x-page-header eyebrow="Directorio" title="Contactos" description="Busca, filtra y cuida cada relación desde un solo lugar.">
        <x-slot:actions>
            <a href="{{ $exportUrl }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-cream-300 bg-white px-4 py-2.5 text-sm font-semibold text-ink-700 transition hover:border-clay-300 hover:text-clay-700 dark:border-white/10 dark:bg-white/5 dark:text-cream-100">
                <flux:icon.arrow-down-tray class="size-4" /> Exportar CSV
            </a>
            @if (auth()->user()->canWrite())
                <a href="{{ route('contacts.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-2xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 shadow-[0_12px_24px_-14px_rgba(198,93,59,1)] transition hover:bg-clay-600">
                    <flux:icon.plus class="size-4" /> Nuevo contacto
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <section class="ct-card overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-cream-200/80 p-4 sm:flex-row sm:items-center dark:border-white/5">
            <div class="relative flex-1">
                <flux:icon.magnifying-glass class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-300" />
                <input type="search" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por nombre, correo, empresa o ciudad…"
                       class="ct-input !pl-10" />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="$toggle('showFilters')"
                        class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2.5 text-sm font-medium transition {{ $showFilters || $activeFilters->isNotEmpty() ? 'border-clay-300 bg-clay-50 text-clay-700 dark:border-clay-400/40 dark:bg-clay-500/10 dark:text-clay-200' : 'border-cream-300 bg-white text-ink-600 hover:border-clay-300 dark:border-white/10 dark:bg-white/5 dark:text-cream-200' }}">
                    <flux:icon.adjustments-horizontal class="size-4" />
                    Filtros
                    @if ($activeFilters->isNotEmpty())
                        <span class="flex size-5 items-center justify-center rounded-full bg-clay-500 text-[10px] font-bold text-white">{{ $activeFilters->count() }}</span>
                    @endif
                </button>

                <select wire:model.live="sort" class="ct-input !w-auto !py-2.5" aria-label="Ordenar por">
                    <option value="name">Nombre</option>
                    <option value="company">Empresa</option>
                    <option value="city">Ciudad</option>
                    <option value="created_at">Más recientes</option>
                    <option value="birthdate">Cumpleaños</option>
                </select>

                <select wire:model.live="perPage" class="ct-input !w-auto !py-2.5" aria-label="Contactos por página">
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                </select>
            </div>
        </div>

        @if ($showFilters)
            <div class="border-b border-cream-200/80 bg-cream-100/50 p-4 dark:border-white/5 dark:bg-white/[0.02]">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="block">
                        <span class="ct-label text-xs">Segmento</span>
                        <select wire:model.live="filters.segment" class="ct-input mt-1">
                            <option value="">Todos</option>
                            @foreach ($segments as $segment)
                                <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="ct-label text-xs">Tipo de cliente</span>
                        <select wire:model.live="filters.type" class="ct-input mt-1">
                            <option value="">Todos</option>
                            <option value="B2B">B2B · Empresas</option>
                            <option value="B2C">B2C · Personas</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="ct-label text-xs">Ciudad</span>
                        <select wire:model.live="filters.city" class="ct-input mt-1">
                            <option value="">Todas</option>
                            @foreach ($options['cities'] as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="ct-label text-xs">Provincia</span>
                        <select wire:model.live="filters.province" class="ct-input mt-1">
                            <option value="">Todas</option>
                            @foreach ($options['provinces'] as $province)
                                <option value="{{ $province }}">{{ $province }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="ct-label text-xs">Género</span>
                        <select wire:model.live="filters.gender" class="ct-input mt-1">
                            <option value="">Todos</option>
                            @foreach ($options['genders'] as $gender)
                                <option value="{{ $gender }}">{{ $gender }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="ct-label text-xs">Tipo de negocio</span>
                        <select wire:model.live="filters.business_type" class="ct-input mt-1">
                            <option value="">Todos</option>
                            @foreach ($options['business_types'] as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="ct-label text-xs">Origen</span>
                        <select wire:model.live="filters.source" class="ct-input mt-1">
                            <option value="">Todos</option>
                            @foreach ($options['sources'] as $source)
                                <option value="{{ $source }}">{{ $source }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="ct-label text-xs">Cumpleaños en</span>
                        <select wire:model.live="filters.birthday_month" class="ct-input mt-1">
                            <option value="">Cualquier mes</option>
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="mt-3 flex justify-end">
                    <button type="button" wire:click="resetFilters" class="text-xs font-semibold text-clay-600 hover:text-clay-700 dark:text-clay-300">
                        Limpiar búsqueda y filtros
                    </button>
                </div>
            </div>
        @endif

        @if ($activeFilters->isNotEmpty() || $search !== '')
            <div class="flex flex-wrap items-center gap-2 border-b border-cream-200/80 px-4 py-3 dark:border-white/5">
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Filtros activos</span>

                @if ($search !== '')
                    <span class="ct-chip">
                        «{{ $search }}»
                        <button type="button" wire:click="$set('search', '')" class="text-ink-400 hover:text-clay-600" aria-label="Quitar búsqueda">
                            <flux:icon.x-mark class="size-3" />
                        </button>
                    </span>
                @endif

                @foreach ($activeFilters as $key => $value)
                    @php
                        $labels = [
                            'segment' => 'Segmento',
                            'type' => 'Tipo',
                            'gender' => 'Género',
                            'city' => 'Ciudad',
                            'province' => 'Provincia',
                            'country' => 'País',
                            'business_type' => 'Negocio',
                            'source' => 'Origen',
                            'birthday_month' => 'Mes',
                        ];
                        $display = $key === 'segment'
                            ? ($segmentNames[$value] ?? $value)
                            : ($key === 'birthday_month'
                                ? \Carbon\Carbon::create()->month((int) $value)->translatedFormat('F')
                                : $value);
                    @endphp
                    <span class="ct-chip">
                        {{ $labels[$key] ?? $key }}: {{ $display }}
                        <button type="button" wire:click="removeFilter('{{ $key }}')" class="text-ink-400 hover:text-clay-600" aria-label="Quitar filtro">
                            <flux:icon.x-mark class="size-3" />
                        </button>
                    </span>
                @endforeach
            </div>
        @endif

        @if ($contacts->isEmpty())
            <div class="p-6">
                <x-empty-state icon="users" title="No encontramos contactos" description="Prueba con otra búsqueda o quita algunos filtros.">
                    <x-slot:actions>
                        <button type="button" wire:click="resetFilters" class="ct-link text-sm">Limpiar filtros</button>
                    </x-slot:actions>
                </x-empty-state>
            </div>
        @else
            <div class="overflow-x-auto ct-scrollbar">
                <table class="ct-table min-w-[760px]">
                    <thead>
                        <tr>
                            <th>Contacto</th>
                            <th>Segmentos</th>
                            <th>Ubicación</th>
                            <th>Tipo</th>
                            <th>Cumpleaños</th>
                            <th class="!text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contacts as $contact)
                            <tr>
                                <td>
                                    <a href="{{ route('contacts.show', $contact) }}" wire:navigate class="flex items-center gap-3">
                                        <x-contact-avatar :name="$contact->name" :color="$contact->avatar_color" size="sm" />
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-ink-800 dark:text-cream-100">{{ $contact->name }}</span>
                                            <span class="block truncate text-xs text-ink-400 dark:text-ink-300">
                                                {{ $contact->email ?: $contact->phone ?: 'Sin datos de contacto' }}
                                            </span>
                                        </span>
                                    </a>
                                </td>
                                <td>
                                    <div class="flex max-w-[220px] flex-wrap gap-1">
                                        @forelse ($contact->segments as $segment)
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                  style="background-color: {{ $segment->color }}1f; color: {{ $segment->color }}">
                                                <span class="size-1.5 rounded-full" style="background-color: {{ $segment->color }}"></span>
                                                {{ $segment->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-ink-300">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="text-xs text-ink-500 dark:text-ink-300">
                                    {{ $contact->location ?: '—' }}
                                </td>
                                <td>
                                    @if ($contact->contact_type)
                                        <span class="ct-chip !py-0.5">{{ $contact->contact_type->label() }}</span>
                                    @else
                                        <span class="text-xs text-ink-300">—</span>
                                    @endif
                                </td>
                                <td class="text-xs text-ink-500 dark:text-ink-300">
                                    @if ($contact->birthdate)
                                        {{ $contact->birthdate->translatedFormat('j M') }}
                                        <span class="text-ink-300">· {{ $contact->age }} años</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('contacts.show', $contact) }}" wire:navigate
                                           class="rounded-xl p-2 text-ink-400 transition hover:bg-cream-200 hover:text-ink-700 dark:hover:bg-white/10 dark:hover:text-cream-100" title="Ver">
                                            <flux:icon.eye class="size-4" />
                                        </a>
                                        @if (auth()->user()->canWrite())
                                            <a href="{{ route('contacts.edit', $contact) }}" wire:navigate
                                               class="rounded-xl p-2 text-ink-400 transition hover:bg-cream-200 hover:text-ink-700 dark:hover:bg-white/10 dark:hover:text-cream-100" title="Editar">
                                                <flux:icon.pencil-square class="size-4" />
                                            </a>
                                        @endif
                                        @if (auth()->user()->canDelete())
                                            <button type="button" wire:click="delete({{ $contact->id }})"
                                                    wire:confirm="¿Eliminar a {{ $contact->name }}? Esta acción no se puede deshacer."
                                                    class="rounded-xl p-2 text-ink-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10" title="Eliminar">
                                                <flux:icon.trash class="size-4" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 px-4 py-3.5 text-xs text-ink-400 sm:flex-row sm:items-center sm:justify-between dark:text-ink-300">
                <p>
                    Mostrando <span class="font-semibold text-ink-600 dark:text-cream-200">{{ $contacts->firstItem() }}–{{ $contacts->lastItem() }}</span>
                    de <span class="font-semibold text-ink-600 dark:text-cream-200">{{ number_format($contacts->total()) }}</span> contactos
                </p>

                @if ($contacts->hasPages())
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage" @disabled($contacts->onFirstPage())
                                class="rounded-xl border border-cream-300 bg-white px-3 py-1.5 font-medium text-ink-600 transition enabled:hover:border-clay-300 disabled:opacity-40 dark:border-white/10 dark:bg-white/5 dark:text-cream-200">
                            Anterior
                        </button>
                        <span>Página {{ $contacts->currentPage() }} de {{ $contacts->lastPage() }}</span>
                        <button type="button" wire:click="nextPage" @disabled(! $contacts->hasMorePages())
                                class="rounded-xl border border-cream-300 bg-white px-3 py-1.5 font-medium text-ink-600 transition enabled:hover:border-clay-300 disabled:opacity-40 dark:border-white/10 dark:bg-white/5 dark:text-cream-200">
                            Siguiente
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </section>
</div>
