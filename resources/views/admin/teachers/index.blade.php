@extends('layouts.admin')

@section('title', 'Guru')

@section('content')
    <x-admin.page-header title="Guru" :description="$teachers->total() . ($search !== '' ? ' guru cocok dengan pencarian.' : ' guru terdaftar. Guru masuk ke aplikasi dengan NUPTK untuk mengisi presensi dan nilai.')">
        <x-slot:actions>
            <a href="{{ route('admin.import.create', 'guru') }}" class="btn btn-secondary">
                <x-icon name="upload" class="size-4" />
                Impor Excel
            </a>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
                <x-icon name="plus" class="size-4" />
                Tambah guru
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="panel overflow-hidden">
        <form method="GET" class="flex gap-2 border-b border-garis p-3" role="search">
            <div class="relative flex-1">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-redup" />
                <label for="q" class="sr-only">Cari guru</label>
                <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Cari nama, NUPTK, atau email" class="field pl-9">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
            @if ($search !== '')
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NUPTK</th>
                        <th>Kontak</th>
                        <th>Jadwal</th>
                        <th>Akun</th>
                        <th><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teachers as $teacher)
                        <tr>
                            <td class="font-semibold">{{ $teacher->full_name }}</td>
                            <td class="text-redup">{{ $teacher->nuptk }}</td>
                            <td>
                                <p>{{ $teacher->user->email ?? '-' }}</p>
                                <p class="text-[13px] text-redup">{{ $teacher->phone_number ?? 'Tanpa nomor HP' }}</p>
                            </td>
                            <td class="whitespace-nowrap text-redup">{{ $teacher->schedules_count }} jam/minggu</td>
                            <td><x-admin.status :status="$teacher->user?->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                            <td class="text-right">
                                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="font-semibold text-dongker hover:underline">Ubah</a>
                            </td>
                        </tr>
                    @empty
                        @if ($search !== '')
                            <x-admin.empty-row colspan="6" title="Tidak ada guru yang cocok">
                                Coba kata kunci lain.
                            </x-admin.empty-row>
                        @else
                            <x-admin.empty-row colspan="6" title="Belum ada guru">
                                Tambahkan satu per satu, atau impor sekaligus dari file Excel.
                            </x-admin.empty-row>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $teachers->links('admin.partials.pagination') }}
@endsection
