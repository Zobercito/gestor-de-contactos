@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="ct-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="ct-display mt-1 text-3xl font-semibold text-ink-900 sm:text-[2.1rem] dark:text-cream-50">{{ $title }}</h1>
        @if ($description)
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-500 dark:text-ink-300">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2.5">
            {{ $actions }}
        </div>
    @endisset
</div>
