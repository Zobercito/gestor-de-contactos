<div class="mx-auto w-full max-w-6xl space-y-6"
     @if (! $import->isFinished()) wire:poll.3s="refresh" @endif>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('imports.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-ink-500 transition hover:text-clay-600 dark:text-ink-300">
            <flux:icon.arrow-left class="size-4" /> Volver a importaciones
        </a>

        <div class="flex flex-wrap items-center gap-2">
            @if ($import->status === \App\Enums\ImportStatus::Failed && (auth()->id() === $import->user_id || auth()->user()->isAdmin()))
                <button type="button" wire:click="retry" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-2xl border border-cream-300 bg-white px-4 py-2.5 text-sm font-semibold text-ink-700 transition hover:border-clay-300 dark:border-white/10 dark:bg-white/5 dark:text-cream-100 disabled:opacity-60">
                    <flux:icon.arrow-path class="size-4" /> Reintentar
                </button>
            @endif
            @if (auth()->user()->canDelete())
                <button type="button" wire:click="delete"
                        wire:confirm="¿Eliminar esta importación del historial? Los contactos creados se conservan."
                        class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10">
                    <flux:icon.trash class="size-4" /> Eliminar
                </button>
            @endif
        </div>
    </div>

    <section class="ct-card overflow-hidden">
        <div class="border-b border-cream-200/80 p-5 sm:p-6 dark:border-white/5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-cream-200 text-ink-500 dark:bg-white/10 dark:text-cream-200">
                        <flux:icon.document-text class="size-6" />
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="ct-display text-2xl font-semibold text-ink-900 dark:text-cream-50">{{ $import->original_name }}</h1>
                            <x-import-status :status="$import->status" />
                        </div>
                        <p class="mt-1 text-xs text-ink-400 dark:text-ink-300">
                            {{ $import->user?->name ?? 'Sistema' }} ·
                            {{ $import->created_at->translatedFormat('j \d\e F \d\e Y, H:i') }} ·
                            {{ number_format($import->total_rows) }} filas
                        </p>
                    </div>
                </div>

                @if (! $import->isFinished())
                    <div class="flex items-center gap-2 text-xs text-ink-400 dark:text-ink-300">
                        <span class="size-2 animate-pulse rounded-full bg-sky-500"></span>
                        Actualizando cada 3 segundos
                    </div>
                @endif
            </div>

            @if (! $import->isFinished())
                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs text-ink-500 dark:text-ink-300">
                        <span>{{ $import->status === \App\Enums\ImportStatus::Pending ? 'Esperando al procesador…' : 'Procesando filas…' }}</span>
                        <span class="font-semibold tabular-nums">{{ $import->progress() }}%</span>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-cream-200 dark:bg-white/10">
                        <div class="h-full rounded-full bg-clay-500 transition-all duration-500" style="width: {{ max(2, $import->progress()) }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6 xl:grid-cols-4">
            <div class="rounded-2xl bg-sage-50 p-4 dark:bg-sage-500/10">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sage-700 dark:text-sage-300">Contactos creados</p>
                <p class="ct-display mt-1 text-3xl font-semibold text-sage-800 dark:text-sage-100">{{ number_format($import->created_count) }}</p>
            </div>
            <div class="rounded-2xl bg-gold-50 p-4 dark:bg-gold-500/10">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gold-700 dark:text-gold-200">Duplicados omitidos</p>
                <p class="ct-display mt-1 text-3xl font-semibold text-gold-800 dark:text-gold-100">{{ number_format($import->duplicate_count) }}</p>
            </div>
            <div class="rounded-2xl bg-rose-50 p-4 dark:bg-rose-500/10">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-rose-700 dark:text-rose-300">Errores</p>
                <p class="ct-display mt-1 text-3xl font-semibold text-rose-800 dark:text-rose-100">{{ number_format($import->error_count) }}</p>
            </div>
            <div class="rounded-2xl bg-cream-100 p-4 dark:bg-white/5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-500 dark:text-ink-300">Avisos</p>
                <p class="ct-display mt-1 text-3xl font-semibold text-ink-700 dark:text-cream-200">{{ number_format($warningsCount) }}</p>
            </div>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[1fr_320px]">
        <section class="ct-card overflow-hidden">
            <div class="border-b border-cream-200/80 px-5 py-4 dark:border-white/5">
                <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Filas con observaciones</h2>
                <p class="mt-0.5 text-xs text-ink-400 dark:text-ink-300">Guardamos hasta 200 avisos por importación para que el informe siga siendo legible.</p>
            </div>

            @if ($issues->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="check-badge" title="Nada que reportar" description="Todas las filas se procesaron sin errores ni duplicados." />
                </div>
            @else
                <div class="max-h-[540px] overflow-y-auto ct-scrollbar">
                    <table class="ct-table min-w-[620px]">
                        <thead class="sticky top-0 bg-cream-50 dark:bg-ink-950">
                            <tr>
                                <th>Fila</th>
                                <th>Tipo</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($issues as $issue)
                                @php
                                    $type = $issue['type'] ?? 'error';
                                    $badge = match ($type) {
                                        'duplicate' => 'bg-gold-100 text-gold-700 dark:bg-gold-500/15 dark:text-gold-200',
                                        'warning' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200',
                                        default => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200',
                                    };
                                    $label = match ($type) {
                                        'duplicate' => 'Duplicada',
                                        'warning' => 'Aviso',
                                        default => 'Error',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-xs tabular-nums text-ink-400">{{ $issue['row'] ?? '—' }}</td>
                                    <td>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $badge }}">{{ $label }}</span>
                                    </td>
                                    <td class="text-xs text-ink-600 dark:text-cream-200">{{ $issue['message'] ?? 'Sin detalle.' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <aside class="space-y-5">
            <section class="ct-card p-5">
                <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Archivo</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-ink-400">Separador</dt>
                        <dd class="font-medium text-ink-700 dark:text-cream-200">
                            {{ match ($import->delimiter) { ';' => 'Punto y coma', "\t" => 'Tabulador', '|' => 'Barra', default => 'Coma' } }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-ink-400">Codificación</dt>
                        <dd class="font-medium text-ink-700 dark:text-cream-200">{{ $import->encoding ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-ink-400">Inicio</dt>
                        <dd class="text-xs font-medium text-ink-700 dark:text-cream-200">{{ $import->started_at?->translatedFormat('j M, H:i') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-ink-400">Fin</dt>
                        <dd class="text-xs font-medium text-ink-700 dark:text-cream-200">{{ $import->finished_at?->translatedFormat('j M, H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="ct-card p-5">
                <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Columnas usadas</h2>
                @if ($mapping->isEmpty())
                    <p class="mt-2 text-xs text-ink-400">Sin emparejamiento registrado.</p>
                @else
                    <dl class="mt-4 space-y-2.5 text-xs">
                        @foreach ($mapping as $label => $header)
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-ink-400">{{ $label }}</dt>
                                <dd class="max-w-[160px] truncate text-right font-medium text-ink-700 dark:text-cream-200">{{ $header ?: '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </section>

            <div class="rounded-2xl border border-cream-300 bg-cream-100/60 p-4 text-[11px] leading-relaxed text-ink-400 dark:border-white/10 dark:bg-white/5 dark:text-ink-300">
                Los contactos creados desde aquí quedaron asociados al usuario que inició la importación. Si reintentas un archivo ya procesado, los contactos existentes se detectarán como duplicados.
            </div>
        </aside>
    </div>
</div>
