<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ $schoolSetting->school_name ?? config('app.name', 'MADRASAH ONE') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ $schoolSetting->favicon_url }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ $schoolSetting->favicon_url }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased font-sans selection:bg-emerald-500 selection:text-white"
      x-data="{
          showPassword: false,
          showDemo: false,
          fill(username) {
              const loginInput = document.getElementById('login');
              const passInput = document.getElementById('password');
              if (loginInput) loginInput.value = username;
              if (passInput) passInput.value = 'password123';
          }
      }">

    <!-- HERO SECTION (SPLIT SCREEN ON DESKTOP, COMPACT & CENTERED ON MOBILE) -->
    <section class="relative bg-gradient-to-br from-emerald-950 via-teal-900 to-slate-950 text-white overflow-hidden flex-1 flex flex-col justify-between py-0">
        
        <!-- Ambient Atmospheric Light Circles (Desktop only to save mobile GPU/battery) -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden hidden sm:block">
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-emerald-500/15 blur-3xl"></div>
            <div class="absolute top-1/3 -right-24 w-96 h-96 rounded-full bg-teal-400/10 blur-3xl"></div>
            <div class="absolute -bottom-24 left-1/3 w-80 h-80 rounded-full bg-cyan-500/10 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:24px_24px] opacity-[0.04]"></div>
        </div>

        <!-- TOP HEADER NAVBAR (COMPACT & CLEAN) -->
        <header class="relative z-10 w-full border-b border-white/10 backdrop-blur-xs bg-slate-950/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-3.5 flex items-center justify-between">
                <!-- Brand Identity -->
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white p-1 shadow-sm flex items-center justify-center shrink-0 border border-white/20">
                        @if($schoolSetting->logo_url)
                            <img src="{{ $schoolSetting->logo_url }}" alt="{{ $schoolSetting->school_name }}" class="w-full h-full object-contain">
                        @else
                            <div class="w-full h-full rounded-lg bg-emerald-600 text-white font-bold text-xs sm:text-sm flex items-center justify-center">
                                {{ $schoolSetting->initials }}
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <span class="font-bold text-sm sm:text-base text-white tracking-tight truncate block leading-tight">
                            {{ $schoolSetting->school_name }}
                        </span>
                        <span class="text-[11px] text-emerald-200/80 truncate block">
                            {{ $schoolSetting->school_slogan ?? 'Integrated Student Monitoring System' }}
                        </span>
                    </div>
                </div>

                <!-- Top Right Help -->
                @if($schoolSetting->school_phone)
                <div class="flex items-center gap-2 shrink-0">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $schoolSetting->school_phone) }}" target="_blank"
                       class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white border border-white/20 font-medium text-xs transition flex items-center gap-1.5 backdrop-blur-sm shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Bantuan</span>
                    </a>
                </div>
                @endif
            </div>
        </header>

        <!-- MAIN CONTAINER -->
        <div class="relative z-10 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16 flex-1 flex flex-col justify-center">
            
            <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- LEFT COLUMN: INSTITUTIONAL BRANDING (DESKTOP ONLY: HIDDEN ON MOBILE < lg) -->
                <div class="hidden lg:block lg:col-span-7 text-left pr-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs text-emerald-200 font-medium mb-4">
                        <span>Presensi & Monitoring Terpadu</span>
                    </div>

                    <h1 class="text-3xl lg:text-4xl font-extrabold text-white tracking-tight leading-tight mb-3">
                        {{ $schoolSetting->school_name }}
                    </h1>

                    <p class="text-emerald-100/90 text-sm sm:text-base font-normal max-w-lg mb-8 leading-relaxed">
                        {{ $schoolSetting->school_slogan ?? 'Layanan absensi gerbang, monitoring ibadah, dan portal informasi madrasah.' }}
                    </p>

                    <!-- 3 Clean Feature Highlights -->
                    <div class="grid grid-cols-3 gap-3.5 max-w-xl">
                        <div class="p-4 rounded-xl bg-white/10 backdrop-blur-md border border-white/15">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            </div>
                            <h4 class="text-xs font-bold text-white mb-0.5">Presensi</h4>
                            <p class="text-[11px] text-slate-300 leading-snug">Pemindaian QR dinamis & RFID gerbang.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-white/10 backdrop-blur-md border border-white/15">
                            <div class="w-7 h-7 rounded-lg bg-teal-500/20 text-teal-300 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h4 class="text-xs font-bold text-white mb-0.5">Ibadah</h4>
                            <p class="text-[11px] text-slate-300 leading-snug">Monitoring salat bertahap di masjid.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-white/10 backdrop-blur-md border border-white/15">
                            <div class="w-7 h-7 rounded-lg bg-cyan-500/20 text-cyan-300 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <h4 class="text-xs font-bold text-white mb-0.5">Wali Murid</h4>
                            <p class="text-[11px] text-slate-300 leading-snug">Informasi kehadiran langsung ke orang tua.</p>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: THE AUTHENTICATION CARD -->
                <div class="lg:col-span-5 w-full max-w-md mx-auto">
                    <div class="bg-white rounded-2xl shadow-xl sm:shadow-2xl p-6 sm:p-8 border border-white/40 text-slate-800 relative transition-all">
                        
                        <!-- Header inside card -->
                        <div class="mb-5">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Masuk</h2>
                        </div>

                        <!-- Alerts -->
                        @if(session('status'))
                        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                            @foreach($errors->all() as $err)
                                <div class="flex items-start gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $err }}</span>
                                </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- FORM -->
                        <form action="{{ route('login') }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label for="login" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Username atau Email
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                                           placeholder="Username, NIS, NIP, atau Email"
                                           class="w-full pl-10 pr-3.5 py-2.5 sm:py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-xs sm:text-sm transition bg-white shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Kata Sandi
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </div>
                                    <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                                           placeholder="Kata sandi"
                                           class="w-full pl-10 pr-10 py-2.5 sm:py-3 rounded-xl border border-slate-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-xs sm:text-sm transition bg-white shadow-2xs">
                                    <button type="button" @click="showPassword = !showPassword" 
                                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition"
                                            tabindex="-1">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-0.5">
                                <label class="flex items-center space-x-2 cursor-pointer select-none">
                                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-slate-600 font-medium text-xs">Ingat saya</span>
                                </label>
                                @if($schoolSetting->school_phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $schoolSetting->school_phone) }}" target="_blank" class="text-emerald-700 hover:text-emerald-800 font-semibold hover:underline text-xs">
                                    Lupa sandi?
                                </a>
                                @endif
                            </div>

                            <button type="submit" 
                                    class="w-full py-2.5 sm:py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 transform active:scale-[0.99]">
                                <span>Masuk</span>
                            </button>
                        </form>

                        @if($schoolSetting->enable_demo_mode || request()->has('demo'))
                        <!-- Collapsible Demo Drawer (Tester Only) -->
                        <div class="mt-4 pt-3.5 border-t border-slate-100">
                            <button type="button" @click="showDemo = !showDemo" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition border border-slate-200">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Akun Uji Coba (Tester / Demo)</span>
                                </span>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="showDemo ? 'rotate-180 text-emerald-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="showDemo" x-cloak class="mt-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                                <div class="text-[10px] text-slate-500 flex items-center justify-between">
                                    <span>Pilih peran untuk auto-fill:</span>
                                    <span class="font-mono text-[10px] text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded-md font-semibold">password123</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                                    <button type="button" @click="fill('superadmin')" class="px-2 py-1.5 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Super Admin</button>
                                    <button type="button" @click="fill('admin')" class="px-2 py-1.5 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Admin TU</button>
                                    <button type="button" @click="fill('kepsek')" class="px-2 py-1.5 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Kepala Madrasah</button>
                                    <button type="button" @click="fill('walikelas')" class="px-2 py-1.5 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Wali Kelas X-A</button>
                                    <button type="button" @click="fill('keagamaan')" class="px-2 py-1.5 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Petugas Ibadah</button>
                                    <button type="button" @click="fill('gurubk')" class="px-2 py-1.5 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Guru BK</button>
                                    <button type="button" @click="fill('piket')" class="px-2 py-1.5 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Guru Piket</button>
                                    <button type="button" @click="fill('siswa')" class="px-2 py-1.5 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Siswa (Ahmad)</button>
                                    <button type="button" @click="fill('orangtua')" class="px-2 py-1.5 text-[11px] rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-900 font-medium text-slate-700 transition truncate shadow-2xs">Orang Tua</button>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-3.5 text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <p class="text-xs sm:text-sm text-slate-500 font-medium">&copy; {{ date('Y') }} Laksana Solution</p>
        </div>
    </footer>
</body>
</html>
