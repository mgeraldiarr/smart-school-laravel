@props(['status'])

@php
    $styles = [
        'Hadir' => 'bg-hadir/10 text-hadir',
        'Sakit' => 'bg-sakit/10 text-sakit',
        'Izin' => 'bg-izin/10 text-izin',
        'Alpa' => 'bg-alpa/10 text-alpa',
        'Aktif' => 'bg-hadir/10 text-hadir',
        'Nonaktif' => 'bg-redup/10 text-redup',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-md px-2 py-0.5 text-[13px] font-semibold', $styles[$status] ?? $styles['Nonaktif']]) }}>
    {{ $status }}
</span>
