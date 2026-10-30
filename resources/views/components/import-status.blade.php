@props(['status'])

@php
    $classes = match ($status->color()) {
        'amber' => '!bg-gold-100 !text-gold-700 dark:!bg-gold-500/15 dark:!text-gold-200',
        'sky' => '!bg-sky-100 !text-sky-700 dark:!bg-sky-500/15 dark:!text-sky-200',
        'sage' => '!bg-sage-100 !text-sage-700 dark:!bg-sage-500/15 dark:!text-sage-200',
        default => '!bg-rose-100 !text-rose-700 dark:!bg-rose-500/15 dark:!text-rose-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'ct-chip !border-transparent '.$classes]) }}>
    @if (in_array($status->value, ['pending', 'processing'], true))
        <span class="size-1.5 animate-pulse rounded-full bg-current"></span>
    @endif
    {{ $status->label() }}
</span>
