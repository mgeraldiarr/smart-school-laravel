@extends('layouts.admin')

@section('title', 'Pencarian')

@section('content')
    @if (mb_strlen($search) < 2)
        <x-admin.page-header title="Pencarian" description="Ketik minimal 2 huruf di kolom pencarian di atas untuk mencari siswa atau guru." />
    @else
        <x-admin.page-header
            :title="'Hasil untuk “' . $search . '”'"
            :description="$students->count() . ' siswa dan ' . $teachers->count() . ' guru ditemukan.'" />

        <div class="grid gap-5 lg:grid-cols-2">
            <x-admin.panel title="Siswa" flush>
                <x-slot:action>
                    <a href="{{ route('admin.students.index', ['q' => $search]) }}" class="font-semibold text-dongker hover:underline">Lihat di daftar siswa</a>
                </x-slot:action>
                <ul class="divide-y divide-garis border-t border-garis">
                    @forelse ($students as $student)
                        <li>
                            <a href="{{ route('admin.students.edit', $student) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-dongker-50/50">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold">{{ $student->full_name }}</span>
                                    <span class="block text-[13px] text-redup">NIS {{ $student->nis }}, kelas {{ $student->schoolClass->name ?? '-' }}</span>
                                </span>
                                <x-admin.status :status="$student->is_active ? 'Aktif' : 'Nonaktif'" />
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center text-sm text-redup">Tidak ada siswa dengan nama atau NIS itu.</li>
                    @endforelse
                </ul>
            </x-admin.panel>

            <x-admin.panel title="Guru" flush>
                <x-slot:action>
                    <a href="{{ route('admin.teachers.index', ['q' => $search]) }}" class="font-semibold text-dongker hover:underline">Lihat di daftar guru</a>
                </x-slot:action>
                <ul class="divide-y divide-garis border-t border-garis">
                    @forelse ($teachers as $teacher)
                        <li>
                            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-dongker-50/50">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold">{{ $teacher->full_name }}</span>
                                    <span class="block truncate text-[13px] text-redup">NUPTK {{ $teacher->nuptk }}, {{ $teacher->user->email ?? '-' }}</span>
                                </span>
                                <x-admin.status :status="$teacher->user?->is_active ? 'Aktif' : 'Nonaktif'" />
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center text-sm text-redup">Tidak ada guru dengan nama, NUPTK, atau email itu.</li>
                    @endforelse
                </ul>
            </x-admin.panel>
        </div>
    @endif
@endsection
