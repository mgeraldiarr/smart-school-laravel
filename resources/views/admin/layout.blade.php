<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Smart School</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 flex min-h-screen text-slate-800 antialiased font-sans">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between hidden md:flex">
        <div>
            <!-- Logo Brand -->
            <div class="p-6 flex items-center gap-3 border-b border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-sky-600 flex items-center justify-center text-xl shadow">🎓</div>
                <div>
                    <h2 class="font-extrabold text-base leading-tight">Smart School</h2>
                    <span class="text-[10px] text-sky-400 uppercase tracking-widest font-bold">Portal Admin TU</span>
                </div>
            </div>

            <!-- Menu Links -->
            <nav class="p-4 space-y-1.5 text-xs font-bold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.dashboard') ? 'bg-sky-600 text-white' : 'text-slate-400' }}">
                    <span>📊</span> Dashboard
                </a>
                <a href="{{ route('admin.students') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.students') ? 'bg-sky-600 text-white' : 'text-slate-400' }}">
                    <span>🧑‍🎓</span> Data Siswa
                </a>
                <a href="{{ route('admin.teachers') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.teachers') ? 'bg-sky-600 text-white' : 'text-slate-400' }}">
                    <span>👨‍🏫</span> Data Guru
                </a>
                <a href="{{ route('admin.import') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.import') ? 'bg-sky-600 text-white' : 'text-slate-400' }}">
                    <span>📥</span> Import Data Excel
                </a>
            </nav>
        </div>

        <!-- Logout Bottom -->
        <div class="p-4 border-t border-slate-800">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white rounded-xl text-xs font-bold transition">
                    <span>🚪</span> Keluar Sistem
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between">
            <h1 class="text-lg font-extrabold text-slate-900">@yield('page_title', 'Dashboard')</h1>
            <div class="flex items-center gap-3 text-xs">
                <span class="text-slate-500">Masuk sebagai: <b>Administrator TU</b></span>
                <div class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold">A</div>
            </div>
        </header>

        <!-- Body Content -->
        <main class="p-8 flex-1">
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold">
                ✅ {{ session('success') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>

</html>