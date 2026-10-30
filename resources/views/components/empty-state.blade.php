@props([
    'icon' => 'user-group',
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-cream-400/80 px-6 py-10 text-center dark:border-white/15']) }}>
    <span class="flex size-12 items-center justify-center rounded-2xl bg-cream-200 text-ink-400 dark:bg-white/5 dark:text-ink-300">
        <flux:icon :name="$icon" class="size-6" />
    </span>
    <p class="mt-3 text-sm font-semibold text-ink-700 dark:text-cream-200">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 max-w-xs text-xs leading-relaxed text-ink-400 dark:text-ink-300">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-4">{{ $actions }}</div>
    @endisset
</div>
