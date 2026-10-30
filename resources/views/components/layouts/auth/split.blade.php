<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-cream-50 text-ink-800 antialiased dark:bg-ink-950 dark:text-cream-100">
        <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
            <aside class="relative hidden overflow-hidden bg-ink-900 p-10 text-cream-50 lg:flex lg:flex-col">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_15%,rgba(198,93,59,0.5),transparent_45%),radial-gradient(circle_at_85%_80%,rgba(122,139,111,0.35),transparent_50%)]"></div>
                <div class="ct-noise absolute inset-0 opacity-40"></div>

                <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3" wire:navigate>
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-clay-500 text-cream-50 shadow-lg">
                        <x-app-logo-icon class="size-7" />
                    </span>
                    <span class="grid leading-none">
                        <span class="ct-display text-2xl font-semibold">Gestor</span>
                        <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-cream-300/70">De contactos</span>
                    </span>
                </a>

                <div class="relative z-10 mt-auto max-w-lg">
                    <p class="ct-eyebrow !text-clay-300">Cada contacto, en su lugar</p>
                    <h1 class="ct-display mt-4 text-4xl font-semibold leading-[1.1] xl:text-5xl">
                        Organiza, entiende y cuida tus relaciones.
                    </h1>
                    <p class="mt-5 max-w-md text-sm leading-relaxed text-cream-200/80">
                        Contactos, segmentos e importación de CSV en una herramienta tranquila, pensada para personas que trabajan con personas.
                    </p>

                    <div class="mt-10 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4 backdrop-blur">
                            <div class="flex items-center gap-2 text-xs text-cream-200/70">
                                <span class="size-2 rounded-full bg-clay-400"></span> Clientes
                            </div>
                            <p class="mt-3 text-sm font-semibold">María López</p>
                            <p class="text-xs text-cream-200/60">Hotel Sol · Panamá</p>
                            <div class="mt-3 flex items-center gap-2 text-[11px] text-cream-200/50">
                                <flux:icon.cake class="size-3.5" /> Cumple en 4 días
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4 backdrop-blur">
                            <div class="flex items-center gap-2 text-xs text-cream-200/70">
                                <span class="size-2 rounded-full bg-sage-300"></span> Prensa
                            </div>
                            <p class="mt-3 text-sm font-semibold">Andrés Castillo</p>
                            <p class="text-xs text-cream-200/60">Medios del Pacífico · Panamá</p>
                            <div class="mt-3 flex items-center gap-2 text-[11px] text-cream-200/50">
                                <flux:icon.document-arrow-up class="size-3.5" /> 148 filas importadas
                            </div>
                        </div>
                    </div>
                </div>

                <p class="relative z-10 mt-10 text-[11px] text-cream-200/40">
                    Proyecto de portafolio · Datos ficticios · Hecho con Laravel, Livewire y PostgreSQL
                </p>
            </aside>

            <main class="flex flex-col justify-center px-6 py-10 sm:px-10 lg:px-16">
                <div class="mx-auto w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-10 flex items-center gap-3 lg:hidden" wire:navigate>
                        <span class="flex size-10 items-center justify-center rounded-2xl bg-clay-500 text-cream-50">
                            <x-app-logo-icon class="size-6" />
                        </span>
                        <span class="grid leading-none">
                            <span class="ct-display text-xl font-semibold text-ink-900 dark:text-cream-50">Gestor</span>
                            <span class="mt-1 text-[9px] font-semibold uppercase tracking-[0.2em] text-ink-400">De contactos</span>
                        </span>
                    </a>

                    {{ $slot }}
                </div>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
