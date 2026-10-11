@extends('layouts.admin')

@section('title', 'Impor ' . $import->subject())

@php
    $result = session('import_result');
    $labels = ['siswa' => 'Siswa', 'guru' => 'Guru'];
@endphp

@section('content')
    <x-admin.page-header
        title="Impor dari Excel"
        description="Tambahkan atau perbarui banyak data sekaligus. Data yang sudah ada dicocokkan lewat NIS atau NUPTK, jadi mengunggah ulang tidak membuat data ganda." />

    <nav class="mb-5 inline-flex rounded-lg border border-garis bg-white p-1" aria-label="Jenis data">
        @foreach ($labels as $key => $label)
            <a href="{{ route('admin.import.create', $key) }}"
               @if ($key === $type) aria-current="page" @endif
               @class([
                   'rounded-md px-4 py-1.5 text-sm font-semibold transition-colors',
                   'bg-dongker text-white' => $key === $type,
                   'text-redup hover:text-dongker' => $key !== $type,
               ])>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($result)
        <x-admin.panel class="mb-5" :title="'Hasil impor ' . $result['file']">
            <dl class="grid grid-cols-3 divide-x divide-garis rounded-lg border border-garis">
                <div class="p-3">
                    <dt class="text-[13px] text-redup">Data baru</dt>
                    <dd class="text-2xl font-extrabold text-hadir">{{ $result['created'] }}</dd>
                </div>
                <div class="p-3">
                    <dt class="text-[13px] text-redup">Diperbarui</dt>
                    <dd class="text-2xl font-extrabold text-dongker">{{ $result['updated'] }}</dd>
                </div>
                <div class="p-3">
                    <dt class="text-[13px] text-redup">Gagal</dt>
                    <dd @class(['text-2xl font-extrabold', 'text-alpa' => count($result['failures']) > 0, 'text-redup' => count($result['failures']) === 0])>{{ count($result['failures']) }}</dd>
                </div>
            </dl>

            @if (count($result['failures']) > 0)
                <p class="mt-4 text-sm text-redup">Baris berikut tidak disimpan. Perbaiki di file Excel, lalu unggah ulang file yang sama; baris yang sudah berhasil hanya akan diperbarui.</p>
                <div class="mt-3 max-h-80 overflow-y-auto rounded-lg border border-garis">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="w-24">Baris</th>
                                <th>Masalah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($result['failures'] as $failure)
                                <tr>
                                    <td class="font-semibold">{{ $failure['row'] }}</td>
                                    <td class="text-alpa">{{ $failure['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif ($result['created'] + $result['updated'] === 0)
                <p class="mt-4 text-sm text-redup">File tidak berisi data. Pastikan data diisi mulai baris ke-2 di sheet pertama.</p>
            @else
                <p class="mt-4 text-sm text-hadir">Semua baris berhasil disimpan.</p>
            @endif
        </x-admin.panel>
    @endif

    <div class="grid gap-5 lg:grid-cols-5">
        <div class="space-y-5 lg:col-span-3">
            <x-admin.panel title="1. Unduh template" description="Kolomnya sudah urut dan diformat sebagai teks, jadi NIS dan NUPTK tidak berubah jadi angka ilmiah di Excel.">
                <a href="{{ route('admin.import.template', $type) }}" class="btn btn-secondary">
                    <x-icon name="download" class="size-4" />
                    template-impor-{{ $type }}.xlsx
                </a>
            </x-admin.panel>

            <x-admin.panel title="2. Isi data di Excel">
                <ul class="list-disc space-y-1 pl-5 text-sm text-redup marker:text-garis">
                    <li>Isi mulai baris ke-2 di sheet pertama. Baris contoh boleh ditimpa atau dihapus.</li>
                    <li>Jangan menambah, menghapus, atau menukar urutan kolom.</li>
                    @if ($type === 'siswa')
                        <li>Tanggal lahir boleh ditulis 2011-05-12 atau 12/05/2011.</li>
                        <li>Kakak-adik dengan email wali yang sama otomatis tertaut ke satu akun orang tua.</li>
                    @else
                        <li>NUPTK hanya berisi angka. Nomor HP boleh dikosongkan.</li>
                    @endif
                </ul>
            </x-admin.panel>

            <x-admin.panel title="3. Unggah file">
                <form action="{{ route('admin.import.store', $type) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label for="file"
                           class="flex cursor-pointer items-center gap-3 rounded-lg border-2 border-dashed border-garis px-4 py-6 transition-colors hover:border-dongker/40 hover:bg-dongker-50/50 has-focus-visible:border-dongker">
                        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-dongker-50 text-dongker">
                            <x-icon name="file" />
                        </span>
                        <span class="min-w-0">
                            <span data-file-name="Pilih file .xlsx atau .csv" class="block truncate font-semibold">Pilih file .xlsx atau .csv</span>
                            <span class="block text-[13px] text-redup">Maksimal 5 MB. CSV dengan koma atau titik koma juga bisa.</span>
                        </span>
                        <input id="file" type="file" name="file" accept=".xlsx,.csv" required class="sr-only" data-file-input>
                    </label>

                    @error('file')
                        <p class="mt-2 text-[13px] text-alpa" role="alert">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn btn-primary mt-4">
                        <x-icon name="upload" class="size-4" />
                        Impor {{ $import->subject() }}
                    </button>
                </form>
            </x-admin.panel>
        </div>

        <x-admin.panel class="lg:col-span-2 self-start" title="Kolom di template" description="Dibaca berdasarkan urutan, bukan judul kolom." flush>
            <ol class="divide-y divide-garis border-t border-garis">
                @foreach ($import->columns() as $column)
                    <li class="flex gap-3 px-5 py-3 text-sm">
                        <span class="w-4 shrink-0 font-semibold text-redup">{{ chr(64 + $loop->iteration) }}</span>
                        <div class="min-w-0">
                            <p class="font-semibold">{{ $column->label }}
                                @if (in_array('required', $column->rules, true))
                                    <span class="font-normal text-redup">(wajib)</span>
                                @endif
                            </p>
                            <p class="text-redup">{{ $column->note }}</p>
                            <p class="mt-0.5 truncate text-[13px] text-redup">Contoh: <span class="text-teks">{{ $column->example }}</span></p>
                        </div>
                    </li>
                @endforeach
            </ol>
            <p class="border-t border-garis px-5 py-3 text-[13px] text-redup">
                Akun baru mendapat kata sandi awal <strong class="font-semibold text-teks">password123</strong>. Kata sandi akun lama tidak diubah.
            </p>
        </x-admin.panel>
    </div>
@endsection
