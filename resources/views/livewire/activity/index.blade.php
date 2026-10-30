<div class="mx-auto w-full max-w-5xl space-y-6">
    <x-page-header eyebrow="Trazabilidad" title="Actividad" description="Un registro sobrio de quién creó, editó, importó o anotó qué.">
        <x-slot:actions>
            <select wire:model.live="event" class="ct-input !w-auto !py-2.5">
                <option value="">Todos los eventos</option>
                @foreach ($events as $eventName)
                    <option value="{{ $eventName }}">{{ ucfirst(str_replace('_', ' ', $eventName)) }}</option>
                @endforeach
            </select>
        </x-slot:actions>
    </x-page-header>

    @if ($logs->isEmpty())
        <x-empty-state icon="clock" title="Sin actividad" description="Cuando el equipo empiece a moverse, lo verás aquí." />
    @else
        @foreach ($logs->groupBy(fn ($log) => $log->created_at->translatedFormat('l j \d\e F \d\e Y')) as $day => $entries)
            <section class="ct-card overflow-hidden">
                <div class="border-b border-cream-200/80 bg-cream-100/50 px-5 py-3 dark:border-white/5 dark:bg-white/[0.02]">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-400 dark:text-ink-300">{{ $day }}</h2>
                </div>

                <ul class="divide-y divide-cream-200/70 dark:divide-white/5">
                    @foreach ($entries as $log)
                        <li class="flex gap-3 px-5 py-4">
                            <span class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl bg-cream-200 text-ink-500 dark:bg-white/10 dark:text-cream-200">
                                <flux:icon :name="$log->icon" class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-ink-700 dark:text-cream-200">{{ $log->description }}</p>
                                <p class="mt-0.5 text-[11px] text-ink-400 dark:text-ink-300">
                                    {{ $log->user?->name ?? 'Sistema' }} · {{ $log->created_at->format('H:i') }}
                                    @if ($log->subject instanceof \App\Models\Contact)
                                        · <a href="{{ route('contacts.show', $log->subject_id) }}" wire:navigate class="font-medium text-clay-600 hover:text-clay-700 dark:text-clay-300">Ver contacto</a>
                                    @elseif ($log->subject instanceof \App\Models\Import)
                                        · <a href="{{ route('imports.show', $log->subject_id) }}" wire:navigate class="font-medium text-clay-600 hover:text-clay-700 dark:text-clay-300">Ver importación</a>
                                    @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach

        @if ($logs->hasPages())
            <div class="flex items-center justify-between text-xs text-ink-400">
                <button type="button" wire:click="previousPage" @disabled($logs->onFirstPage())
                        class="rounded-xl border border-cream-300 bg-white px-3 py-1.5 font-medium text-ink-600 transition enabled:hover:border-clay-300 disabled:opacity-40 dark:border-white/10 dark:bg-white/5 dark:text-cream-200">Anterior</button>
                <span>Página {{ $logs->currentPage() }} de {{ $logs->lastPage() }}</span>
                <button type="button" wire:click="nextPage" @disabled(! $logs->hasMorePages())
                        class="rounded-xl border border-cream-300 bg-white px-3 py-1.5 font-medium text-ink-600 transition enabled:hover:border-clay-300 disabled:opacity-40 dark:border-white/10 dark:bg-white/5 dark:text-cream-200">Siguiente</button>
            </div>
        @endif
    @endif
</div>
