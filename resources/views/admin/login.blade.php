<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Smart School</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-16 h-16 bg-sky-600 rounded-2xl flex items-center justify-center mx-auto text-3xl shadow-lg shadow-sky-600/30">
                🎓
            </div>
            <h1 class="text-2xl font-black text-slate-900">Smart School</h1>
            <p class="text-xs text-slate-500 font-medium">Portal Web Administrator & Tata Usaha</p>
        </div>

        @if($errors->any())
        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('admin.login.process') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Administrator</label>
                <input type="email" name="email" value="admin@smartschool.sch.id" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:border-sky-500 focus:outline-none bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" value="admin123" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:border-sky-500 focus:outline-none bg-slate-50">
            </div>
            <button type="submit" class="w-full py-3.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-sky-600/30 transition">
                Masuk ke Dashboard TU
            </button>
        </form>

        <div class="text-center text-[11px] text-slate-400">
            SMP & SMA Smart School • Sistem Terintegrasi
        </div>
    </div>
</body>

</html>