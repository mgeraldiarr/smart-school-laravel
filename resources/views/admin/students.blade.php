@extends('admin.layout')

@section('title', 'Data Siswa')
@section('page_title', 'Master Data Siswa')

@section('content')
<div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="p-5 border-b border-slate-100 flex justify-between items-center">
        <div>
            <h3 class="font-bold text-sm text-slate-800">Daftar Siswa Terdaftar</h3>
            <span class="text-xs text-slate-400">Total siswa: {{ $students->total() }}</span>
        </div>
        <a href="{{ route('admin.import') }}" class="px-3.5 py-2 bg-sky-600 text-white rounded-xl text-xs font-bold hover:bg-sky-700 transition">
            + Import dari Excel
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b">
                <tr>
                    <th class="p-4">NIS (ID Login)</th>
                    <th class="p-4">Nama Lengkap</th>
                    <th class="p-4">Kelas</th>
                    <th class="p-4">Tgl Lahir</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($students as $s)
                <tr class="hover:bg-slate-50/50">
                    <td class="p-4 font-mono font-bold text-sky-700">{{ $s->nis }}</td>
                    <td class="p-4 font-bold text-slate-800">{{ $s->full_name }}</td>
                    <td class="p-4"><span class="px-2.5 py-1 bg-slate-100 rounded-lg font-bold">{{ $s->schoolClass->name ?? '-' }}</span></td>
                    <td class="p-4 text-slate-500">{{ $s->birth_date }}</td>
                    <td class="p-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $s->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                            {{ $s->is_active ? 'Aktif' : 'Non-aktif' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400">Belum ada data siswa. Silakan import file Excel.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t">
        {{ $students->links() }}
    </div>
</div>
@endsection