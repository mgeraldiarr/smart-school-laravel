@extends('layouts.admin')

@php($editing = $student->exists)

@section('title', $editing ? 'Ubah siswa' : 'Tambah siswa')

@section('content')
    <x-admin.page-header
        :title="$editing ? $student->full_name : 'Tambah siswa'"
        :description="$editing ? 'NIS ' . $student->nis . ', kelas ' . ($student->schoolClass->name ?? '-') . '.' : 'Akun orang tua dibuat otomatis dari email wali.'"
        :back="['href' => route('admin.students.index'), 'label' => 'Daftar siswa']" />

    <form method="POST" action="{{ $editing ? route('admin.students.update', $student) : route('admin.students.store') }}" class="grid gap-5 lg:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.panel class="lg:col-span-2" title="Data siswa">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.field label="Nama lengkap" name="full_name" class="sm:col-span-2">
                    <input id="full_name" name="full_name" value="{{ old('full_name', $student->full_name) }}" required class="field" @error('full_name') aria-invalid="true" @enderror>
                </x-admin.field>

                <x-admin.field label="NIS" name="nis" hint="Dipakai orang tua untuk masuk ke aplikasi.">
                    <input id="nis" name="nis" value="{{ old('nis', $student->nis) }}" required inputmode="numeric" class="field" @error('nis') aria-invalid="true" @enderror>
                </x-admin.field>

                <x-admin.field label="Tanggal lahir" name="birth_date">
                    <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', $student->birth_date ? \Illuminate\Support\Carbon::parse($student->birth_date)->toDateString() : '') }}" required max="{{ today()->subDay()->toDateString() }}" class="field" @error('birth_date') aria-invalid="true" @enderror>
                </x-admin.field>

                <x-admin.field label="Kelas" name="class_name" hint="Pilih dari daftar, atau ketik nama kelas baru.">
                    <input id="class_name" name="class_name" list="class-options" value="{{ old('class_name', $student->schoolClass->name ?? '') }}" required placeholder="Contoh: 8-A" class="field" @error('class_name') aria-invalid="true" @enderror>
                    <datalist id="class-options">
                        @foreach ($classes as $className)
                            <option value="{{ $className }}"></option>
                        @endforeach
                    </datalist>
                </x-admin.field>

                <div class="flex items-end pb-2.5">
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-2.5 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $student->is_active)) class="size-4 rounded accent-dongker">
                        <span><span class="font-semibold">Siswa aktif</span> <span class="text-redup">(masih bersekolah)</span></span>
                    </label>
                </div>
            </div>
        </x-admin.panel>

        <x-admin.panel title="Orang tua / wali" :description="$editing ? 'Isi untuk menautkan wali baru. Wali yang sudah tertaut tetap ada.' : 'Wajib diisi agar orang tua bisa masuk ke aplikasi.'">
            @if ($editing && $student->parents->isNotEmpty())
                <ul class="mb-4 divide-y divide-garis rounded-lg border border-garis">
                    @foreach ($student->parents as $parent)
                        <li class="px-3 py-2.5 text-sm">
                            <p class="font-semibold">{{ $parent->full_name }}</p>
                            <p class="text-redup">{{ $parent->user->email ?? '-' }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="space-y-4">
                <x-admin.field label="Nama wali" name="parent_name">
                    <input id="parent_name" name="parent_name" value="{{ old('parent_name') }}" @required(! $editing) class="field" @error('parent_name') aria-invalid="true" @enderror>
                </x-admin.field>
                <x-admin.field label="Email wali" name="parent_email" hint="Untuk kode OTP saat lupa kata sandi.">
                    <input id="parent_email" type="email" name="parent_email" value="{{ old('parent_email') }}" @required(! $editing) class="field" @error('parent_email') aria-invalid="true" @enderror>
                </x-admin.field>
            </div>
        </x-admin.panel>

        <div class="flex flex-wrap items-center gap-2 lg:col-span-3">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Simpan perubahan' : 'Tambah siswa' }}</button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>

    @if ($editing)
        <section class="panel mt-8 flex flex-col gap-4 border-alpa/25 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-alpa">Hapus siswa</h2>
                <p class="mt-0.5 text-sm text-redup">Hanya bisa untuk siswa yang belum punya presensi atau nilai. Siswa yang pindah atau lulus cukup dinonaktifkan.</p>
            </div>
            <form method="POST" action="{{ route('admin.students.destroy', $student) }}" data-confirm="Hapus {{ $student->full_name }} secara permanen?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus siswa</button>
            </form>
        </section>
    @endif
@endsection
