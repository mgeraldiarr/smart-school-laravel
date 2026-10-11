<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') &mdash; Smart School</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="h-full antialiased text-slate-800 flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 flex-shrink-0 flex-col justify-between border-r border-slate-800 select-none">
        <div>
            <!-- Brand -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800/80">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white tracking-tight leading-none">Smart School</div>
                        <div class="text-[11px] text-slate-400 font-medium mt-1 leading-none">Administration</div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="px-3 py-4 space-y-1">
                <div class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Menu Utama</div>

                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.students') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.students') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Data Siswa</span>
                </a>

                <a href="{{ route('admin.teachers') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.teachers') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Data Guru</span>
                </a>

                <a href="{{ route('admin.import') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('admin.import') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Import Data</span>
                </a>
            </div>
        </div>

        <!-- User Session & Logout -->
        <div class="p-3 border-t border-slate-800/80">
            <div class="px-3 py-2 rounded-lg bg-slate-800/40 mb-2">
                <div class="text-[11px] font-medium text-white truncate">{{ Auth::user()->email ?? 'admin@smartschool.sch.id' }}</div>
                <div class="text-[10px] text-slate-400 font-medium">Administrator</div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-slate-400 hover:text-rose-300 hover:bg-rose-950/20 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar Sesi</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>Portal Administrasi</span>
                <span class="text-slate-300">/</span>
                <span class="font-semibold text-slate-800">@yield('page_title', 'Dashboard')</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                    Sistem Aktif
                </span>
            </div>
        </header>

        <!-- Page View Body -->
        <main class="flex-1 p-8">
            @yield('content')
        </main>
    </div>

</body>
</html>