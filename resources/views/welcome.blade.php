<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-cream-50 text-ink-800 antialiased dark:bg-ink-950 dark:text-cream-100">
        <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-2xl bg-clay-500 text-cream-50 shadow-[0_10px_24px_-12px_rgba(198,93,59,0.9)]">
                    <x-app-logo-icon class="size-6" />
                </span>
                <span class="grid leading-none">
                    <span class="ct-display text-xl font-semibold text-ink-900 dark:text-cream-50">Gestor</span>
                    <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-ink-400">De contactos</span>
                </span>
            </a>

            <nav class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 transition hover:bg-clay-600">Ir al panel</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-ink-600 transition hover:text-clay-600 dark:text-cream-200">Entrar</a>
                    <a href="{{ route('login') }}" class="rounded-xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 shadow-[0_10px_24px_-14px_rgba(198,93,59,1)] transition hover:bg-clay-600">Probar la demo</a>
                @endauth
            </nav>
        </header>

        <main>
            <section class="relative overflow-hidden">
                <div class="mx-auto grid w-full max-w-7xl items-center gap-12 px-5 pb-16 pt-10 sm:px-8 lg:grid-cols-[1.05fr_1fr] lg:pb-24 lg:pt-16">
                    <div class="relative z-10">
                        <p class="ct-eyebrow">Un directorio que se siente humano</p>
                        <h1 class="ct-display mt-4 max-w-2xl text-4xl font-semibold leading-[1.05] text-ink-900 sm:text-5xl lg:text-6xl dark:text-cream-50">
                            Tus contactos,<br>
                            <span class="text-clay-500">con contexto.</span>
                        </h1>
                        <p class="mt-5 max-w-xl text-base leading-relaxed text-ink-500 dark:text-ink-300">
                            Aquí conviven contactos, segmentos, notas e importación de CSV en una herramienta cálida.
                            Menos hojas de cálculo perdidas; más conversaciones que continúan donde quedaron.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-2xl bg-clay-500 px-5 py-3 text-sm font-semibold text-cream-50 shadow-[0_16px_32px_-18px_rgba(198,93,59,1)] transition hover:bg-clay-600">
                                Entrar a la demo
                                <flux:icon.arrow-right class="size-4" />
                            </a>
                            <a href="#funciones" class="inline-flex items-center gap-2 rounded-2xl border border-cream-300 bg-white/70 px-5 py-3 text-sm font-semibold text-ink-700 transition hover:border-clay-300 dark:border-white/10 dark:bg-white/5 dark:text-cream-100">
                                Ver qué incluye
                            </a>
                        </div>

                        <p class="mt-4 text-[11px] text-ink-400 dark:text-ink-300">
                            Demo con datos ficticios · Tres roles · Se reinicia cada noche
                        </p>
                    </div>

                    <div class="relative">
                        <div class="absolute -inset-6 -z-10 rounded-[3rem] bg-gradient-to-br from-clay-200/60 via-cream-100 to-sage-200/50 blur-2xl dark:from-clay-500/20 dark:via-transparent dark:to-sage-500/10"></div>

                        <div class="ct-card overflow-hidden !rounded-[2rem] p-4 sm:p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-400">Panel</p>
                                    <p class="ct-display mt-1 text-xl font-semibold text-ink-900 dark:text-cream-50">Hola, Ana</p>
                                </div>
                                <span class="flex size-9 items-center justify-center rounded-full bg-sage-500 text-[10px] font-bold text-white">AT</span>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <div class="rounded-2xl bg-clay-50 p-3 dark:bg-clay-500/10">
                                    <p class="text-[9px] font-semibold uppercase tracking-wider text-clay-600 dark:text-clay-300">Contactos</p>
                                    <p class="ct-display mt-1 text-2xl font-semibold text-ink-900 dark:text-cream-50">246</p>
                                </div>
                                <div class="rounded-2xl bg-sage-50 p-3 dark:bg-sage-500/10">
                                    <p class="text-[9px] font-semibold uppercase tracking-wider text-sage-700 dark:text-sage-300">Segmentos</p>
                                    <p class="ct-display mt-1 text-2xl font-semibold text-ink-900 dark:text-cream-50">8</p>
                                </div>
                                <div class="rounded-2xl bg-gold-50 p-3 dark:bg-gold-500/10">
                                    <p class="text-[9px] font-semibold uppercase tracking-wider text-gold-700 dark:text-gold-200">Cumples</p>
                                    <p class="ct-display mt-1 text-2xl font-semibold text-ink-900 dark:text-cream-50">12</p>
                                </div>
                            </div>

                            <div class="mt-3 space-y-2 rounded-2xl border border-cream-200 p-3 dark:border-white/10">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex size-8 items-center justify-center rounded-full bg-clay-500 text-[10px] font-bold text-white">ML</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-semibold text-ink-800 dark:text-cream-100">María López</p>
                                        <p class="text-[10px] text-ink-400">Hotel Sol · Clientes</p>
                                    </div>
                                    <span class="rounded-full bg-gold-100 px-2 py-0.5 text-[9px] font-bold text-gold-700 dark:bg-gold-500/15 dark:text-gold-200">Cumple en 4 días</span>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <span class="flex size-8 items-center justify-center rounded-full bg-sage-500 text-[10px] font-bold text-white">AC</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-semibold text-ink-800 dark:text-cream-100">Andrés Castillo</p>
                                        <p class="text-[10px] text-ink-400">Medios del Pacífico · Prensa</p>
                                    </div>
                                    <span class="rounded-full bg-sage-100 px-2 py-0.5 text-[9px] font-bold text-sage-700 dark:bg-sage-500/15 dark:text-sage-200">Nota nueva</span>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <span class="flex size-8 items-center justify-center rounded-full bg-gold-500 text-[10px] font-bold text-white">VO</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-semibold text-ink-800 dark:text-cream-100">Valeria Ortiz</p>
                                        <p class="text-[10px] text-ink-400">Café Darién · Restaurantes</p>
                                    </div>
                                    <span class="rounded-full bg-cream-200 px-2 py-0.5 text-[9px] font-bold text-ink-500 dark:bg-white/10 dark:text-cream-200">B2B</span>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between rounded-2xl bg-ink-900 px-4 py-3 text-cream-50">
                                <div class="flex items-center gap-2 text-[11px]">
                                    <flux:icon.arrow-up-tray class="size-3.5 text-clay-300" />
                                    lista-clientes-octubre.csv
                                </div>
                                <span class="rounded-full bg-sage-500/20 px-2 py-0.5 text-[9px] font-bold text-sage-200">121 creados</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="funciones" class="border-y border-cream-200/80 bg-cream-100/50 py-16 dark:border-white/5 dark:bg-white/[0.02]">
                <div class="mx-auto w-full max-w-7xl px-5 sm:px-8">
                    <div class="max-w-2xl">
                        <p class="ct-eyebrow">Funciones</p>
                        <h2 class="ct-display mt-2 text-3xl font-semibold text-ink-900 sm:text-4xl dark:text-cream-50">Todo lo necesario, nada de más.</h2>
                        <p class="mt-3 text-sm leading-relaxed text-ink-500 dark:text-ink-300">Construido desde cero para el portafolio, con el alcance cerrado a propósito.</p>
                    </div>

                    <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ([
                            ['icon' => 'arrow-up-tray', 'title' => 'Importar CSV con informe', 'text' => 'Vista previa, emparejamiento de columnas, detección de duplicados y un reporte de cada fila.'],
                            ['icon' => 'tag', 'title' => 'Segmentos con color', 'text' => 'Un contacto puede estar en varios segmentos. Filtra y exporta por afinidad, sector o campaña.'],
                            ['icon' => 'shield-check', 'title' => 'Roles de verdad', 'text' => 'Administración, edición y lectura comprobadas en el servidor con Policies de Laravel.'],
                            ['icon' => 'cake', 'title' => 'Cumpleaños a la vista', 'text' => 'Un panel que recuerda a quién felicitar y qué contacto necesita atención esta semana.'],
                            ['icon' => 'arrow-down-tray', 'title' => 'Exportación segura', 'text' => 'CSV listo para otra herramienta, con escape de fórmulas para evitar inyección.'],
                            ['icon' => 'chat-bubble-left-ellipsis', 'title' => 'Notas e historial', 'text' => 'Contexto que no cabe en un campo: preferencias, acuerdos y quién cambió qué.'],
                        ] as $feature)
                            <article class="ct-card p-5">
                                <span class="flex size-10 items-center justify-center rounded-2xl bg-clay-100 text-clay-600 dark:bg-clay-500/15 dark:text-clay-300">
                                    <flux:icon :name="$feature['icon']" class="size-5" />
                                </span>
                                <h3 class="ct-display mt-4 text-lg font-semibold text-ink-900 dark:text-cream-50">{{ $feature['title'] }}</h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-ink-500 dark:text-ink-300">{{ $feature['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="py-16">
                <div class="mx-auto grid w-full max-w-7xl items-center gap-8 px-5 sm:px-8 lg:grid-cols-[1fr_1fr]">
                    <div>
                        <p class="ct-eyebrow">Stack</p>
                        <h2 class="ct-display mt-2 text-3xl font-semibold text-ink-900 sm:text-4xl dark:text-cream-50">Laravel por dentro, cuidado por fuera.</h2>
                        <p class="mt-3 max-w-lg text-sm leading-relaxed text-ink-500 dark:text-ink-300">
                            PHP 8, Laravel 12, Livewire y Volt, PostgreSQL en Docker, Pruebas con Pest y CI en GitHub Actions.
                            Una app completa, no una maqueta.
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach (['PHP 8.4', 'Laravel 12', 'Livewire + Volt', 'PostgreSQL', 'Tailwind CSS 4', 'Docker', 'Pest', 'GitHub Actions'] as $tech)
                                <span class="ct-chip">{{ $tech }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-3xl bg-ink-900 p-6 text-cream-100 sm:p-8">
                        <p class="ct-eyebrow !text-clay-300">Roles de la demo</p>
                        <div class="mt-4 space-y-3">
                            @foreach ([
                                ['Administradora', 'Todo: contactos, importaciones y usuarios'],
                                ['Editora', 'Crea, edita e importa; no elimina'],
                                ['Lectora', 'Consulta y exporta, sin cambios'],
                            ] as [$role, $detail])
                                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold">{{ $role }}</p>
                                        <p class="text-[11px] text-cream-200/60">{{ $detail }}</p>
                                    </div>
                                    <flux:icon.arrow-right class="size-4 text-clay-300" />
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('login') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-clay-300 hover:text-clay-200">
                            Probar con un clic <flux:icon.arrow-right class="size-4" />
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-cream-200/80 py-8 dark:border-white/5">
            <div class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-3 px-5 text-xs text-ink-400 sm:flex-row sm:px-8 dark:text-ink-300">
                <p>Gestor de contactos · Proyecto de portafolio con datos ficticios.</p>
                <p>Inspirado en un ejercicio de mi práctica profesional; reconstruido desde cero con diseño propio.</p>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
