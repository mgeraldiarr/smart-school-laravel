<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk &mdash; Smart School Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 antialiased text-slate-800">

    <div class="w-full max-w-sm">
        
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-600 text-white shadow-sm mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Smart School</h1>
            <p class="text-xs text-slate-500 mt-1">Portal Manajemen Administrasi Sekolah</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.login.process') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Alamat Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="admin@smartschool.sch.id" 
                        required 
                        placeholder="admin@smartschool.sch.id"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Kata Sandi</label>
                    <input 
                        type="password" 
                        name="password" 
                        value="admin123" 
                        required 
                        placeholder="••••••••"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition active:scale-[0.99]"
                >
                    Masuk ke Sistem
                </button>
            </form>
        </div>

        <div class="text-center mt-6 text-[11px] text-slate-400">
            Kredensial bawaan: <span class="font-mono text-slate-600">admin@smartschool.sch.id</span> &bull; <span class="font-mono text-slate-600">admin123</span>
        </div>

    </div>

</body>
</html>