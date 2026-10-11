@props(['name'])

@php
    $paths = [
        'home' => 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1v-9.5Z',
        'students' => 'M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20M10 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm10 9v-1.5a3.5 3.5 0 0 0-2.5-3.35M15 4.15a3.5 3.5 0 0 1 0 6.7',
        'teacher' => 'M4 5h16v10H4zM8 19h8M12 15v4M8 9h5',
        'grades' => 'M5 20V10m7 10V4m7 16v-7',
        'upload' => 'M12 15V4m0 0L7.5 8.5M12 4l4.5 4.5M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3',
        'download' => 'M12 4v11m0 0-4.5-4.5M12 15l4.5-4.5M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3',
        'logout' => 'M15 12H4m0 0 3.5-3.5M4 12l3.5 3.5M10 5V4a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8a1 1 0 0 1-1-1v-1',
        'search' => 'm20 20-4.2-4.2M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z',
        'plus' => 'M12 5v14M5 12h14',
        'file' => 'M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Zm0 0v5h5M9 13h6M9 17h4',
        'check' => 'm5 12.5 4.5 4.5L19 7.5',
        'alert' => 'M12 9v4m0 3.5v.01M10.3 4.2 2.8 17.5A2 2 0 0 0 4.5 20.5h15a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z',
        'menu' => 'M4 7h16M4 12h16M4 17h16',
        'close' => 'M6 6l12 12M18 6 6 18',
        'chevron' => 'm9 6 6 6-6 6',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'size-[18px] shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="{{ $paths[$name] }}" />
</svg>
