@extends('admin.layout')

@section('title', 'Import Data')
@section('page_title', 'Import Data Siswa & Wali Murid')

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-base font-semibold text-slate-900 tracking-tight">Unggah Berkas Data Kolektif</h2>
        <p class="text-xs text-slate-500 mt-0.5">Sistem akan secara otomatis memproses data siswa, mengaitkan akun wali murid berdasarkan alamat email, dan mengalokasikan kelas.</p>
    </div>

    <!-- Upload Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
        <form action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-2">Berkas Spreadsheet (Format CSV)</label>
                <div class="border-2 border-dashed border-slate-200 hover:border-slate-300 rounded-xl p-8 text-center bg-slate-50/40 hover:bg-slate-50 transition cursor-pointer flex flex-col items-center justify-center">
                    <svg class="w-10 h-10 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <div class="text-xs text-slate-600 mb-2">
                        <label for="file_excel" class="font-semibold text-blue-600 hover:text-blue-700 cursor-pointer">Pilih berkas CSV</label>
                        <span>atau seret berkas ke area ini</span>
                    </div>
                    <input id="file_excel" type="file" name="file_excel" accept=".csv" required class="text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                    <span class="text-[11px] text-slate-400 mt-2">Maksimal ukuran berkas 5MB (.csv dengan pemisah koma)</span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('admin.students') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                    Kembali ke Data Siswa
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Mulai Impor Data</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Spesifikasi Format CSV -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider">Format Kolom CSV yang Didukung</h3>
            <span class="text-[11px] font-mono text-slate-400">Header wajib disertakan pada baris pertama</span>
        </div>

        <div class="overflow-x-auto border border-slate-200/70 rounded-lg">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold text-[11px]">
                        <th class="px-4 py-2.5">No</th>
                        <th class="px-4 py-2.5">Nama Kolom</th>
                        <th class="px-4 py-2.5">Tipe Data</th>
                        <th class="px-4 py-2.5">Contoh Nilai</th>
                        <th class="px-4 py-2.5">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 text-[11px]">
                    <tr>
                        <td class="px-4 py-2 text-slate-400 font-mono">1</td>
                        <td class="px-4 py-2 font-mono font-medium text-slate-800">NIS</td>
                        <td class="px-4 py-2 text-slate-500">Angka / Text</td>
                        <td class="px-4 py-2 font-mono">20241010</td>
                        <td class="px-4 py-2 text-slate-500">Nomor Induk Siswa unik (ID login ortu)</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 text-slate-400 font-mono">2</td>
                        <td class="px-4 py-2 font-mono font-medium text-slate-800">Nama Siswa</td>
                        <td class="px-4 py-2 text-slate-500">Text</td>
                        <td class="px-4 py-2 font-medium">Rizky Pratama</td>
                        <td class="px-4 py-2 text-slate-500">Nama lengkap siswa</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 text-slate-400 font-mono">3</td>
                        <td class="px-4 py-2 font-mono font-medium text-slate-800">Kelas</td>
                        <td class="px-4 py-2 text-slate-500">Text</td>
                        <td class="px-4 py-2">8-A</td>
                        <td class="px-4 py-2 text-slate-500">Otomatis didaftarkan jika belum ada</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 text-slate-400 font-mono">4</td>
                        <td class="px-4 py-2 font-mono font-medium text-slate-800">Tanggal Lahir</td>
                        <td class="px-4 py-2 text-slate-500">YYYY-MM-DD</td>
                        <td class="px-4 py-2 font-mono">2011-05-12</td>
                        <td class="px-4 py-2 text-slate-500">Format tanggal internasional</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 text-slate-400 font-mono">5</td>
                        <td class="px-4 py-2 font-mono font-medium text-slate-800">Nama Wali</td>
                        <td class="px-4 py-2 text-slate-500">Text</td>
                        <td class="px-4 py-2 font-medium">Budi Pratama</td>
                        <td class="px-4 py-2 text-slate-500">Nama orang tua / wali murid</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 text-slate-400 font-mono">6</td>
                        <td class="px-4 py-2 font-mono font-medium text-slate-800">Gmail Wali</td>
                        <td class="px-4 py-2 text-slate-500">Email</td>
                        <td class="px-4 py-2 font-mono">budi@gmail.com</td>
                        <td class="px-4 py-2 text-slate-500">Tujuan pengiriman kode OTP reset kata sandi</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection