<div class="mx-auto w-full max-w-7xl space-y-7">
    <x-page-header
        eyebrow="{{ $today->translatedFormat('l j \d\e F') }}"
        title="Hola, {{ Str::of(auth()->user()->name)->explode(' ')->first() }}"
        description="Un repaso tranquilo a tus contactos, tus segmentos y lo que viene.">
        <x-slot:actions>
            <a href="{{ route('imports.create') }}" wire:navigate
               class="inline-flex items-center gap-2 rounded-2xl border border-cream-300 bg-white px-4 py-2.5 text-sm font-semibold text-ink-700 transition hover:border-clay-300 hover:text-clay-700 dark:border-white/10 dark:bg-white/5 dark:text-cream-100 dark:hover:border-clay-400/40">
                <flux:icon.arrow-up-tray class="size-4" /> Importar CSV
            </a>
            @if (auth()->user()->canWrite())
                <a href="{{ route('contacts.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-2xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 shadow-[0_12px_24px_-14px_rgba(198,93,59,1)] transition hover:bg-clay-600">
                    <flux:icon.plus class="size-4" /> Nuevo contacto
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card icon="users" label="Contactos" :value="number_format($total)" detail="{{ number_format($newThisMonth) }} nuevos este mes" tone="clay" />
        <x-stat-card icon="tag" label="Segmentos" :value="number_format($segments->count())" detail="Con colores y equipos" tone="sage" />
        <x-stat-card icon="cake" label="Cumpleaños" :value="number_format($birthdaysCount)" detail="En los próximos 30 días" tone="gold" />
        <x-stat-card icon="building-office-2" label="Empresas" :value="number_format($b2b)" detail="{{ number_format($b2c) }} contactos personales" tone="ink" />
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <section class="ct-card xl:col-span-2">
            <div class="flex items-center justify-between border-b border-cream-200/80 px-5 py-4 dark:border-white/5">
                <div>
                    <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Próximos cumpleaños</h2>
                    <p class="mt-0.5 text-xs text-ink-400 dark:text-ink-300">Un buen momento para escribir sin hablar de trabajo.</p>
                </div>
                <flux:icon.cake class="size-5 text-gold-500" />
            </div>

            @if ($birthdays->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="cake" title="Sin cumpleaños a la vista" description="Agrega fechas de nacimiento a tus contactos para no olvidarlos." />
                </div>
            @else
                <ul class="divide-y divide-cream-200/70 dark:divide-white/5">
                    @foreach ($birthdays as $entry)
                        <li>
                            <a href="{{ route('contacts.show', $entry['contact']) }}" wire:navigate class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-cream-100/60 dark:hover:bg-white/[0.03]">
                                <x-contact-avatar :name="$entry['contact']->name" :color="$entry['contact']->avatar_color" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink-800 dark:text-cream-100">{{ $entry['contact']->name }}</p>
                                    <p class="truncate text-xs text-ink-400 dark:text-ink-300">
                                        {{ $entry['contact']->company ?: $entry['contact']->city ?: 'Sin empresa' }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold text-clay-600 dark:text-clay-300">
                                        {{ $entry['days'] === 0 ? 'Hoy' : ($entry['days'] === 1 ? 'Mañana' : 'En '.$entry['days'].' días') }}
                                    </p>
                                    <p class="text-[11px] text-ink-400 dark:text-ink-300">{{ $entry['date']->translatedFormat('j \d\e F') }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="ct-card">
            <div class="flex items-center justify-between border-b border-cream-200/80 px-5 py-4 dark:border-white/5">
                <div>
                    <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Por segmento</h2>
                    <p class="mt-0.5 text-xs text-ink-400 dark:text-ink-300">Cómo se reparte tu red.</p>
                </div>
                <a href="{{ route('segments.index') }}" wire:navigate class="text-xs font-semibold text-clay-600 dark:text-clay-300">Ver todos</a>
            </div>

            <div class="space-y-4 p-5">
                @forelse ($segments->take(6) as $segment)
                    <div>
                        <div class="flex items-center justify-between gap-2 text-sm">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="size-2.5 shrink-0 rounded-full" style="background-color: {{ $segment->color }}"></span>
                                <span class="truncate font-medium text-ink-700 dark:text-cream-200">{{ $segment->name }}</span>
                            </span>
                            <span class="shrink-0 text-xs tabular-nums text-ink-400 dark:text-ink-300">{{ $segment->contacts_count }}</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-cream-200 dark:bg-white/10">
                            <div class="h-full rounded-full" style="width: {{ max(4, round(($segment->contacts_count / $segmentMax) * 100)) }}%; background-color: {{ $segment->color }}"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-ink-400">Todavía no hay segmentos.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <section class="ct-card">
            <div class="flex items-center justify-between border-b border-cream-200/80 px-5 py-4 dark:border-white/5">
                <div>
                    <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Actividad reciente</h2>
                    <p class="mt-0.5 text-xs text-ink-400 dark:text-ink-300">Quién hizo qué, sin misterio.</p>
                </div>
                <a href="{{ route('activity.index') }}" wire:navigate class="text-xs font-semibold text-clay-600 dark:text-clay-300">Historial</a>
            </div>

            <ul class="divide-y divide-cream-200/70 dark:divide-white/5">
                @forelse ($activity as $log)
                    <li class="flex gap-3 px-5 py-3.5">
                        <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-xl bg-cream-200 text-ink-500 dark:bg-white/10 dark:text-cream-200">
                            <flux:icon :name="$log->icon" class="size-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm text-ink-700 dark:text-cream-200">{{ $log->description }}</p>
                            <p class="mt-0.5 text-[11px] text-ink-400 dark:text-ink-300">
                                {{ $log->user?->name ?? 'Sistema' }} · {{ $log->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-ink-400">Aún no hay actividad registrada.</li>
                @endforelse
            </ul>
        </section>

        <section class="ct-card">
            <div class="flex items-center justify-between border-b border-cream-200/80 px-5 py-4 dark:border-white/5">
                <div>
                    <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Importaciones</h2>
                    <p class="mt-0.5 text-xs text-ink-400 dark:text-ink-300">El pulso de tus archivos CSV.</p>
                </div>
                <a href="{{ route('imports.index') }}" wire:navigate class="text-xs font-semibold text-clay-600 dark:text-clay-300">Ver historial</a>
            </div>

            <ul class="divide-y divide-cream-200/70 dark:divide-white/5">
                @forelse ($imports as $import)
                    <li>
                        <a href="{{ route('imports.show', $import) }}" wire:navigate class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-cream-100/60 dark:hover:bg-white/[0.03]">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-cream-200 text-ink-500 dark:bg-white/10 dark:text-cream-200">
                                <flux:icon.document-text class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-ink-700 dark:text-cream-200">{{ $import->original_name }}</p>
                                <p class="text-[11px] text-ink-400 dark:text-ink-300">{{ $import->created_at->diffForHumans() }} · {{ number_format($import->total_rows) }} filas</p>
                            </div>
                            <x-import-status :status="$import->status" class="shrink-0" />
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-ink-400">Todavía no importaste ningún archivo.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
