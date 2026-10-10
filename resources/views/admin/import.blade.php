@extends('admin.layout')

@section('title', 'Import Data Excel')
@section('page_title', 'Import Data Siswa & Wali Murid')

@section('content')
<div class="max-w-3xl space-y-6">
    <!-- Upload Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <div>
            <h3 class="font-bold text-sm text-slate-900">Upload File CSV Data Sekolah</h3>
            <p class="text-xs text-slate-500 mt-1">File CSV akan otomatis membuat data Siswa, menghubungkan Akun Wali Murid, dan mendaftarkan Kelas.</p>
        </div>

        <form action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="border-2 border-dashed border-sky-300 bg-sky-50/50 rounded-2xl p-6 text-center">
                <input type="file" name="file_excel" accept=".csv" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-600 file:text-white hover:file:bg-sky-700 cursor-pointer" />
                <span class="text-[11px] text-slate-400 block mt-2">Hanya mendukung file format .csv (Comma Separated Values)</span>
            </div>

            <button type="submit" class="px-6 py-3 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-xs shadow-md transition">
                ⚡ Mulai Import Data Sekarang
            </button>
        </form>
    </div>

    <!-- Petunjuk Format Kolom CSV -->
    <div class="bg-slate-50 p-6 rounded-3xl border border-slate-200 text-xs space-y-2">
        <span class="font-bold text-slate-800 block">📋 Petunjuk Urutan Kolom CSV (Wajib Sesuai):</span>
        <code class="block bg-slate-900 text-emerald-400 p-3 rounded-xl font-mono text-[11px]">
            NIS,Nama Siswa,Kelas,Tanggal Lahir (YYYY-MM-DD),Nama Wali,Gmail Wali<br>
            20241010,Rizky Pratama,8-A,2011-05-12,Budi Pratama,budi@gmail.com<br>
            20241011,Siti Nurhaliza,8-A,2011-09-20,Nurul Huda,nurul@gmail.com
        </code>
    </div>
</div>
@endsection 