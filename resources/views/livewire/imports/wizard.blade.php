<div class="mx-auto w-full max-w-6xl space-y-6">
    <x-page-header eyebrow="Importar" title="Trae tus contactos desde un CSV" description="Detectamos las columnas, te mostramos una vista previa y omitimos duplicados por ti.">
        <x-slot:actions>
            <a href="{{ route('imports.index') }}" wire:navigate
               class="inline-flex items-center gap-2 rounded-2xl border border-cream-300 bg-white px-4 py-2.5 text-sm font-semibold text-ink-700 transition hover:border-clay-300 dark:border-white/10 dark:bg-white/5 dark:text-cream-100">
                <flux:icon.clock class="size-4" /> Historial
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Pasos --}}
    <ol class="flex items-center gap-2 text-xs font-semibold sm:gap-3">
        @foreach ([1 => 'Subir archivo', 2 => 'Emparejar columnas', 3 => 'Revisar e importar'] as $number => $label)
            <li class="flex items-center gap-2 {{ $step === $number ? 'text-clay-600 dark:text-clay-300' : ($step > $number ? 'text-sage-600 dark:text-sage-300' : 'text-ink-300 dark:text-ink-400') }}">
                <span class="flex size-7 items-center justify-center rounded-full border text-[11px] {{ $step > $number ? 'border-sage-400 bg-sage-100 text-sage-700 dark:border-sage-400/40 dark:bg-sage-500/15 dark:text-sage-200' : ($step === $number ? 'border-clay-400 bg-clay-100 text-clay-700 dark:border-clay-400/50 dark:bg-clay-500/15 dark:text-clay-200' : 'border-cream-300 dark:border-white/10') }}">
                    @if ($step > $number)
                        <flux:icon.check class="size-3.5" />
                    @else
                        {{ $number }}
                    @endif
                </span>
                <span class="hidden sm:inline">{{ $label }}</span>
            </li>
            @if ($number < 3)
                <li class="h-px flex-1 bg-cream-300 dark:bg-white/10"></li>
            @endif
        @endforeach
    </ol>

    @if ($errorMessage !== '')
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($step === 1)
        <section class="ct-card p-5 sm:p-7">
            <div class="grid gap-6 lg:grid-cols-[1fr_300px]">
                <div>
                    <div class="relative flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-cream-400 bg-cream-50/70 px-6 py-12 text-center transition hover:border-clay-400 dark:border-white/15 dark:bg-white/[0.02]">
                        <span class="flex size-14 items-center justify-center rounded-2xl bg-clay-100 text-clay-600 dark:bg-clay-500/15 dark:text-clay-300">
                            <flux:icon.arrow-up-tray class="size-6" />
                        </span>
                        <p class="mt-4 text-sm font-semibold text-ink-700 dark:text-cream-100">Selecciona tu archivo CSV</p>
                        <p class="mt-1 max-w-sm text-xs leading-relaxed text-ink-400 dark:text-ink-300">
                            Aceptamos coma, punto y coma, tabulador o barra vertical. Codificación UTF-8 y Windows-1252.
                        </p>

                        <label class="mt-5 cursor-pointer rounded-2xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 transition hover:bg-clay-600">
                            Elegir archivo
                            <input type="file" wire:model="file" accept=".csv,.txt,text/csv,text/plain" class="sr-only">
                        </label>

                        @if ($file)
                            <div class="mt-4 flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs text-ink-600 shadow-sm dark:bg-white/10 dark:text-cream-200">
                                <flux:icon.document-text class="size-4 text-clay-500" />
                                <span class="max-w-[240px] truncate">{{ $file->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeFile" class="text-ink-400 hover:text-rose-500" aria-label="Quitar archivo">
                                    <flux:icon.x-mark class="size-3.5" />
                                </button>
                            </div>
                        @endif

                        <div wire:loading wire:target="file" class="mt-3 text-xs text-clay-600">Cargando archivo…</div>
                        @error('file') <p class="mt-3 text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
                    </div>

                    <button type="button" wire:click="upload" wire:loading.attr="disabled"
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-clay-500 px-4 py-3 text-sm font-semibold text-cream-50 shadow-[0_14px_28px_-16px_rgba(198,93,59,1)] transition hover:bg-clay-600 disabled:opacity-60">
                        <flux:icon.sparkles class="size-4" />
                        <span wire:loading.remove wire:target="upload">Analizar archivo</span>
                        <span wire:loading wire:target="upload">Leyendo columnas…</span>
                    </button>
                </div>

                <aside class="space-y-3">
                    <div class="rounded-2xl bg-cream-100 p-4 dark:bg-white/5">
                        <p class="text-xs font-semibold text-ink-700 dark:text-cream-200">Límites de esta sesión</p>
                        <ul class="mt-2 space-y-1.5 text-[11px] leading-relaxed text-ink-400 dark:text-ink-300">
                            <li>· Hasta {{ number_format($maxRows) }} filas por archivo</li>
                            <li>· Archivos de {{ number_format((int) config('contacts.import.max_file_kb') / 1024, 1) }} MB como máximo</li>
                            <li>· Los duplicados por correo o teléfono se omiten</li>
                        </ul>
                    </div>

                    <a href="{{ route('imports.sample') }}"
                       class="flex items-center gap-3 rounded-2xl border border-cream-300 bg-white p-4 transition hover:border-clay-300 dark:border-white/10 dark:bg-white/[0.03]">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-sage-100 text-sage-700 dark:bg-sage-500/15 dark:text-sage-300">
                            <flux:icon.arrow-down-tray class="size-4" />
                        </span>
                        <span>
                            <span class="block text-xs font-semibold text-ink-700 dark:text-cream-200">CSV de ejemplo</span>
                            <span class="block text-[11px] text-ink-400 dark:text-ink-300">Con duplicados y errores a propósito</span>
                        </span>
                    </a>

                    <div class="rounded-2xl border border-gold-200 bg-gold-50/70 p-4 text-[11px] leading-relaxed text-gold-700 dark:border-gold-500/20 dark:bg-gold-500/10 dark:text-gold-200">
                        <span class="font-semibold">Consejo</span><br>
                        No necesitas que las columnas se llamen igual: en el siguiente paso las emparejas tú.
                    </div>
                </aside>
            </div>
        </section>
    @endif

    @if ($step === 2)
        <section class="ct-card p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="ct-display text-xl font-semibold text-ink-900 dark:text-cream-50">Empareja las columnas</h2>
                    <p class="mt-1 text-xs text-ink-400 dark:text-ink-300">Dijimos que estas columnas encajan. Corrige lo que haga falta.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-[11px]">
                    <span class="ct-chip">{{ number_format($totalRows) }} filas</span>
                    <span class="ct-chip">Separador: {{ $delimiterLabel }}</span>
                    <span class="ct-chip">{{ $encoding }}</span>
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($fields as $field => $definition)
                    <label class="block rounded-2xl border border-cream-200 bg-cream-50/50 p-3 dark:border-white/5 dark:bg-white/[0.02]">
                        <span class="flex items-center justify-between text-xs font-semibold text-ink-700 dark:text-cream-200">
                            {{ $definition['label'] }}
                            @if ($field === 'name')
                                <span class="text-[10px] font-bold uppercase tracking-wider text-clay-500">Obligatorio</span>
                            @elseif (in_array($field, ['email', 'phone'], true))
                                <span class="text-[10px] font-medium text-ink-400">Correo o teléfono</span>
                            @endif
                        </span>
                        <select wire:model="mapping.{{ $field }}" class="ct-input mt-2 !py-2 text-xs">
                            <option value="">No importar</option>
                            @foreach ($headers as $header)
                                <option value="{{ $header }}">{{ $header }}</option>
                            @endforeach
                        </select>
                        @error('mapping.'.$field) <span class="mt-1 block text-[11px] text-rose-600">{{ $message }}</span> @enderror
                    </label>
                @endforeach
            </div>

            @error('mapping.email') <p class="mt-3 text-xs text-rose-600">{{ $message }}</p> @enderror
            @error('mapping.name') <p class="mt-3 text-xs text-rose-600">{{ $message }}</p> @enderror

            <div class="mt-6">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Vista previa del archivo</p>
                <div class="mt-2 overflow-x-auto rounded-2xl border border-cream-200 ct-scrollbar dark:border-white/10">
                    <table class="min-w-[900px] w-full text-left text-xs">
                        <thead class="bg-cream-100 dark:bg-white/5">
                            <tr>
                                @foreach ($headers as $header)
                                    <th class="whitespace-nowrap px-3 py-2 font-semibold text-ink-500 dark:text-cream-200">{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_slice($preview, 0, 5) as $row)
                                <tr class="border-t border-cream-200/70 dark:border-white/5">
                                    @foreach ($headers as $header)
                                        <td class="max-w-[200px] truncate px-3 py-2 text-ink-500 dark:text-ink-300">{{ $row[$header] ?: '—' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                <button type="button" wire:click="cancel" class="text-xs font-semibold text-ink-400 hover:text-rose-500">Cancelar importación</button>
                <div class="flex gap-2">
                    <button type="button" wire:click="back" class="rounded-xl border border-cream-300 bg-white px-4 py-2.5 text-sm font-semibold text-ink-600 transition hover:border-clay-300 dark:border-white/10 dark:bg-white/5 dark:text-cream-200">
                        Atrás
                    </button>
                    <button type="button" wire:click="analyze" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 transition hover:bg-clay-600 disabled:opacity-60">
                        <span wire:loading.remove wire:target="analyze">Revisar filas</span>
                        <span wire:loading wire:target="analyze">Analizando…</span>
                        <flux:icon.arrow-right class="size-4" wire:loading.remove wire:target="analyze" />
                    </button>
                </div>
            </div>
        </section>
    @endif

    @if ($step === 3 && $analysis)
        <section class="ct-card p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="ct-display text-xl font-semibold text-ink-900 dark:text-cream-50">Revisa antes de importar</h2>
                    <p class="mt-1 text-xs text-ink-400 dark:text-ink-300">Nada se guarda hasta que confirmes. Los duplicados se omiten automáticamente.</p>
                </div>
                <span class="ct-chip">{{ number_format($analysis['counts']['rows']) }} filas analizadas</span>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-sage-50 p-4 dark:bg-sage-500/10">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sage-700 dark:text-sage-300">Listas</p>
                    <p class="ct-display mt-1 text-2xl font-semibold text-sage-800 dark:text-sage-100">{{ number_format($analysis['counts']['ready']) }}</p>
                </div>
                <div class="rounded-2xl bg-gold-50 p-4 dark:bg-gold-500/10">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gold-700 dark:text-gold-200">Duplicadas</p>
                    <p class="ct-display mt-1 text-2xl font-semibold text-gold-800 dark:text-gold-100">{{ number_format($analysis['counts']['duplicates']) }}</p>
                </div>
                <div class="rounded-2xl bg-rose-50 p-4 dark:bg-rose-500/10">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-rose-700 dark:text-rose-300">Con errores</p>
                    <p class="ct-display mt-1 text-2xl font-semibold text-rose-800 dark:text-rose-100">{{ number_format($analysis['counts']['errors']) }}</p>
                </div>
                <div class="rounded-2xl bg-cream-100 p-4 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-500 dark:text-ink-300">Avisos</p>
                    <p class="ct-display mt-1 text-2xl font-semibold text-ink-700 dark:text-cream-200">{{ number_format($analysis['counts']['warnings']) }}</p>
                </div>
            </div>

            @error('analysis') <p class="mt-3 text-xs text-rose-600">{{ $message }}</p> @enderror

            <div class="mt-6 overflow-x-auto rounded-2xl border border-cream-200 ct-scrollbar dark:border-white/10">
                <table class="w-full min-w-[760px] text-left text-xs">
                    <thead class="bg-cream-100 dark:bg-white/5">
                        <tr>
                            <th class="px-3 py-2 font-semibold text-ink-500 dark:text-cream-200">Fila</th>
                            <th class="px-3 py-2 font-semibold text-ink-500 dark:text-cream-200">Contacto</th>
                            <th class="px-3 py-2 font-semibold text-ink-500 dark:text-cream-200">Correo / Teléfono</th>
                            <th class="px-3 py-2 font-semibold text-ink-500 dark:text-cream-200">Estado</th>
                            <th class="px-3 py-2 font-semibold text-ink-500 dark:text-cream-200">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($analysis['rows'] as $row)
                            @php
                                $status = $row['status'];
                                $badge = match ($status) {
                                    'ready' => 'bg-sage-100 text-sage-700',
                                    'duplicate' => 'bg-gold-100 text-gold-700',
                                    default => 'bg-rose-100 text-rose-700',
                                };
                                $text = match ($status) {
                                    'ready' => 'Lista',
                                    'duplicate' => 'Duplicada',
                                    default => 'Error',
                                };
                            @endphp
                            <tr class="border-t border-cream-200/70 dark:border-white/5">
                                <td class="px-3 py-2 text-ink-400">{{ $row['row'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="font-semibold text-ink-700 dark:text-cream-200">{{ $row['data']['name'] ?? '—' }}</span>
                                    <span class="block text-[11px] text-ink-400">{{ $row['data']['company'] ?? '' }}</span>
                                </td>
                                <td class="px-3 py-2 text-ink-500 dark:text-ink-300">
                                    {{ $row['data']['email'] ?? '—' }}<br>
                                    <span class="text-[11px] text-ink-400">{{ $row['data']['phone'] ?? '' }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $badge }}">{{ $text }}</span>
                                </td>
                                <td class="px-3 py-2 text-ink-500 dark:text-ink-300">
                                    {{ implode(' ', array_merge($row['messages'], $row['warnings'])) ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                <button type="button" wire:click="back" class="rounded-xl border border-cream-300 bg-white px-4 py-2.5 text-sm font-semibold text-ink-600 transition hover:border-clay-300 dark:border-white/10 dark:bg-white/5 dark:text-cream-200">
                    Ajustar columnas
                </button>
                <button type="button" wire:click="confirm" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-2xl bg-clay-500 px-5 py-3 text-sm font-semibold text-cream-50 shadow-[0_14px_28px_-16px_rgba(198,93,59,1)] transition hover:bg-clay-600 disabled:opacity-60">
                    <flux:icon.check class="size-4" />
                    <span wire:loading.remove wire:target="confirm">
                        Importar {{ number_format($analysis['counts']['ready']) }} contactos
                    </span>
                    <span wire:loading wire:target="confirm">Importando…</span>
                </button>
            </div>

            @if ($totalRows > $queueThreshold)
                <p class="mt-3 text-right text-[11px] text-ink-400">
                    Es un archivo grande: se procesará en segundo plano y verás el avance en el informe.
                </p>
            @endif
        </section>
    @endif
</div>
