@php
    $editing = $contact !== null;
@endphp

<div class="mx-auto w-full max-w-6xl space-y-6">
    <x-page-header
        eyebrow="{{ $editing ? 'Editar contacto' : 'Nuevo contacto' }}"
        title="{{ $editing ? $contact->name : 'Añadir una persona a tu red' }}"
        description="{{ $editing ? 'Actualiza sus datos y segmentos sin perder el hilo.' : 'Con unos pocos datos basta. Puedes completar el resto después.' }}">
        <x-slot:actions>
            <a href="{{ $editing ? route('contacts.show', $contact) : route('contacts.index') }}" wire:navigate
               class="inline-flex items-center gap-2 rounded-2xl border border-cream-300 bg-white px-4 py-2.5 text-sm font-semibold text-ink-700 transition hover:border-clay-300 dark:border-white/10 dark:bg-white/5 dark:text-cream-100">
                <flux:icon.arrow-left class="size-4" /> Cancelar
            </a>
        </x-slot:actions>
    </x-page-header>

    <form wire:submit="save" class="grid gap-5 lg:grid-cols-[1fr_340px]">
        <div class="space-y-5">
            <section class="ct-card p-5 sm:p-6">
                <div class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-clay-100 text-clay-600 dark:bg-clay-500/15 dark:text-clay-300">
                        <flux:icon.user class="size-4" />
                    </span>
                    <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Lo esencial</h2>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="ct-label">Nombre completo <span class="text-clay-500">*</span></label>
                        <input id="name" type="text" wire:model="name" placeholder="María López" class="ct-input mt-1.5" autofocus />
                        @error('name') <p class="mt-1 text-xs text-clay-600 dark:text-clay-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="ct-label">Correo electrónico</label>
                        <input id="email" type="email" wire:model="email" placeholder="maria@empresa.com" class="ct-input mt-1.5" />
                        @error('email') <p class="mt-1 text-xs text-clay-600 dark:text-clay-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="ct-label">Teléfono o WhatsApp</label>
                        <input id="phone" type="text" wire:model="phone" placeholder="+507 6000-0000" class="ct-input mt-1.5" />
                        @error('phone') <p class="mt-1 text-xs text-clay-600 dark:text-clay-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="contact_type" class="ct-label">Tipo de cliente</label>
                        <select id="contact_type" wire:model="contact_type" class="ct-input mt-1.5">
                            <option value="">Sin definir</option>
                            <option value="B2B">B2B · Empresa</option>
                            <option value="B2C">B2C · Persona</option>
                        </select>
                        @error('contact_type') <p class="mt-1 text-xs text-clay-600 dark:text-clay-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="source" class="ct-label">¿Cómo llegó?</label>
                        <input id="source" type="text" wire:model="source" list="sources" placeholder="Feria, referido, web…" class="ct-input mt-1.5" />
                        <datalist id="sources">
                            <option value="Feria"></option>
                            <option value="Referido"></option>
                            <option value="Web"></option>
                            <option value="Instagram"></option>
                            <option value="Evento"></option>
                            <option value="Importación CSV"></option>
                        </datalist>
                        @error('source') <p class="mt-1 text-xs text-clay-600 dark:text-clay-300">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="mt-4 rounded-xl bg-cream-100 px-3.5 py-2.5 text-[11px] leading-relaxed text-ink-400 dark:bg-white/5 dark:text-ink-300">
                    Un contacto necesita al menos un correo o un teléfono para poder existir en tu directorio.
                </p>
            </section>

            <section class="ct-card p-5 sm:p-6">
                <div class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-sage-100 text-sage-700 dark:bg-sage-500/15 dark:text-sage-300">
                        <flux:icon.building-office-2 class="size-4" />
                    </span>
                    <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Trabajo y contexto</h2>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="company" class="ct-label">Empresa</label>
                        <input id="company" type="text" wire:model="company" placeholder="Hotel Sol" class="ct-input mt-1.5" />
                        @error('company') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="job_title" class="ct-label">Cargo</label>
                        <input id="job_title" type="text" wire:model="job_title" placeholder="Gerente general" class="ct-input mt-1.5" />
                        @error('job_title') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="business_type" class="ct-label">Tipo de negocio</label>
                        <input id="business_type" type="text" wire:model="business_type" list="business-types" placeholder="Hotel, proveedor, ONG…" class="ct-input mt-1.5" />
                        <datalist id="business-types">
                            <option value="Hotel"></option>
                            <option value="Restaurante"></option>
                            <option value="Proveedor"></option>
                            <option value="Retail"></option>
                            <option value="ONG"></option>
                            <option value="Prensa"></option>
                        </datalist>
                        @error('business_type') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="gender" class="ct-label">Género</label>
                        <select id="gender" wire:model="gender" class="ct-input mt-1.5">
                            <option value="">Sin definir</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Otro">Otro</option>
                            <option value="Prefiere no decir">Prefiere no decir</option>
                        </select>
                        @error('gender') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="associated_brand" class="ct-label">Marca asociada</label>
                        <input id="associated_brand" type="text" wire:model="associated_brand" placeholder="Marca Sol" class="ct-input mt-1.5" />
                        @error('associated_brand') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="associated_company" class="ct-label">Empresa asociada</label>
                        <input id="associated_company" type="text" wire:model="associated_company" placeholder="Hotel Playa" class="ct-input mt-1.5" />
                        @error('associated_company') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="ct-card p-5 sm:p-6">
                <div class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-gold-100 text-gold-700 dark:bg-gold-500/15 dark:text-gold-200">
                        <flux:icon.map-pin class="size-4" />
                    </span>
                    <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Dónde y cuándo</h2>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="city" class="ct-label">Ciudad</label>
                        <input id="city" type="text" wire:model="city" placeholder="Panamá" class="ct-input mt-1.5" />
                        @error('city') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="province" class="ct-label">Provincia</label>
                        <input id="province" type="text" wire:model="province" placeholder="Panamá" class="ct-input mt-1.5" />
                        @error('province') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="country" class="ct-label">País</label>
                        <input id="country" type="text" wire:model="country" placeholder="Panamá" class="ct-input mt-1.5" />
                        @error('country') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="birthdate" class="ct-label">Fecha de nacimiento</label>
                        <input id="birthdate" type="date" wire:model="birthdate" class="ct-input mt-1.5" />
                        @error('birthdate') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-5">
            <section class="ct-card p-5">
                <div class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-cream-200 text-ink-500 dark:bg-white/10 dark:text-cream-200">
                        <flux:icon.tag class="size-4" />
                    </span>
                    <h2 class="ct-display text-lg font-semibold text-ink-900 dark:text-cream-50">Segmentos</h2>
                </div>

                <p class="mt-1 text-xs leading-relaxed text-ink-400 dark:text-ink-300">Un contacto puede estar en varios. Sirven para filtrar y exportar.</p>

                <div class="mt-4 space-y-1.5">
                    @forelse ($segments as $segment)
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-xl px-2.5 py-2 transition hover:bg-cream-100 dark:hover:bg-white/5">
                            <input type="checkbox" wire:model="segmentIds" value="{{ $segment->id }}"
                                   class="size-4 rounded border-cream-400 text-clay-500 focus:ring-clay-400 dark:border-white/20 dark:bg-white/5">
                            <span class="size-2.5 rounded-full" style="background-color: {{ $segment->color }}"></span>
                            <span class="text-sm text-ink-700 dark:text-cream-200">{{ $segment->name }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-ink-400">Aún no hay segmentos creados.</p>
                    @endforelse
                </div>
                @error('segmentIds.*') <p class="mt-2 text-xs text-clay-600">{{ $message }}</p> @enderror
            </section>

            <div class="ct-card sticky top-6 p-5">
                <button type="submit" wire:loading.attr="disabled"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl bg-clay-500 px-4 py-3 text-sm font-semibold text-cream-50 shadow-[0_14px_28px_-16px_rgba(198,93,59,1)] transition hover:bg-clay-600 disabled:opacity-60">
                    <flux:icon.check class="size-4" />
                    <span wire:loading.remove wire:target="save">{{ $editing ? 'Guardar cambios' : 'Crear contacto' }}</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
                <p class="mt-2.5 text-center text-[11px] text-ink-400">Los campos con <span class="text-clay-500">*</span> son obligatorios.</p>

                @if ($editing && auth()->user()->canDelete())
                    <div class="mt-5 border-t border-cream-200 pt-4 dark:border-white/10">
                        <button type="button" wire:click="delete"
                                wire:confirm="¿Eliminar a {{ $contact->name }}? Esta acción no se puede deshacer."
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10">
                            <flux:icon.trash class="size-3.5" /> Eliminar contacto
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>
