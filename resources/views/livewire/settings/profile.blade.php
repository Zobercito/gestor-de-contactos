<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Perfil" subheading="Tu nombre y correo de acceso">
        <form wire:submit="updateProfileInformation" class="ct-card my-6 w-full space-y-6 p-6">
            <flux:input wire:model="name" label="Nombre" type="text" name="name" required autofocus autocomplete="name" />
            <flux:input wire:model="email" label="Correo electrónico" type="email" name="email" required autocomplete="email" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm text-ink-500 dark:text-ink-300">
                        Tu correo aún no está verificado.
                        <button
                            wire:click.prevent="resendVerificationNotification"
                            class="ct-link text-sm"
                        >
                            Reenviar el correo de verificación
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-sage-600">
                            Te enviamos un nuevo enlace de verificación.
                        </p>
                    @endif
                </div>
            @endif

            <div class="flex items-center justify-end gap-4 border-t border-ink-100 pt-5 dark:border-ink-800">
                <x-action-message on="profile-updated">Guardado.</x-action-message>
                <flux:button variant="primary" type="submit">Guardar cambios</flux:button>
            </div>
        </form>

        @unless (config('demo.enabled'))
            <livewire:settings.delete-user-form />
        @endunless
    </x-settings.layout>
</section>
