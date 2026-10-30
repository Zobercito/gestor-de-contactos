<div class="mx-auto w-full max-w-6xl space-y-6">
    <x-page-header eyebrow="Organización" title="Segmentos" description="Agrupa contactos por afinidad, sector o campaña. Un contacto puede vivir en varios.">
        <x-slot:actions>
            @if (auth()->user()->canWrite())
                <button type="button" wire:click="create"
                        class="inline-flex items-center gap-2 rounded-2xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 shadow-[0_12px_24px_-14px_rgba(198,93,59,1)] transition hover:bg-clay-600">
                    <flux:icon.plus class="size-4" /> Nuevo segmento
                </button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if ($showForm)
        <section class="ct-card border-clay-200 p-5 sm:p-6 dark:border-clay-400/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="ct-eyebrow">{{ $editingId ? 'Editando' : 'Nuevo segmento' }}</p>
                    <h2 class="ct-display mt-1 text-xl font-semibold text-ink-900 dark:text-cream-50">
                        {{ $editingId ? $name : 'Dale un nombre y un color' }}
                    </h2>
                </div>
                <button type="button" wire:click="cancel" class="rounded-xl p-2 text-ink-400 transition hover:bg-cream-200 hover:text-ink-700 dark:hover:bg-white/10" title="Cerrar">
                    <flux:icon.x-mark class="size-4" />
                </button>
            </div>

            <form wire:submit="save" class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="segment-name" class="ct-label">Nombre</label>
                    <input id="segment-name" type="text" wire:model="name" placeholder="Clientes, Prensa, Newsletter…" class="ct-input mt-1.5" />
                    @error('name') <p class="mt-1 text-xs text-clay-600 dark:text-clay-300">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="segment-description" class="ct-label">Descripción</label>
                    <input id="segment-description" type="text" wire:model="description" placeholder="Para qué sirve este segmento" class="ct-input mt-1.5" />
                    @error('description') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <span class="ct-label">Color</span>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @foreach ($palette as $tone)
                            <button type="button" wire:click="$set('color', '{{ $tone }}')"
                                    class="size-8 rounded-full transition hover:scale-110 {{ $color === $tone ? 'ring-2 ring-ink-700 ring-offset-2 ring-offset-cream-50 dark:ring-cream-200 dark:ring-offset-ink-950' : '' }}"
                                    style="background-color: {{ $tone }}" aria-label="Usar color {{ $tone }}"></button>
                        @endforeach
                        <label class="flex items-center gap-2 text-xs text-ink-400">
                            <input type="color" wire:model="color" class="size-8 cursor-pointer rounded-lg border-0 bg-transparent p-0">
                            Personalizado
                        </label>
                        <span class="ms-auto inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" style="background-color: {{ $color }}1f; color: {{ $color }}">
                            <span class="size-2 rounded-full" style="background-color: {{ $color }}"></span>
                            {{ $name ?: 'Vista previa' }}
                        </span>
                    </div>
                    @error('color') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-2 sm:col-span-2">
                    <button type="button" wire:click="cancel" class="rounded-xl px-3 py-2 text-sm font-medium text-ink-500 hover:text-ink-700 dark:text-ink-300">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-xl bg-clay-500 px-4 py-2 text-sm font-semibold text-cream-50 transition hover:bg-clay-600 disabled:opacity-60">
                        <flux:icon.check class="size-4" />
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Guardar cambios' : 'Crear segmento' }}</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </form>
        </section>
    @endif

    @if ($segments->isEmpty())
        <x-empty-state icon="tag" title="Todavía no hay segmentos" description="Crea el primero para empezar a organizar tu directorio." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($segments as $segment)
                <article class="ct-card group relative overflow-hidden p-5">
                    <div class="absolute inset-x-0 top-0 h-1.5" style="background-color: {{ $segment->color }}"></div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex size-11 items-center justify-center rounded-2xl" style="background-color: {{ $segment->color }}20; color: {{ $segment->color }}">
                                <flux:icon.tag class="size-5" />
                            </span>
                            <div>
                                <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">{{ $segment->name }}</h2>
                                <p class="text-xs text-ink-400 dark:text-ink-300">{{ $segment->contacts_count }} {{ $segment->contacts_count === 1 ? 'contacto' : 'contactos' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            @if (auth()->user()->canWrite())
                                <button type="button" wire:click="edit({{ $segment->id }})"
                                        class="rounded-lg p-1.5 text-ink-300 transition hover:bg-cream-200 hover:text-ink-700 dark:hover:bg-white/10" title="Editar">
                                    <flux:icon.pencil-square class="size-4" />
                                </button>
                            @endif
                            @if (auth()->user()->canWrite())
                                <button type="button" wire:click="delete({{ $segment->id }})"
                                        wire:confirm="¿Eliminar el segmento «{{ $segment->name }}»? Los contactos se conservan."
                                        class="rounded-lg p-1.5 text-ink-300 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10" title="Eliminar">
                                    <flux:icon.trash class="size-4" />
                                </button>
                            @endif
                        </div>
                    </div>

                    <p class="mt-3 line-clamp-2 min-h-[2.5rem] text-xs leading-relaxed text-ink-400 dark:text-ink-300">
                        {{ $segment->description ?: 'Sin descripción.' }}
                    </p>

                    <div class="mt-4 flex items-center justify-between">
                        <a href="{{ route('contacts.export', ['segment' => $segment->id]) }}"
                           class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-clay-600 hover:text-clay-700 dark:text-clay-300">
                            <flux:icon.arrow-down-tray class="size-3.5" /> Exportar
                        </a>
                        <a href="{{ route('contacts.index', ['segment' => $segment->id]) }}" wire:navigate
                           class="text-[11px] font-semibold text-ink-400 hover:text-ink-600 dark:text-ink-300">
                            Ver contactos
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
