<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Masuk - {{ $schoolSetting->school_name ?? config('app.name', 'MADRASAH ONE') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex items-center justify-center p-3 sm:p-6 bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900 text-slate-100 selection:bg-emerald-500 selection:text-white">
    <div class="w-full max-w-md my-auto" x-data="{
        fill(username) {
            document.getElementById('login').value = username;
            document.getElementById('password').value = 'password123';
        }
    }">
        <!-- Brand Header (Official Identity) -->
        <div class="text-center mb-5 sm:mb-6">
            <div class="inline-flex items-center justify-center mb-3">
                @if($schoolSetting->logo_url)
                    <img src="{{ $schoolSetting->logo_url }}" alt="{{ $schoolSetting->school_name }}" 
                         class="w-16 h-16 sm:w-20 sm:h-20 object-contain rounded-2xl bg-white p-2 shadow-xl shadow-emerald-950/50 border border-emerald-500/20">
                @else
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-extrabold text-2xl sm:text-3xl shadow-xl shadow-emerald-950/60 flex items-center justify-center border border-emerald-400/30">
                        {{ $schoolSetting->initials }}
                    </div>
                @endif
            </div>
            
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white drop-shadow-sm leading-snug">
                {{ $schoolSetting->school_name }}
            </h1>
            <p class="text-xs text-emerald-300/90 font-medium tracking-wide mt-1 uppercase px-4">
                {{ $schoolSetting->school_slogan ?? 'Integrated Student Monitoring System' }}
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-md rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-2xl text-slate-800 border border-emerald-500/20">
            <div class="mb-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Portal Masuk</h2>
                    <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Silakan masuk dengan akun terdaftar Anda.</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $schoolSetting->school_level ?? 'Resmi' }}
                </span>
            </div>

            @if(session('status'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <div class="flex items-start gap-1.5">
                        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $err }}</span>
                    </div>
                @endforeach
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Identitas Pengguna
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                               placeholder="Username / NIS / NIP / Email"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs sm:text-sm transition bg-white shadow-xs">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="password" id="password" name="password" required
                               placeholder="Masukkan kata sandi..."
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs sm:text-sm transition bg-white shadow-xs">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label class="flex items-center space-x-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-slate-600 font-medium text-xs">Ingat Saya</span>
                    </label>
                    @if($schoolSetting->school_phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $schoolSetting->school_phone) }}" target="_blank" class="text-emerald-700 hover:text-emerald-800 text-[11px] font-medium hover:underline">
                        Bantuan Login
                    </a>
                    @endif
                </div>

                <button type="submit" 
                        class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-600/30 transition duration-150 transform active:scale-[0.99] flex items-center justify-center gap-2">
                    <span>Masuk ke Sistem</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            @if($schoolSetting->enable_demo_mode)
            <!-- Elegant Collapsible Demo Account Drawer -->
            <div class="mt-5 pt-4 border-t border-slate-200/80" x-data="{ showDemo: false }">
                <button type="button" @click="showDemo = !showDemo" 
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl bg-slate-100/80 hover:bg-emerald-50 text-slate-600 hover:text-emerald-800 text-xs font-semibold transition border border-slate-200/60">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Akun Uji Coba (Tester / Demo)</span>
                    </span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="showDemo ? 'rotate-180 text-emerald-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="showDemo" x-cloak class="mt-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="text-[10px] text-slate-500 flex items-center justify-between">
                        <span>Pilih akun peran untuk auto-fill:</span>
                        <span class="font-mono text-[10px] text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded font-bold">password123</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                        <button type="button" @click="fill('superadmin')" class="px-2 py-1 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Super Admin</button>
                        <button type="button" @click="fill('admin')" class="px-2 py-1 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Admin TU</button>
                        <button type="button" @click="fill('kepsek')" class="px-2 py-1 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Kepala Madrasah</button>
                        <button type="button" @click="fill('walikelas')" class="px-2 py-1 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Wali Kelas X-A</button>
                        <button type="button" @click="fill('keagamaan')" class="px-2 py-1 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Petugas Ibadah</button>
                        <button type="button" @click="fill('gurubk')" class="px-2 py-1 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Guru BK</button>
                        <button type="button" @click="fill('piket')" class="px-2 py-1 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Guru Piket</button>
                        <button type="button" @click="fill('siswa')" class="px-2 py-1 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Siswa (Ahmad)</button>
                        <button type="button" @click="fill('orangtua')" class="px-2 py-1 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-100 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Orang Tua</button>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="text-center mt-5 text-[11px] text-slate-400 px-4">
            &copy; {{ date('Y') }} {{ $schoolSetting->school_name }}. Seluruh hak cipta dilindungi.
        </div>
    </div>
</body>
</html>
