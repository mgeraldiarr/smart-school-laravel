@extends('admin.layout')

@section('title', 'Data Siswa')
@section('page_title', 'Master Data Siswa')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-slate-900 tracking-tight">Daftar Siswa Terdaftar</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data induk siswa, penempatan rombongan belajar, dan status keaktifan akademik.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.import') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Import Data CSV</span>
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-500">Total Siswa Terdaftar:</span>
                <span class="font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded text-[11px]">{{ $students->total() }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="px-5 py-3">NIS</th>
                        <th class="px-5 py-3">Nama Lengkap</th>
                        <th class="px-5 py-3">Kelas</th>
                        <th class="px-5 py-3">Tanggal Lahir</th>
                        <th class="px-5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($students as $s)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5 font-mono font-medium text-slate-800">
                            <span class="bg-slate-100 px-2 py-1 rounded text-[11px] border border-slate-200/60">{{ $s->nis }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-900">{{ $s->full_name }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/60">
                                {{ $s->schoolClass->name ?? 'Belum Diatur' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $s->birth_date ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            @if($s->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                Non-aktif
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <svg class="w-10 h-10 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-xs font-medium text-slate-600">Belum ada data siswa</p>
                                <p class="text-[11px] text-slate-400 mt-1">Unggah file CSV untuk menambahkan data siswa secara kolektif.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection