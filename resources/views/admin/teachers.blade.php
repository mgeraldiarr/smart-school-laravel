@extends('admin.layout')

@section('title', 'Data Guru')
@section('page_title', 'Master Data Guru & Pengajar')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-slate-900 tracking-tight">Daftar Tenaga Pendidik</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar guru terdaftar yang memiliki akses verifikasi absensi dan penilaian akademik.</p>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="text-slate-500">Total Tenaga Pendidik:</span>
            <span class="font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded text-[11px]">{{ $teachers->total() }}</span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="px-5 py-3">NUPTK</th>
                        <th class="px-5 py-3">Nama Lengkap</th>
                        <th class="px-5 py-3">Email Akun</th>
                        <th class="px-5 py-3">Nomor Telepon</th>
                        <th class="px-5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($teachers as $t)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5 font-mono font-medium text-slate-800">
                            <span class="bg-slate-100 px-2 py-1 rounded text-[11px] border border-slate-200/60">{{ $t->nuptk }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-900">{{ $t->full_name }}</td>
                        <td class="px-5 py-3.5 text-slate-500 font-mono text-[11px]">{{ $t->user->email ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $t->phone_number ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                Aktif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <svg class="w-10 h-10 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="text-xs font-medium text-slate-600">Belum ada data guru</p>
                                <p class="text-[11px] text-slate-400 mt-1">Data guru dapat ditambahkan melalui database seeder atau panel administrasi.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachers->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
            {{ $teachers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection