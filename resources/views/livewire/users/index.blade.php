<div class="mx-auto w-full max-w-5xl space-y-6">
    <x-page-header eyebrow="Administración" title="Usuarios" description="Tres roles claros. La autorización vive en el servidor, no en botones escondidos.">
        <x-slot:actions>
            <button type="button" wire:click="create"
                    class="inline-flex items-center gap-2 rounded-2xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 shadow-[0_12px_24px_-14px_rgba(198,93,59,1)] transition hover:bg-clay-600">
                <flux:icon.user-plus class="size-4" /> Nuevo usuario
            </button>
        </x-slot:actions>
    </x-page-header>

    @if ($showForm)
        <section class="ct-card border-clay-200 p-5 sm:p-6 dark:border-clay-400/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="ct-eyebrow">Nuevo usuario</p>
                    <h2 class="ct-display mt-1 text-xl font-semibold text-ink-900 dark:text-cream-50">Invita a alguien al equipo</h2>
                </div>
                <button type="button" wire:click="cancel" class="rounded-xl p-2 text-ink-400 hover:bg-cream-200 dark:hover:bg-white/10" title="Cerrar">
                    <flux:icon.x-mark class="size-4" />
                </button>
            </div>

            <form wire:submit="save" class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="user-name" class="ct-label">Nombre</label>
                    <input id="user-name" type="text" wire:model="name" class="ct-input mt-1.5" placeholder="Nombre y apellido" />
                    @error('name') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="user-email" class="ct-label">Correo</label>
                    <input id="user-email" type="email" wire:model="email" class="ct-input mt-1.5" placeholder="persona@gestordecontactos.test" />
                    @error('email') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="user-password" class="ct-label">Contraseña temporal</label>
                    <input id="user-password" type="password" wire:model="password" class="ct-input mt-1.5" placeholder="Mínimo 8 caracteres" />
                    @error('password') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="user-role" class="ct-label">Rol</label>
                    <select id="user-role" wire:model="role" class="ct-input mt-1.5">
                        @foreach ($roles as $roleOption)
                            <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="mt-1 text-xs text-clay-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 flex justify-end gap-2">
                    <button type="button" wire:click="cancel" class="rounded-xl px-3 py-2 text-sm font-medium text-ink-500">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-xl bg-clay-500 px-4 py-2 text-sm font-semibold text-cream-50 transition hover:bg-clay-600 disabled:opacity-60">
                        <flux:icon.check class="size-4" /> Crear usuario
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="ct-card overflow-hidden">
        <div class="overflow-x-auto ct-scrollbar">
            <table class="ct-table min-w-[640px]">
                <thead>
                    <tr>
                        <th>Persona</th>
                        <th>Rol</th>
                        <th>Desde</th>
                        <th class="!text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sage-500 text-xs font-bold text-white">{{ $user->initials() }}</span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-ink-800 dark:text-cream-100">
                                            {{ $user->name }}
                                            @if ($user->id === auth()->id()) <span class="text-[10px] font-medium text-ink-400">(tú)</span> @endif
                                        </span>
                                        <span class="block truncate text-xs text-ink-400 dark:text-ink-300">{{ $user->email }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <select wire:change="updateRole({{ $user->id }}, $event.target.value)"
                                        @disabled($user->id === auth()->id())
                                        class="ct-input !w-auto !py-1.5 text-xs disabled:cursor-not-allowed disabled:opacity-60">
                                    @foreach ($roles as $roleOption)
                                        <option value="{{ $roleOption->value }}" @selected($user->role === $roleOption)>{{ $roleOption->label() }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-xs text-ink-400 dark:text-ink-300">{{ $user->created_at->translatedFormat('j M Y') }}</td>
                            <td>
                                <div class="flex justify-end">
                                    @if ($user->id !== auth()->id())
                                        <button type="button" wire:click="delete({{ $user->id }})"
                                                wire:confirm="¿Eliminar a {{ $user->name }}? Su historial de actividad se conserva sin autor."
                                                class="rounded-xl p-2 text-ink-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10" title="Eliminar usuario">
                                            <flux:icon.trash class="size-4" />
                                        </button>
                                    @else
                                        <span class="text-[11px] text-ink-300">Sesión actual</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ($roles as $roleOption)
            <div class="ct-card p-4">
                <p class="text-sm font-semibold text-ink-700 dark:text-cream-200">{{ $roleOption->label() }}</p>
                <p class="mt-1 text-xs leading-relaxed text-ink-400 dark:text-ink-300">{{ $roleOption->description() }}</p>
            </div>
        @endforeach
    </div>
</div>
