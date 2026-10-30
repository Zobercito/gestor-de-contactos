<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-cream-50 text-ink-800 antialiased dark:bg-ink-950 dark:text-cream-100">
        <flux:sidebar sticky stashable class="border-r border-cream-300/80 bg-cream-100/80 backdrop-blur-xl dark:border-white/10 dark:bg-ink-900/80">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="mb-6 px-1" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline" class="text-ink-600 dark:text-cream-200 [&_[data-flux-navlist-item][data-current]]:bg-white [&_[data-flux-navlist-item][data-current]]:text-clay-700 [&_[data-flux-navlist-item][data-current]]:shadow-sm dark:[&_[data-flux-navlist-item][data-current]]:bg-white/10 dark:[&_[data-flux-navlist-item][data-current]]:text-cream-50">
                <flux:navlist.group heading="General" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Panel</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('contacts.index')" :current="request()->routeIs('contacts.*')" wire:navigate>Contactos</flux:navlist.item>
                    <flux:navlist.item icon="tag" :href="route('segments.index')" :current="request()->routeIs('segments.*')" wire:navigate>Segmentos</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group heading="Datos" class="mt-6 grid">
                    <flux:navlist.item icon="arrow-up-tray" :href="route('imports.index')" :current="request()->routeIs('imports.*')" wire:navigate>Importar CSV</flux:navlist.item>
                    <flux:navlist.item icon="clock" :href="route('activity.index')" :current="request()->routeIs('activity.*')" wire:navigate>Actividad</flux:navlist.item>
                </flux:navlist.group>

                @if (auth()->user()->isAdmin())
                    <flux:navlist.group heading="Administración" class="mt-6 grid">
                        <flux:navlist.item icon="user-group" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>Usuarios</flux:navlist.item>
                    </flux:navlist.group>
                @endif
            </flux:navlist>

            <flux:spacer />

            @if (auth()->user()->canWrite())
                <a href="{{ route('contacts.create') }}" wire:navigate
                   class="mb-4 flex items-center justify-center gap-2 rounded-2xl bg-clay-500 px-4 py-2.5 text-sm font-semibold text-cream-50 shadow-[0_10px_24px_-12px_rgba(198,93,59,0.9)] transition hover:bg-clay-600">
                    <flux:icon.plus class="size-4" />
                    Nuevo contacto
                </a>
            @endif

            @if (config('demo.enabled'))
                <div class="mb-4 rounded-2xl border border-gold-200 bg-gold-50/80 px-3.5 py-3 text-[11px] leading-relaxed text-gold-700 dark:border-gold-500/20 dark:bg-gold-500/10 dark:text-gold-200">
                    <span class="font-semibold">Modo demostración</span><br>
                    Los datos vuelven a su estado inicial cada noche.
                </div>
            @endif

            <flux:dropdown position="bottom" align="start">
                <button type="button" class="flex w-full items-center gap-3 rounded-2xl border border-transparent px-2 py-2 text-left transition hover:border-cream-300 hover:bg-white/70 dark:hover:border-white/10 dark:hover:bg-white/5">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sage-500 text-xs font-bold text-white">
                        {{ auth()->user()->initials() }}
                    </span>
                    <span class="grid min-w-0 flex-1 leading-tight">
                        <span class="truncate text-sm font-semibold text-ink-800 dark:text-cream-100">{{ auth()->user()->name }}</span>
                        <span class="truncate text-[11px] text-ink-400 dark:text-ink-300">{{ auth()->user()->role->label() }}</span>
                    </span>
                    <flux:icon.chevron-up-down class="size-4 shrink-0 text-ink-400" />
                </button>

                <flux:menu class="w-[230px]">
                    <div class="px-3 py-2">
                        <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-ink-400">{{ auth()->user()->email }}</p>
                    </div>
                    <flux:menu.separator />
                    <flux:menu.item href="{{ route('settings.profile') }}" icon="cog" wire:navigate>Mi perfil</flux:menu.item>
                    <flux:menu.item href="{{ route('settings.appearance') }}" icon="paint-brush" wire:navigate>Apariencia</flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            Cerrar sesión
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="border-b border-cream-300/80 bg-cream-50/80 backdrop-blur-xl lg:hidden dark:border-white/10 dark:bg-ink-950/80">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <a href="{{ route('dashboard') }}" wire:navigate class="ml-1">
                <x-app-logo compact />
            </a>
            <flux:spacer />
            <span class="flex size-9 items-center justify-center rounded-full bg-sage-500 text-xs font-bold text-white">
                {{ auth()->user()->initials() }}
            </span>
        </flux:header>

        <flux:main class="min-h-screen bg-cream-50 px-4 py-6 sm:px-6 lg:px-10 lg:py-8 dark:bg-ink-950">
            {{ $slot }}
        </flux:main>

        @fluxScripts
    </body>
</html>
