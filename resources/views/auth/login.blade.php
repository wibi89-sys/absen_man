<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - MADRASAH ONE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900 text-slate-100">
    <div class="w-full max-w-md" x-data="{
        fill(username) {
            document.getElementById('login').value = username;
            document.getElementById('password').value = 'password123';
        }
    }">
        <!-- Brand Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-500 text-white font-bold text-2xl shadow-lg shadow-emerald-500/30 mb-3">
                M1
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">MADRASAH ONE</h1>
            <p class="text-xs text-emerald-300 font-medium tracking-wide uppercase mt-1">Integrated Student Monitoring System</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-md rounded-2xl p-6 sm:p-8 shadow-2xl text-slate-800 border border-emerald-500/20">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-900">Selamat Datang</h2>
                <p class="text-xs text-slate-500 mt-0.5">Silakan masuk menggunakan Username, NIS, NIP, atau Email Anda.</p>
            </div>

            @if(session('status'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                {{ session('status') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Username / Email / NIS / NIP</label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                           placeholder="Contoh: admin, kepsek, siswa, walikelas"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kata Sandi</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Masukkan kata sandi..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-slate-600 font-medium">Ingat Saya</span>
                    </label>
                    <span class="text-slate-400">Kata sandi dummy: <strong class="text-slate-700">password123</strong></span>
                </div>

                <button type="submit" 
                        class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-600/30 transition duration-150">
                    Masuk ke Sistem
                </button>
            </form>

            <!-- Quick Dummy Account Fillers for Testing -->
            <div class="mt-6 pt-5 border-t border-slate-200">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Akun Pengujian Cepat [DUMMY]:</span>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" @click="fill('superadmin')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition">Super Admin</button>
                    <button type="button" @click="fill('admin')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition">Admin TU</button>
                    <button type="button" @click="fill('kepsek')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition">Kepala Madrasah</button>
                    <button type="button" @click="fill('walikelas')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition">Wali Kelas X-A</button>
                    <button type="button" @click="fill('keagamaan')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition">Petugas Keagamaan</button>
                    <button type="button" @click="fill('gurubk')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition">Guru BK</button>
                    <button type="button" @click="fill('piket')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition">Guru Piket</button>
                    <button type="button" @click="fill('siswa')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition">Siswa (Ahmad)</button>
                    <button type="button" @click="fill('orangtua')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition">Orang Tua (H. Rahmat)</button>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            &copy; 2026 MADRASAH ONE. Dirancang untuk efisiensi Shared Hosting cPanel.
        </div>
    </div>
</body>
</html>
