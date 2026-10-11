@extends('layouts.admin')

@section('title', 'Beranda')

@php
    $fmt = fn ($n, $d = 1) => $n === null ? '-' : number_format($n, $d, ',', '.');
    $isToday = $focusDate->isToday();
    $focusLabel = $isToday ? 'hari ini' : $focusDate->locale('id')->translatedFormat('l, j M');
    $recorded = $focus->sum();
    $unrecorded = max($activeStudents - $recorded, 0);
    $statusColor = ['Hadir' => 'hadir', 'Sakit' => 'sakit', 'Izin' => 'izin', 'Alpa' => 'alpa'];
    $presentDelta = $focus['Hadir'] - $presentBefore;
    $teachersTracked = $compliance->whereNotNull('rate');
    $complianceAverage = $teachersTracked->isEmpty() ? null : $teachersTracked->avg('rate');
    $statusSwatch = ['Hadir' => 'bg-hadir', 'Sakit' => 'bg-sakit', 'Izin' => 'bg-izin', 'Alpa' => 'bg-alpa'];

    $trendSpec = [
        'kind' => 'bar',
        'stacked' => true,
        'labels' => $trend['labels'],
        'datasets' => collect($trend['series'])
            ->map(fn ($data, $status) => ['label' => $status, 'data' => $data, 'color' => $statusColor[$status]])
            ->values(),
    ];
    $todaySpec = [
        'kind' => 'doughnut',
        'legend' => false,
        'labels' => [...array_keys($statusColor), 'Belum tercatat'],
        'datasets' => [[
            'label' => 'Siswa',
            'data' => [...$focus->values(), $unrecorded],
            'colors' => [...array_values($statusColor), 'dongker-50'],
        ]],
    ];
    $gradesSpec = [
        'kind' => 'bar',
        'horizontal' => true,
        'max' => 100,
        'labels' => $grades['labels'],
        'datasets' => [
            ['label' => 'Tugas', 'data' => $grades['series']['Tugas'], 'color' => 'emas'],
            ['label' => 'UTS', 'data' => $grades['series']['UTS'], 'color' => 'izin'],
            ['label' => 'UAS', 'data' => $grades['series']['UAS'], 'color' => 'dongker'],
        ],
    ];
@endphp

@section('content')
    <x-admin.page-header title="Beranda" :description="$isToday ? 'Ringkasan sekolah hari ini dan ' . $periodDays . ' hari terakhir.' : 'Hari ini tidak ada sekolah. Angka harian diambil dari ' . $focusLabel . '.'" />

    {{-- Angka utama: satu panel dibagi empat, bukan empat kartu terpisah. --}}
    <section class="panel grid grid-cols-2 divide-garis lg:grid-cols-4 lg:divide-x" aria-label="Ringkasan">
        <div class="col-span-2 border-b border-garis p-5 lg:col-span-1 lg:border-b-0">
            <p class="text-sm font-semibold text-redup">Kehadiran {{ $focusLabel }}</p>
            <p class="mt-2 flex items-baseline gap-2">
                <span class="text-4xl font-extrabold tracking-tight text-dongker">{{ $presentRate === null ? '-' : $presentRate . '%' }}</span>
            </p>
            <p class="mt-1 text-[13px] text-redup">
                {{ $focus['Hadir'] }} dari {{ $activeStudents }} siswa aktif.
                @if ($presentDelta !== 0)
                    <span @class(['font-semibold', 'text-hadir' => $presentDelta > 0, 'text-alpa' => $presentDelta < 0])>
                        {{ $presentDelta > 0 ? '+' : '' }}{{ $presentDelta }} dibanding hari sekolah sebelumnya
                    </span>
                @endif
            </p>
        </div>
        <div class="border-r border-garis p-5 lg:border-r-0">
            <p class="text-sm font-semibold text-redup">Siswa aktif</p>
            <p class="mt-2 text-[28px] font-extrabold tracking-tight">{{ number_format($activeStudents, 0, ',', '.') }}</p>
            <p class="mt-1 text-[13px] text-redup">di {{ $classCount }} rombel</p>
        </div>
        <div class="p-5">
            <p class="text-sm font-semibold text-redup">Guru</p>
            <p class="mt-2 text-[28px] font-extrabold tracking-tight">{{ $teacherCount }}</p>
            <p class="mt-1 text-[13px] text-redup">
                {{ $complianceAverage === null ? 'Belum ada jadwal mengajar' : 'Rata-rata ' . $fmt($complianceAverage, 0) . '% sesi terabsen' }}
            </p>
        </div>
        <div class="col-span-2 border-t border-garis p-5 lg:col-span-1 lg:border-t-0">
            <p class="text-sm font-semibold text-redup">Rata-rata ujian</p>
            <dl class="mt-2 flex items-baseline gap-6">
                <div>
                    <dt class="text-[13px] text-redup">UTS</dt>
                    <dd class="text-[28px] font-extrabold tracking-tight">{{ $fmt($averageUts) }}</dd>
                </div>
                <div>
                    <dt class="text-[13px] text-redup">UAS</dt>
                    <dd class="text-[28px] font-extrabold tracking-tight">{{ $fmt($averageUas) }}</dd>
                </div>
            </dl>
        </div>
    </section>

    <div class="mt-5 grid gap-5 xl:grid-cols-12">
        <x-admin.panel class="xl:col-span-8" title="Tren kehadiran siswa" :description="'Jumlah siswa per status, Senin sampai Sabtu, ' . $periodDays . ' hari terakhir.'">
            @if (collect($trend['series'])->flatten()->sum() > 0)
                <x-admin.chart id="chart-trend" height="h-72" label="Grafik batang kehadiran harian" :spec="$trendSpec" />
            @else
                <p class="grid h-72 place-items-center text-sm text-redup">Belum ada presensi dalam {{ $periodDays }} hari terakhir.</p>
            @endif
        </x-admin.panel>

        <x-admin.panel class="xl:col-span-4" :title="'Status ' . $focusLabel" description="Siswa unik per status presensi.">
            <div class="relative mx-auto h-48 max-w-48">
                <x-admin.chart id="chart-today" height="h-48" :label="'Diagram status presensi ' . $focusLabel" :spec="$todaySpec" />
                <div class="pointer-events-none absolute inset-0 grid place-items-center text-center">
                    <div>
                        <p class="text-2xl font-extrabold text-dongker">{{ $recorded }}</p>
                        <p class="text-[12px] text-redup">tercatat</p>
                    </div>
                </div>
            </div>

            <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                @foreach ($focus as $status => $count)
                    <div class="flex items-center gap-2">
                        <span class="size-2.5 rounded-sm {{ $statusSwatch[$status] }}"></span>
                        <dt class="text-redup">{{ $status }}</dt>
                        <dd class="ml-auto font-semibold">{{ $count }}</dd>
                    </div>
                @endforeach
                <div class="col-span-2 flex items-center gap-2 border-t border-garis pt-2">
                    <span class="size-2.5 rounded-sm bg-dongker-50 ring-1 ring-garis ring-inset"></span>
                    <dt class="text-redup">Belum tercatat</dt>
                    <dd class="ml-auto font-semibold">{{ $unrecorded }}</dd>
                </div>
            </dl>
        </x-admin.panel>

        <x-admin.panel class="xl:col-span-7" title="Rata-rata nilai per mata pelajaran" description="Tugas, UTS, dan UAS dari semua kelas.">
            <x-slot:action>
                <a href="{{ route('admin.grades.index') }}" class="font-semibold text-dongker hover:underline">Rekap nilai</a>
            </x-slot:action>

            @if (count($grades['labels']) > 0)
                <x-admin.chart id="chart-grades" height="h-96" label="Grafik rata-rata nilai per mata pelajaran" :spec="$gradesSpec" />
            @else
                <p class="grid h-72 place-items-center text-sm text-redup">Belum ada nilai yang diinput guru.</p>
            @endif
        </x-admin.panel>

        <x-admin.panel class="xl:col-span-5" title="Kehadiran per kelas" :description="'Persentase presensi berstatus Hadir, ' . $periodDays . ' hari terakhir.'">
            <ul class="space-y-3.5">
                @forelse ($byClass as $class)
                    <li>
                        <div class="mb-1.5 flex items-baseline justify-between text-sm">
                            <span class="font-semibold">{{ $class->name }}</span>
                            <span class="text-redup">
                                @if ($class->rate === null)
                                    Belum ada data
                                @else
                                    <strong class="font-semibold text-teks">{{ $fmt($class->rate) }}%</strong> dari {{ number_format($class->total, 0, ',', '.') }} presensi
                                @endif
                            </span>
                        </div>
                        <x-admin.meter :value="$class->rate" />
                    </li>
                @empty
                    <li class="py-8 text-center text-sm text-redup">Belum ada kelas.</li>
                @endforelse
            </ul>
        </x-admin.panel>

        <x-admin.panel class="xl:col-span-5" title="Guru mengisi presensi" :description="'Jam pelajaran terjadwal yang sudah diabsen, ' . $periodDays . ' hari terakhir. Hari libur belum dikecualikan.'">
            <ul class="divide-y divide-garis">
                @forelse ($compliance->take(8) as $teacher)
                    <li class="py-3 first:pt-0 last:pb-0">
                        <div class="mb-1.5 flex items-baseline justify-between gap-3 text-sm">
                            <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="truncate font-semibold hover:text-dongker hover:underline">{{ $teacher->name }}</a>
                            <span class="shrink-0 text-redup">
                                @if ($teacher->rate === null)
                                    Tidak ada jadwal
                                @else
                                    <strong class="font-semibold text-teks">{{ $teacher->recorded }}</strong>/{{ $teacher->expected }} sesi
                                @endif
                            </span>
                        </div>
                        <x-admin.meter :value="$teacher->rate" />
                    </li>
                @empty
                    <li class="py-8 text-center text-sm text-redup">Belum ada guru.</li>
                @endforelse
            </ul>
            @if ($compliance->count() > 8)
                <p class="mt-3 text-[13px] text-redup">Menampilkan 8 guru dengan persentase terendah.</p>
            @endif
        </x-admin.panel>

        <x-admin.panel class="xl:col-span-7" title="Presensi terbaru" description="Dari pindai wajah dan input guru." flush>
            <div class="overflow-x-auto border-t border-garis">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Mata pelajaran</th>
                            <th>Waktu</th>
                            <th>Lewat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recent as $attendance)
                            <tr>
                                <td>
                                    <p class="font-semibold">{{ $attendance->student->full_name }}</p>
                                    <p class="text-[13px] text-redup">{{ $attendance->student->schoolClass->name ?? '-' }}</p>
                                </td>
                                <td>{{ $attendance->schedule->subject->name ?? '-' }}</td>
                                <td class="whitespace-nowrap text-redup">
                                    {{ \Illuminate\Support\Carbon::parse($attendance->date)->locale('id')->translatedFormat('j M') }},
                                    {{ substr($attendance->check_in_time, 0, 5) }}
                                </td>
                                <td class="whitespace-nowrap text-redup">{{ $attendance->method === 'face_scan' ? 'Pindai wajah' : 'Input guru' }}</td>
                                <td><x-admin.status :status="$attendance->status" /></td>
                            </tr>
                        @empty
                            <x-admin.empty-row colspan="5" title="Belum ada presensi">
                                Presensi muncul di sini setelah siswa memindai wajah atau guru mengisinya dari aplikasi.
                            </x-admin.empty-row>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.panel>
    </div>
@endsection
