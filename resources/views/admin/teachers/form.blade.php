@extends('layouts.admin')

@php($editing = $teacher->exists)

@section('title', $editing ? 'Ubah guru' : 'Tambah guru')

@section('content')
    <x-admin.page-header
        :title="$editing ? $teacher->full_name : 'Tambah guru'"
        :description="$editing ? 'NUPTK ' . $teacher->nuptk . ', mengajar ' . $teacher->schedules_count . ' jam per minggu.' : 'Akun login guru dibuat otomatis dengan kata sandi awal password123.'"
        :back="['href' => route('admin.teachers.index'), 'label' => 'Daftar guru']" />

    <form method="POST" action="{{ $editing ? route('admin.teachers.update', $teacher) : route('admin.teachers.store') }}" class="grid gap-5 lg:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.panel class="lg:col-span-2" title="Data guru">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.field label="Nama lengkap dan gelar" name="full_name" class="sm:col-span-2">
                    <input id="full_name" name="full_name" value="{{ old('full_name', $teacher->full_name) }}" required placeholder="Contoh: Dewi Lestari, S.Pd" class="field" @error('full_name') aria-invalid="true" @enderror>
                </x-admin.field>

                <x-admin.field label="NUPTK" name="nuptk" hint="Dipakai guru untuk masuk ke aplikasi.">
                    <input id="nuptk" name="nuptk" value="{{ old('nuptk', $teacher->nuptk) }}" required inputmode="numeric" class="field" @error('nuptk') aria-invalid="true" @enderror>
                </x-admin.field>

                <x-admin.field label="Nomor HP" name="phone_number">
                    <input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number', $teacher->phone_number) }}" placeholder="08xxxxxxxxxx" class="field" @error('phone_number') aria-invalid="true" @enderror>
                </x-admin.field>

                <x-admin.field label="Email" name="email" hint="Untuk kode OTP saat lupa kata sandi." class="sm:col-span-2">
                    <input id="email" type="email" name="email" value="{{ old('email', $teacher->user->email ?? '') }}" required class="field" @error('email') aria-invalid="true" @enderror>
                </x-admin.field>
            </div>
        </x-admin.panel>

        <x-admin.panel title="Akun">
            <div class="space-y-4 text-sm">
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-start gap-2.5">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $teacher->user->is_active ?? true)) class="mt-0.5 size-4 rounded accent-dongker">
                    <span>
                        <span class="block font-semibold">Akun aktif</span>
                        <span class="text-redup">Matikan agar guru tidak bisa masuk ke aplikasi.</span>
                    </span>
                </label>

                @if ($editing)
                    <input type="hidden" name="reset_password" value="0">
                    <label class="flex items-start gap-2.5">
                        <input type="checkbox" name="reset_password" value="1" @checked(old('reset_password')) class="mt-0.5 size-4 rounded accent-dongker">
                        <span>
                            <span class="block font-semibold">Atur ulang kata sandi</span>
                            <span class="text-redup">Kata sandi kembali ke password123.</span>
                        </span>
                    </label>
                @endif
            </div>
        </x-admin.panel>

        <div class="flex flex-wrap items-center gap-2 lg:col-span-3">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Simpan perubahan' : 'Tambah guru' }}</button>
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>

    @if ($editing)
        <section class="panel mt-8 flex flex-col gap-4 border-alpa/25 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-alpa">Hapus guru</h2>
                <p class="mt-0.5 text-sm text-redup">Hanya bisa untuk guru tanpa jadwal, nilai, atau presensi. Guru yang pindah cukup dinonaktifkan akunnya.</p>
            </div>
            <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" data-confirm="Hapus {{ $teacher->full_name }} beserta akun login-nya?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus guru</button>
            </form>
        </section>
    @endif
@endsection
