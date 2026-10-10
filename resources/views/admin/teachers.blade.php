@extends('admin.layout')

@section('title', 'Data Guru')
@section('page_title', 'Master Data Guru & Pengajar')

@section('content')
<div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="p-5 border-b border-slate-100">
        <h3 class="font-bold text-sm text-slate-800">Daftar Tenaga Pendidik</h3>
        <span class="text-xs text-slate-400">Total guru: {{ $teachers->total() }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b">
                <tr>
                    <th class="p-4">NUPTK (Login Guru)</th>
                    <th class="p-4">Nama Lengkap</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">No. HP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($teachers as $t)
                <tr class="hover:bg-slate-50/50">
                    <td class="p-4 font-mono font-bold text-sky-700">{{ $t->nuptk }}</td>
                    <td class="p-4 font-bold text-slate-800">{{ $t->full_name }}</td>
                    <td class="p-4 text-slate-500">{{ $t->user->email ?? '-' }}</td>
                    <td class="p-4 text-slate-500">{{ $t->phone_number ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-400">Belum ada data guru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t">
        {{ $teachers->links() }}
    </div>
</div>
@endsection