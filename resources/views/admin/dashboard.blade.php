@extends('admin.layout')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Eksekutif')

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Siswa -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex items-start justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Total Siswa</span>
                <div class="text-2xl font-bold text-slate-900 mt-1 tabular-nums">{{ $totalSiswa }}</div>
                <span class="inline-block mt-2 text-[11px] font-medium text-slate-400">Terdaftar aktif</span>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>

        <!-- Total Guru -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex items-start justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Tenaga Pendidik</span>
                <div class="text-2xl font-bold text-slate-900 mt-1 tabular-nums">{{ $totalGuru }}</div>
                <span class="inline-block mt-2 text-[11px] font-medium text-slate-400">Guru terverifikasi</span>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>

        <!-- Total Rombel -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex items-start justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Rombongan Belajar</span>
                <div class="text-2xl font-bold text-slate-900 mt-1 tabular-nums">{{ $totalKelas }}</div>
                <span class="inline-block mt-2 text-[11px] font-medium text-slate-400">Kelas aktif</span>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>

        <!-- Presensi Hari Ini -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex items-start justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Kehadiran Hari Ini</span>
                <div class="text-2xl font-bold text-blue-600 mt-1 tabular-nums">{{ $totalHadirHariIni }}</div>
                <span class="inline-block mt-2 text-[11px] font-medium text-emerald-600">Terekam di sistem</span>
            </div>
            <div class="w-9 h-9 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200/80 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Log Presensi Terakhir</h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan presensi masuk sesi pelajaran hari ini</p>
            </div>
            <span class="text-[11px] font-medium text-slate-400">Sinkronisasi Otomatis</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Nama Siswa</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Mata Pelajaran</th>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">Metode</th>
                        <th class="px-6 py-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($attendancesRecent as $att)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-3.5 font-medium text-slate-900">{{ $att->student->full_name }}</td>
                            <td class="px-6 py-3.5 text-slate-600">{{ $att->student->schoolClass->name ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-slate-600">{{ $att->schedule->subject->name ?? '-' }}</td>
                            <td class="px-6 py-3.5 font-mono text-slate-500 tabular-nums">{{ $att->check_in_time }}</td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $att->method === 'face_scan' ? 'bg-sky-50 text-sky-700 border border-sky-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    {{ $att->method === 'face_scan' ? 'Face Recognition' : 'Manual Guru' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    {{ $att->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-xs text-slate-400">
                                Belum ada catatan presensi yang terekam hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection