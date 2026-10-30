<div class="mx-auto w-full max-w-6xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('contacts.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-ink-500 transition hover:text-clay-600 dark:text-ink-300">
            <flux:icon.arrow-left class="size-4" /> Volver a contactos
        </a>

        <div class="flex flex-wrap items-center gap-2">
            @if (auth()->user()->canWrite())
                <a href="{{ route('contacts.edit', $contact) }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-2xl border border-cream-300 bg-white px-4 py-2.5 text-sm font-semibold text-ink-700 transition hover:border-clay-300 hover:text-clay-700 dark:border-white/10 dark:bg-white/5 dark:text-cream-100">
                    <flux:icon.pencil-square class="size-4" /> Editar
                </a>
            @endif
            @if (auth()->user()->canDelete())
                <button type="button" wire:click="delete"
                        wire:confirm="¿Eliminar a {{ $contact->name }}? Esta acción no se puede deshacer."
                        class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10">
                    <flux:icon.trash class="size-4" /> Eliminar
                </button>
            @endif
        </div>
    </div>

    <section class="ct-card overflow-hidden">
        <div class="relative h-28 bg-ink-900 px-5 pt-5 sm:h-32 sm:px-7 sm:pt-6">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(198,93,59,0.55),transparent_45%),radial-gradient(circle_at_85%_90%,rgba(122,139,111,0.4),transparent_45%)]"></div>
            <div class="ct-noise absolute inset-0 opacity-30"></div>
            <div class="relative z-10">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="ct-display text-2xl font-semibold text-cream-50 sm:text-3xl">{{ $contact->name }}</h1>
                    @if ($contact->contact_type)
                        <span class="inline-flex items-center rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-semibold text-cream-50 ring-1 ring-white/20">{{ $contact->contact_type->label() }}</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-cream-200/80">
                    {{ collect([$contact->job_title, $contact->company])->filter()->implode(' · ') ?: 'Sin empresa registrada' }}
                </p>
            </div>
        </div>

        <div class="relative z-10 px-5 pb-6 sm:px-7">
            <div class="-mt-10 flex items-end justify-between gap-4">
                <x-contact-avatar :name="$contact->name" :color="$contact->avatar_color" size="lg" class="!size-20 !text-xl ring-4 ring-cream-50 dark:ring-ink-950" />

                <div class="mb-1 flex flex-wrap justify-end gap-2">
                    @if ($contact->email)
                        <a href="mailto:{{ $contact->email }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-cream-100 px-3 py-2 text-xs font-semibold text-ink-600 transition hover:bg-cream-200 dark:bg-white/5 dark:text-cream-200 dark:hover:bg-white/10">
                            <flux:icon.envelope class="size-3.5" /> Escribir
                        </a>
                    @endif
                    @if ($contact->phone)
                        <a href="tel:{{ $contact->phone }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-cream-100 px-3 py-2 text-xs font-semibold text-ink-600 transition hover:bg-cream-200 dark:bg-white/5 dark:text-cream-200 dark:hover:bg-white/10">
                            <flux:icon.phone class="size-3.5" /> Llamar
                        </a>
                    @endif
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                @forelse ($contact->segments as $segment)
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                          style="background-color: {{ $segment->color }}1f; color: {{ $segment->color }}">
                        <span class="size-1.5 rounded-full" style="background-color: {{ $segment->color }}"></span>
                        {{ $segment->name }}
                    </span>
                @empty
                    <span class="ct-chip">Sin segmentos</span>
                @endforelse
            </div>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[1fr_360px]">
        <div class="space-y-5">
            <section class="ct-card p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Notas</h2>
                        <p class="mt-0.5 text-xs text-ink-400 dark:text-ink-300">Lo que no cabe en un campo del formulario.</p>
                    </div>
                    <span class="ct-chip">{{ $notes->count() }}</span>
                </div>

                @if (auth()->user()->canWrite())
                    <form wire:submit="addNote" class="mt-4">
                        <textarea wire:model="note" rows="3" maxlength="2000"
                                  placeholder="Ej. Prefiere que la contacten por WhatsApp después de las 3 p. m."
                                  class="ct-input resize-none"></textarea>
                        @error('note') <p class="mt-1 text-xs text-clay-600 dark:text-clay-300">{{ $message }}</p> @enderror
                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-[11px] text-ink-400">Solo el equipo interno puede leer esto.</p>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-2 rounded-xl bg-clay-500 px-3.5 py-2 text-xs font-semibold text-cream-50 transition hover:bg-clay-600 disabled:opacity-60">
                                <flux:icon.plus class="size-3.5" />
                                <span wire:loading.remove wire:target="addNote">Añadir nota</span>
                                <span wire:loading wire:target="addNote">Guardando…</span>
                            </button>
                        </div>
                    </form>
                @endif

                <div class="mt-5 space-y-4">
                    @forelse ($notes as $noteItem)
                        <article class="flex gap-3">
                            <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-cream-200 text-[10px] font-bold text-ink-600 dark:bg-white/10 dark:text-cream-200">
                                {{ $noteItem->user?->initials() ?? '·' }}
                            </span>
                            <div class="min-w-0 flex-1 rounded-2xl border border-cream-200 bg-cream-50/60 px-4 py-3 dark:border-white/5 dark:bg-white/[0.02]">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-[11px] text-ink-400 dark:text-ink-300">
                                        <span class="font-semibold text-ink-600 dark:text-cream-200">{{ $noteItem->user?->name ?? 'Sistema' }}</span>
                                        · {{ $noteItem->created_at->diffForHumans() }}
                                    </p>
                                    @if (auth()->user()->can('delete', $noteItem))
                                        <button type="button" wire:click="deleteNote({{ $noteItem->id }})"
                                                wire:confirm="¿Eliminar esta nota?"
                                                class="rounded-lg p-1 text-ink-300 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10"
                                                title="Eliminar nota">
                                            <flux:icon.trash class="size-3.5" />
                                        </button>
                                    @endif
                                </div>
                                <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-ink-700 dark:text-cream-200">{{ $noteItem->body }}</p>
                            </div>
                        </article>
                    @empty
                        <x-empty-state icon="chat-bubble-left-ellipsis" title="Todavía no hay notas" description="Apunta aquí preferencias, acuerdos o el contexto que ayude a la próxima conversación." />
                    @endforelse
                </div>
            </section>

            <section class="ct-card p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Historial del contacto</h2>
                    <flux:icon.clock class="size-4 text-ink-300" />
                </div>

                <ul class="mt-4 space-y-3">
                    @forelse ($activity as $log)
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-lg bg-cream-200 text-ink-500 dark:bg-white/10 dark:text-cream-200">
                                <flux:icon :name="$log->icon" class="size-3.5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-ink-700 dark:text-cream-200">{{ $log->description }}</p>
                                <p class="mt-0.5 text-[11px] text-ink-400 dark:text-ink-300">
                                    {{ $log->user?->name ?? 'Sistema' }} · {{ $log->created_at->translatedFormat('j M Y, H:i') }}
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-sm text-ink-400">Sin movimientos registrados todavía.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <aside class="space-y-5">
            <section class="ct-card p-5">
                <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Ficha</h2>

                <dl class="mt-4 space-y-3.5 text-sm">
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Correo</dt>
                        <dd class="mt-0.5 break-all text-ink-700 dark:text-cream-200">
                            @if ($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="hover:text-clay-600">{{ $contact->email }}</a>
                            @else — @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Teléfono</dt>
                        <dd class="mt-0.5 text-ink-700 dark:text-cream-200">{{ $contact->phone ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Empresa</dt>
                        <dd class="mt-0.5 text-ink-700 dark:text-cream-200">{{ $contact->company ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Cargo</dt>
                        <dd class="mt-0.5 text-ink-700 dark:text-cream-200">{{ $contact->job_title ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Tipo de negocio</dt>
                        <dd class="mt-0.5 text-ink-700 dark:text-cream-200">{{ $contact->business_type ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Ubicación</dt>
                        <dd class="mt-0.5 text-ink-700 dark:text-cream-200">{{ $contact->location ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Cumpleaños</dt>
                        <dd class="mt-0.5 text-ink-700 dark:text-cream-200">
                            @if ($contact->birthdate)
                                {{ $contact->birthdate->translatedFormat('j \d\e F \d\e Y') }}
                                <span class="text-ink-400">· {{ $contact->age }} años</span>
                            @else — @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Origen</dt>
                        <dd class="mt-0.5 text-ink-700 dark:text-cream-200">{{ $contact->source ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Marca asociada</dt>
                        <dd class="mt-0.5 text-ink-700 dark:text-cream-200">{{ $contact->associated_brand ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">Empresa asociada</dt>
                        <dd class="mt-0.5 text-ink-700 dark:text-cream-200">{{ $contact->associated_company ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="ct-card p-5 text-xs text-ink-400 dark:text-ink-300">
                <p>Creado {{ $contact->created_at->diffForHumans() }}@if ($contact->creator) por {{ $contact->creator->name }}@endif.</p>
                @if ($contact->updated_at->gt($contact->created_at))
                    <p class="mt-1">Última edición {{ $contact->updated_at->diffForHumans() }}@if ($contact->updater) por {{ $contact->updater->name }}@endif.</p>
                @endif
            </section>
        </aside>
    </div>
</div>
