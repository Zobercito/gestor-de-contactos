@props(['compact' => false])

<a {{ $attributes->merge(['class' => 'group flex items-center gap-3']) }} href="{{ route('dashboard') }}" wire:navigate>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-clay-500 text-cream-50 shadow-[0_8px_20px_-8px_rgba(198,93,59,0.8)] transition group-hover:rotate-3">
        <x-app-logo-icon class="size-6" />
    </span>
    @unless ($compact)
        <span class="grid leading-none">
            <span class="ct-display text-xl font-semibold text-ink-900 dark:text-cream-50">Gestor</span>
            <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-ink-400">De contactos</span>
        </span>
    @endunless
</a>
