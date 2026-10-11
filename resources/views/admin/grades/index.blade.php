@extends('layouts.admin')

@section('title', 'Nilai')

@php
    $fmt = fn ($n) => $n === null ? '-' : number_format((float) $n, 1, ',', '.');
    $selectedClass = $classes->firstWhere('id', $classId);
    $chartSpec = [
        'kind' => 'bar',
        'horizontal' => true,
        'max' => 100,
        'labels' => $chart['labels'],
        'datasets' => [
            ['label' => 'Tugas', 'data' => $chart['series']['Tugas'], 'color' => 'emas'],
            ['label' => 'UTS', 'data' => $chart['series']['UTS'], 'color' => 'izin'],
            ['label' => 'UAS', 'data' => $chart['series']['UAS'], 'color' => 'dongker'],
        ],
    ];
@endphp

@section('content')
    <x-admin.page-header title="Rekap nilai" :description="'Rata-rata Tugas, UTS, dan UAS per mata pelajaran' . ($selectedClass ? ' di kelas ' . $selectedClass->name : ' dari semua kelas') . '. KKM ' . $kkm . '.'">
        <x-slot:actions>
            <form method="GET" class="flex items-center gap-2">
                <label for="kelas" class="sr-only">Kelas</label>
                <select id="kelas" name="kelas" class="field w-44" data-auto-submit>
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected($classId === $class->id)>Kelas {{ $class->name }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="btn btn-secondary">Terapkan</button></noscript>
            </form>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($summary->isEmpty())
        <div class="panel px-5 py-16 text-center">
            <p class="font-semibold text-dongker">Belum ada nilai{{ $selectedClass ? ' untuk kelas ' . $selectedClass->name : '' }}</p>
            <p class="mx-auto mt-1 max-w-sm text-sm text-redup">Nilai diinput guru lewat aplikasi seluler dan akan langsung muncul di sini.</p>
        </div>
    @else
        <div class="grid gap-5 xl:grid-cols-12">
            <x-admin.panel class="xl:col-span-7" title="Rata-rata per mata pelajaran">
                <x-admin.chart id="chart-grades" height="h-96" label="Grafik rata-rata nilai per mata pelajaran" :spec="$chartSpec" />
            </x-admin.panel>

            <x-admin.panel class="xl:col-span-5" title="Di bawah KKM" :description="'Nilai UTS dan UAS di bawah ' . $kkm . ', terendah di atas.'" flush>
                <ul class="max-h-96 divide-y divide-garis overflow-y-auto border-t border-garis">
                    @forelse ($belowKkm as $grade)
                        <li class="flex items-center justify-between gap-3 px-5 py-2.5 text-sm">
                            <div class="min-w-0">
                                <p class="truncate font-semibold">{{ $grade->student->full_name ?? '-' }}</p>
                                <p class="truncate text-[13px] text-redup">{{ $grade->student->schoolClass->name ?? '-' }}, {{ $grade->subject->name ?? '-' }}, {{ $grade->assessment_type }}</p>
                            </div>
                            <span class="shrink-0 text-lg font-extrabold text-alpa">{{ $fmt($grade->score) }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center text-sm text-redup">Semua nilai UTS dan UAS sudah mencapai KKM.</li>
                    @endforelse
                </ul>
            </x-admin.panel>

            <x-admin.panel class="xl:col-span-12" title="Rincian per mata pelajaran" flush>
                <div class="overflow-x-auto border-t border-garis">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mata pelajaran</th>
                                @foreach ($types as $type)
                                    <th class="text-right">{{ $type }}</th>
                                @endforeach
                                <th class="text-right">Jumlah nilai</th>
                                <th class="text-right">Terendah</th>
                                <th class="text-right">Tertinggi</th>
                                <th class="text-right">Di bawah KKM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summary as $subject => $rows)
                                @php($byType = $rows->keyBy('type'))
                                <tr>
                                    <td class="font-semibold">{{ $subject }}</td>
                                    @foreach ($types as $type)
                                        @php($avg = $byType[$type]->average ?? null)
                                        <td @class(['text-right font-semibold', 'text-alpa' => $avg !== null && $avg < $kkm])>{{ $fmt($avg) }}</td>
                                    @endforeach
                                    <td class="text-right text-redup">{{ $rows->sum('total') }}</td>
                                    <td class="text-right text-redup">{{ $fmt($rows->min('lowest')) }}</td>
                                    <td class="text-right text-redup">{{ $fmt($rows->max('highest')) }}</td>
                                    <td @class(['text-right', 'font-semibold text-alpa' => $rows->sum('below_kkm') > 0, 'text-redup' => $rows->sum('below_kkm') == 0])>{{ $rows->sum('below_kkm') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>
        </div>
    @endif
@endsection
