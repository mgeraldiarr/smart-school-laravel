@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<div class="grid min-h-screen lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">

    <aside class="hidden flex-col justify-between bg-dongker p-10 text-white lg:flex">
        <div class="flex items-center gap-3">
            <x-crest class="size-10 [&_.fill-dongker]:fill-white/10" />
            <p class="text-lg font-extrabold">Smart School</p>
        </div>

        <div class="max-w-sm">
            <p class="text-[28px] leading-tight font-extrabold tracking-tight">
                Data siswa, guru, presensi, dan nilai dalam satu tempat.
            </p>
            <p class="mt-4 leading-relaxed text-white/70">
                Panel ini khusus untuk tata usaha. Guru dan orang tua memakai aplikasi seluler Smart School.
            </p>
        </div>

        <div class="h-1 w-16 rounded-full bg-emas" aria-hidden="true"></div>
    </aside>

    <main class="flex items-center justify-center bg-white px-5 py-12">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <x-crest />
                <p class="font-extrabold text-dongker">Smart School</p>
            </div>

            <h1 class="text-2xl font-extrabold tracking-tight text-dongker">Masuk ke panel admin</h1>
            <p class="mt-1.5 text-sm text-redup">Gunakan email dan kata sandi akun tata usaha.</p>

            @if ($errors->any())
                <div class="mt-6 flex items-start gap-2.5 rounded-lg border border-alpa/25 bg-alpa/5 px-3.5 py-3 text-sm text-alpa" role="alert">
                    <x-icon name="alert" class="mt-px size-4" />
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form action="{{ route('admin.login.store') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                {{-- Kesalahan login sudah ditampilkan di atas, jadi kolom tidak mengulang pesannya. --}}
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field py-2.5" @error('email') aria-invalid="true" @enderror>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold">Kata sandi</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="field py-2.5">
                </div>

                <button type="submit" class="btn btn-primary w-full py-2.5">Masuk</button>
            </form>

            @env('local')
                <p class="mt-8 rounded-lg bg-latar px-3.5 py-3 text-[13px] text-redup">
                    Lingkungan lokal. Akun dari seeder: <strong class="font-semibold text-teks">admin@smartschool.sch.id</strong> dengan kata sandi <strong class="font-semibold text-teks">admin123</strong>.
                </p>
            @endenv
        </div>
    </main>
</div>
@endsection
