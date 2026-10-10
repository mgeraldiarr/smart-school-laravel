@extends('admin.layout')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Sekolah')

@section('content')
<div class="space-y-6">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Siswa</span>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $totalSiswa }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl">🧑‍🎓</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Guru</span>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $totalGuru }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">👨‍🏫</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Kelas</span>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $totalKelas }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">🏫</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Hadir Hari Ini</span>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $totalHadirHariIni }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">✅</div>
        </div>
    </div>

    <!-- Quick Action Banner -->
    <div class="bg-gradient-to-r from-sky-600 to-indigo-700 rounded-3xl p-6 text-white flex flex-col md:flex-row items-center justify-between gap-4 shadow-lg shadow-sky-600/20">
        <div>
            <h3 class="text-lg font-bold">Import Data Siswa & Wali Murid Cepat</h3>
            <p class="text-xs text-sky-100 mt-1">Gunakan file CSV / Excel untuk memasukkan data siswa baru dan akun orang tua sekaligus dalam 1 detik.</p>
        </div>
        <a href="{{ route('admin.import') }}" class="px-5 py-2.5 bg-white text-sky-700 font-bold text-xs rounded-xl hover:bg-sky-50 shadow transition whitespace-nowrap">
            📥 Buka Menu Import
        </a>
    </div>
</div>
@endsection