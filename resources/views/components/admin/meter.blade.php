{{-- Batang persentase; digambar dengan SVG agar lebar tidak perlu style inline. --}}
@props(['value' => null, 'tone' => null])

@php
    $value = $value === null ? 0 : max(0, min(100, (float) $value));
    $tone ??= $value >= 90 ? 'fill-hadir' : ($value >= 75 ? 'fill-emas' : 'fill-alpa');
@endphp

<svg {{ $attributes->merge(['class' => 'h-2 w-full overflow-hidden rounded-full']) }} aria-hidden="true">
    <rect width="100%" height="100%" class="fill-dongker-50" />
    <rect width="{{ $value }}%" height="100%" rx="4" class="{{ $tone }}" />
</svg>
