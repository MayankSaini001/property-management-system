@props(['value', 'percent' => 0, 'color' => '#2b6cb0', 'track' => '#e5e7eb', 'suffix' => ''])

@php
    $radius = 48;
    $circumference = 2 * M_PI * $radius;
    $percent = max(0, min(100, (float) $percent));
    $filled = $circumference * $percent / 100;
@endphp

<svg viewBox="0 0 120 120" class="h-32 w-32 shrink-0" role="img" aria-label="{{ $value }}{{ $suffix }}">
    <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="{{ $track }}" stroke-width="8" />
    <circle cx="60" cy="60" r="{{ $radius }}" fill="none" stroke="{{ $color }}" stroke-width="8"
        stroke-linecap="round"
        stroke-dasharray="{{ round($filled, 2) }} {{ round($circumference, 2) }}"
        transform="rotate(-90 60 60)" />
    <text x="60" y="60" text-anchor="middle" dominant-baseline="central" font-size="28" font-weight="500" fill="{{ $color }}">{{ $value }}{{ $suffix }}</text>
</svg>