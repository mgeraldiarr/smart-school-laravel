@extends('layouts.admin')

@section('title', 'Siswa')

@php($filtered = $search !== '' || request('kelas') || request('status'))

@section('content')
    <x-admin.page-header title="Siswa" :description="number_format($students->total(), 0, ',', '.') . ($filtered ? ' siswa cocok dengan filter.' : ' siswa terdaftar. Orang tua masuk ke aplikasi dengan NIS anaknya.')">
        <x-slot:actions>
            <a href="{{ route('admin.import.create', 'siswa') }}" class="btn btn-secondary">
                <x-icon name="upload" class="size-4" />
                Impor Excel
            </a>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="size-4" />
                Tambah siswa
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="panel overflow-hidden">
        <form method="GET" class="flex flex-col gap-2 border-b border-garis p-3 sm:flex-row sm:items-center" role="search">
            <div class="relative flex-1">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-redup" />
                <label for="q" class="sr-only">Cari siswa</label>
                <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau NIS" class="field pl-9">
            </div>
            <label for="kelas" class="sr-only">Kelas</label>
            <select id="kelas" name="kelas" class="field sm:w-40">
                <option value="">Semua kelas</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected(request('kelas') == $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="field sm:w-40">
                <option value="">Semua status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">Terapkan</button>
                @if ($filtered)
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th>Tanggal lahir</th>
                        <th>Status</th>
                        <th><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td class="font-semibold">{{ $student->full_name }}</td>
                            <td class="text-redup">{{ $student->nis }}</td>
                            <td>{{ $student->schoolClass->name ?? '-' }}</td>
                            <td class="whitespace-nowrap text-redup">
                                {{ $student->birth_date ? \Illuminate\Support\Carbon::parse($student->birth_date)->locale('id')->translatedFormat('j F Y') : '-' }}
                            </td>
                            <td><x-admin.status :status="$student->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                            <td class="text-right">
                                <a href="{{ route('admin.students.edit', $student) }}" class="font-semibold text-dongker hover:underline">Ubah</a>
                            </td>
                        </tr>
                    @empty
                        @if ($filtered)
                            <x-admin.empty-row colspan="6" title="Tidak ada siswa yang cocok">
                                Coba kata kunci lain atau reset filter.
                            </x-admin.empty-row>
                        @else
                            <x-admin.empty-row colspan="6" title="Belum ada siswa">
                                Tambahkan satu per satu, atau impor sekaligus dari file Excel.
                            </x-admin.empty-row>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $students->links('admin.partials.pagination') }}
@endsection
