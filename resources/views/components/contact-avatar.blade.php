@props([
    'name',
    'color' => '#C65D3B',
    'size' => 'md',
])

@php
    $initials = \Illuminate\Support\Str::of($name)->explode(' ')->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    $sizes = [
        'sm' => 'size-8 text-[10px]',
        'md' => 'size-10 text-xs',
        'lg' => 'size-14 text-base',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center rounded-full font-bold text-white ring-2 ring-white/70 dark:ring-white/10 '.($sizes[$size] ?? $sizes['md'])]) }}
      style="background-color: {{ $color }}">
    {{ $initials }}
</span>
