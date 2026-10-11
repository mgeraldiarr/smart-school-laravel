<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Smart School</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">

    <aside id="sidebar" class="sidebar fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-garis bg-white">
        <div class="flex h-16 items-center gap-3 px-5">
            <x-crest />
            <div class="leading-tight">
                <p class="font-extrabold text-dongker">Smart School</p>
                <p class="text-[13px] text-redup">Panel tata usaha</p>
            </div>
            <button type="button" class="ml-auto rounded-md p-1.5 text-redup hover:bg-latar lg:hidden" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Tutup menu">
                <x-icon name="close" />
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Menu utama">
            <x-admin.nav-link :href="route('admin.dashboard')" icon="home" :active="request()->routeIs('admin.dashboard')">Beranda</x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.students.index')" icon="students" :active="request()->routeIs('admin.students.*')">Siswa</x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.teachers.index')" icon="teacher" :active="request()->routeIs('admin.teachers.*')">Guru</x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.grades.index')" icon="grades" :active="request()->routeIs('admin.grades.*')">Nilai</x-admin.nav-link>
            <x-admin.nav-link :href="route('admin.import.create', 'siswa')" icon="upload" :active="request()->routeIs('admin.import.*')">Impor Excel</x-admin.nav-link>
        </nav>

        <div class="border-t border-garis p-3">
            <div class="flex items-center gap-3 rounded-lg px-2 py-2">
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-dongker-100 text-[13px] font-bold text-dongker">
                    {{ strtoupper(substr(auth()->user()->email, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[13px] font-semibold" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</p>
                    <p class="text-[12px] text-redup">Admin</p>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-md p-1.5 text-redup hover:bg-latar hover:text-alpa" title="Keluar" aria-label="Keluar">
                        <x-icon name="logout" class="size-4" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="sidebar-backdrop fixed inset-0 z-30 bg-teks/30 lg:hidden" data-sidebar-toggle aria-hidden="true"></div>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-garis bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
            <button type="button" class="-ml-1 rounded-md p-1.5 text-redup hover:bg-latar lg:hidden" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Buka menu">
                <x-icon name="menu" />
            </button>

            <form action="{{ route('admin.search') }}" method="GET" role="search" class="relative w-full max-w-md">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-redup" />
                <label for="global-search" class="sr-only">Cari siswa atau guru</label>
                <input id="global-search" type="search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}"
                       placeholder="Cari nama siswa, NIS, nama guru, atau NUPTK"
                       class="field border-transparent bg-latar py-2 pl-9 focus:bg-white">
            </form>

            <p class="ml-auto hidden text-sm text-redup md:block">{{ now()->locale('id')->translatedFormat('l, j F Y') }}</p>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-7xl">
                <x-admin.flash />
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
