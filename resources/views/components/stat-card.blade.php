@props([
    'icon' => 'users',
    'label',
    'value',
    'detail' => null,
    'tone' => 'clay',
])

@php
    $tones = [
        'clay' => 'bg-clay-100 text-clay-600 dark:bg-clay-500/15 dark:text-clay-300',
        'sage' => 'bg-sage-100 text-sage-700 dark:bg-sage-500/15 dark:text-sage-300',
        'gold' => 'bg-gold-100 text-gold-700 dark:bg-gold-500/15 dark:text-gold-200',
        'ink' => 'bg-cream-200 text-ink-600 dark:bg-white/10 dark:text-cream-200',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'ct-card p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-ink-400 dark:text-ink-300">{{ $label }}</p>
            <p class="ct-display mt-2 text-3xl font-semibold text-ink-900 dark:text-cream-50">{{ $value }}</p>
            @if ($detail)
                <p class="mt-1 text-xs text-ink-400 dark:text-ink-300">{{ $detail }}</p>
            @endif
        </div>
        <span class="flex size-10 shrink-0 items-center justify-center rounded-2xl {{ $tones[$tone] ?? $tones['clay'] }}">
            <flux:icon :name="$icon" class="size-5" />
        </span>
    </div>
</div>
