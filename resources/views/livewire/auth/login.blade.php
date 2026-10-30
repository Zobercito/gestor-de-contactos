<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = true;

    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div>
    <p class="ct-eyebrow">Bienvenido de nuevo</p>
    <h1 class="ct-display mt-2 text-3xl font-semibold text-ink-900 dark:text-cream-50">Entra a tu espacio</h1>
    <p class="mt-2 text-sm leading-relaxed text-ink-500 dark:text-ink-300">
        Gestiona tus contactos con calma. Todo sigue donde lo dejaste.
    </p>

    <form wire:submit="login" class="mt-8 space-y-5">
        <div>
            <label for="email" class="ct-label">Correo electrónico</label>
            <input id="email" type="email" wire:model="email" required autofocus autocomplete="email"
                   placeholder="tu@correo.com"
                   class="ct-input mt-1.5" />
            @error('email') <p class="mt-1.5 text-sm text-clay-600 dark:text-clay-300">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="ct-label">Contraseña</label>
            </div>
            <input id="password" type="password" wire:model="password" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="ct-input mt-1.5" />
            @error('password') <p class="mt-1.5 text-sm text-clay-600 dark:text-clay-300">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2.5 text-sm text-ink-500 dark:text-ink-300">
            <input type="checkbox" wire:model="remember"
                   class="size-4 rounded border-cream-400 text-clay-500 focus:ring-clay-400 dark:border-white/20 dark:bg-white/5">
            Mantener la sesión abierta
        </label>

        <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-clay-500 px-5 py-3 text-sm font-semibold text-cream-50 shadow-[0_16px_32px_-18px_rgba(198,93,59,1)] transition hover:bg-clay-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-clay-400 focus-visible:ring-offset-2 focus-visible:ring-offset-cream-50"
                wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Entrar</span>
            <span wire:loading wire:target="login">Entrando…</span>
            <flux:icon.arrow-right class="size-4" wire:loading.remove wire:target="login" />
        </button>
    </form>

    @if (config('demo.enabled'))
        <div class="mt-8">
            <div class="flex items-center gap-3">
                <span class="h-px flex-1 bg-cream-300 dark:bg-white/10"></span>
                <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-ink-400">O prueba la demo</span>
                <span class="h-px flex-1 bg-cream-300 dark:bg-white/10"></span>
            </div>

            <div class="mt-4 grid gap-2.5">
                @foreach ([
                    ['role' => 'admin', 'label' => 'Administradora', 'name' => 'Ana Torres', 'detail' => 'Todo: contactos, importaciones y usuarios'],
                    ['role' => 'editor', 'label' => 'Editora', 'name' => 'Elena Ruiz', 'detail' => 'Crea, edita e importa; no elimina'],
                    ['role' => 'viewer', 'label' => 'Lectora', 'name' => 'Lucas Vega', 'detail' => 'Consulta y exporta, sin cambios'],
                ] as $demo)
                    <form method="POST" action="{{ route('demo.login', $demo['role']) }}">
                        @csrf
                        <button type="submit"
                                class="group flex w-full items-center gap-3 rounded-2xl border border-cream-300 bg-white/70 px-4 py-3 text-left transition hover:border-clay-300 hover:bg-white dark:border-white/10 dark:bg-white/[0.03] dark:hover:border-clay-400/40 dark:hover:bg-white/[0.06]">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-cream-200 text-xs font-bold text-clay-700 dark:bg-white/10 dark:text-cream-100">
                                {{ collect(explode(' ', $demo['name']))->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}
                            </span>
                            <span class="grid min-w-0 flex-1 leading-tight">
                                <span class="text-sm font-semibold text-ink-800 dark:text-cream-100">{{ $demo['label'] }} · {{ $demo['name'] }}</span>
                                <span class="mt-0.5 truncate text-[11px] text-ink-400 dark:text-ink-300">{{ $demo['detail'] }}</span>
                            </span>
                            <flux:icon.arrow-right class="size-4 text-ink-300 transition group-hover:translate-x-0.5 group-hover:text-clay-500" />
                        </button>
                    </form>
                @endforeach
            </div>

            <p class="mt-3 text-center text-[11px] text-ink-400 dark:text-ink-300">
                Acceso de demostración con datos ficticios · Los cambios se reinician cada noche
            </p>
        </div>
    @endif

    <p class="mt-8 text-center text-sm text-ink-400 dark:text-ink-300">
        ¿Solo quieres mirar? <a href="{{ route('home') }}" class="ct-link" wire:navigate>Vuelve al inicio</a>
    </p>
</div>
