<div class="mx-auto w-full max-w-7xl space-y-6" @if ($hasActive) wire:poll.8s @endif>
    <x-page-header eyebrow="Datos" title="Importaciones" description="Cada archivo deja un informe: qué entró, qué se omitió y por qué.">
        <x-slot:actions>
            <a href="{{ route('imports.sample') }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-cream-300 bg-white px-4 py-2.5 text-sm font-semibold text-ink-700 transition hover:border-clay-300 dark:border-white/10 dark:bg-white/5 dark:text-cream-100">
                <flux:icon.arrow-down-tray class="size-4" /> CSV de ejemplo
            </a>
            @if (auth()->user()->canWrite())
                <a href="{{ route('imports.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-2xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 shadow-[0_12px_24px_-14px_rgba(198,93,59,1)] transition hover:bg-clay-600">
                    <flux:icon.arrow-up-tray class="size-4" /> Importar CSV
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card icon="check-badge" label="Completadas" :value="number_format($stats['completed'])" detail="Importaciones cerradas" tone="sage" />
        <x-stat-card icon="user-plus" label="Contactos creados" :value="number_format($stats['created'])" detail="Suma de todas las importaciones" tone="clay" />
        <x-stat-card icon="shield-check" label="Duplicados omitidos" :value="number_format($stats['duplicates'])" detail="Sin tocar tu base existente" tone="gold" />
        <x-stat-card icon="exclamation-triangle" label="Errores" :value="number_format($stats['errors'])" detail="Filas que necesitaron atención" tone="ink" />
    </div>

    <section class="ct-card overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-cream-200/80 p-4 sm:flex-row sm:items-center dark:border-white/5">
            <div class="relative flex-1">
                <flux:icon.magnifying-glass class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-300" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre de archivo…" class="ct-input !pl-10" />
            </div>
            <select wire:model.live="status" class="ct-input !w-auto !py-2.5">
                <option value="">Todos los estados</option>
                <option value="pending">En cola</option>
                <option value="processing">Procesando</option>
                <option value="completed">Completadas</option>
                <option value="failed">Fallidas</option>
            </select>
        </div>

        @if ($imports->isEmpty())
            <div class="p-6">
                <x-empty-state icon="arrow-up-tray" title="Todavía no hay importaciones" description="Sube tu primer CSV y te mostraremos exactamente qué pasó con cada fila.">
                    @if (auth()->user()->canWrite())
                        <x-slot:actions>
                            <a href="{{ route('imports.create') }}" wire:navigate class="ct-link text-sm">Importar un CSV</a>
                        </x-slot:actions>
                    @endif
                </x-empty-state>
            </div>
        @else
            <div class="overflow-x-auto ct-scrollbar">
                <table class="ct-table min-w-[800px]">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Filas</th>
                            <th>Resultado</th>
                            <th>Estado</th>
                            <th class="!text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($imports as $import)
                            <tr>
                                <td>
                                    <a href="{{ route('imports.show', $import) }}" wire:navigate class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-cream-200 text-ink-500 dark:bg-white/10 dark:text-cream-200">
                                            <flux:icon.document-text class="size-4" />
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block max-w-[280px] truncate text-sm font-semibold text-ink-800 dark:text-cream-100">{{ $import->original_name }}</span>
                                            <span class="block text-[11px] text-ink-400 dark:text-ink-300">
                                                {{ $import->user?->name ?? 'Sistema' }} · {{ $import->created_at->diffForHumans() }}
                                            </span>
                                        </span>
                                    </a>
                                </td>
                                <td class="text-sm tabular-nums text-ink-600 dark:text-cream-200">{{ number_format($import->total_rows) }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1 text-[10px] font-semibold">
                                        <span class="rounded-full bg-sage-100 px-2 py-0.5 text-sage-700 dark:bg-sage-500/15 dark:text-sage-200">{{ $import->created_count }} creados</span>
                                        <span class="rounded-full bg-gold-100 px-2 py-0.5 text-gold-700 dark:bg-gold-500/15 dark:text-gold-200">{{ $import->duplicate_count }} dup.</span>
                                        @if ($import->error_count > 0)
                                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200">{{ $import->error_count }} err.</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <x-import-status :status="$import->status" />
                                    @if (! $import->isFinished() && $import->total_rows > 0)
                                        <p class="mt-1 text-[10px] text-ink-400">{{ $import->progress() }}%</p>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('imports.show', $import) }}" wire:navigate
                                           class="rounded-xl p-2 text-ink-400 transition hover:bg-cream-200 hover:text-ink-700 dark:hover:bg-white/10 dark:hover:text-cream-100" title="Ver informe">
                                            <flux:icon.eye class="size-4" />
                                        </a>
                                        @if (auth()->user()->canDelete())
                                            <button type="button" wire:click="delete({{ $import->id }})"
                                                    wire:confirm="¿Eliminar esta importación del historial? Los contactos creados se conservan."
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
                <p>Mostrando {{ $imports->firstItem() }}–{{ $imports->lastItem() }} de {{ $imports->total() }}</p>
                @if ($imports->hasPages())
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousPage" @disabled($imports->onFirstPage())
                                class="rounded-xl border border-cream-300 bg-white px-3 py-1.5 font-medium text-ink-600 transition enabled:hover:border-clay-300 disabled:opacity-40 dark:border-white/10 dark:bg-white/5 dark:text-cream-200">Anterior</button>
                        <span>Página {{ $imports->currentPage() }} de {{ $imports->lastPage() }}</span>
                        <button type="button" wire:click="nextPage" @disabled(! $imports->hasMorePages())
                                class="rounded-xl border border-cream-300 bg-white px-3 py-1.5 font-medium text-ink-600 transition enabled:hover:border-clay-300 disabled:opacity-40 dark:border-white/10 dark:bg-white/5 dark:text-cream-200">Siguiente</button>
                    </div>
                @endif
            </div>
        @endif
    </section>
</div>
