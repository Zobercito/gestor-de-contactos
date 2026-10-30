<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Contraseña" subheading="Usa una clave larga y distinta a la de otros servicios">
        <form wire:submit="updatePassword" class="ct-card mt-6 space-y-6 p-6">
            <flux:input
                wire:model="current_password"
                id="update_password_current_password"
                label="Contraseña actual"
                type="password"
                name="current_password"
                required
                autocomplete="current-password"
            />
            <flux:input
                wire:model="password"
                id="update_password_password"
                label="Nueva contraseña"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />
            <flux:input
                wire:model="password_confirmation"
                id="update_password_password_confirmation"
                label="Repite la nueva contraseña"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center justify-end gap-4 border-t border-ink-100 pt-5 dark:border-ink-800">
                <x-action-message on="password-updated">Contraseña actualizada.</x-action-message>
                <flux:button variant="primary" type="submit">Actualizar contraseña</flux:button>
            </div>
        </form>

        @if (config('demo.enabled'))
            <p class="mt-4 text-xs text-ink-400">
                En la demo pública los cambios se pierden cuando la base de datos se reinicia.
            </p>
        @endif
    </x-settings.layout>
</section>
