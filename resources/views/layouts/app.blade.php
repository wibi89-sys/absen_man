<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ $schoolSetting->school_name ?? config('app.name', 'MADRASAH ONE') }}</title>

    <!-- Google Font Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full text-slate-800 antialiased font-sans" x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">
    <div class="min-h-full flex flex-col md:flex-row">
        
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/50 z-40 md:hidden backdrop-blur-xs" 
             @click="sidebarOpen = false"></div>

        <!-- Sidebar Navigation (Modern Clean Enterprise) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-60 max-w-[80vw] bg-slate-900 text-slate-300 transition-transform duration-200 ease-in-out md:translate-x-0 md:static md:inset-auto flex flex-col shadow-xl md:shadow-none h-full md:min-h-screen border-r border-slate-800/80">
            
            <!-- Brand Header -->
            <div class="h-14 flex items-center justify-between px-4 bg-slate-950/80 border-b border-slate-800/80">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 min-w-0">
                    @if($schoolSetting->logo_url)
                        <img src="{{ $schoolSetting->logo_url }}" alt="Logo" class="w-8 h-8 object-contain rounded-lg bg-white p-0.5 shadow-xs shrink-0">
                    @else
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center font-bold text-white shadow-xs text-xs shrink-0">
                            {{ $schoolSetting->initials }}
                        </div>
                    @endif
                    <div class="min-w-0 truncate">
                        <span class="font-bold text-xs text-white block leading-tight truncate tracking-tight">{{ $schoolSetting->school_name }}</span>
                        <span class="text-[10px] text-emerald-400 font-medium block truncate">{{ $schoolSetting->school_slogan ?? 'Student Monitoring' }}</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1 rounded-md transition" aria-label="Tutup Menu">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Menu Utama</div>

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-2.5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek', 'waka']))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Master Data</div>
                    
                    <a href="{{ route('master.students.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('master.students.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Data Siswa
                    </a>

                    <a href="{{ route('master.teachers.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('master.teachers.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Data Guru
                    </a>

                    <a href="{{ route('master.classes.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('master.classes.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Rombel / Kelas
                    </a>

                    <a href="{{ route('master.academic-years.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('master.academic-years.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Tahun Ajaran
                    </a>

                    <a href="{{ route('master.locations.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('master.locations.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Lokasi Madrasah
                    </a>

                    <a href="{{ route('master.parents.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('master.parents.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Verifikasi Orang Tua
                    </a>

                    <a href="{{ route('master.machines.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('master.machines.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        Mesin Absensi LAN
                    </a>

                    @if(auth()->user()?->hasAnyRole(['super_admin', 'admin']))
                    <a href="{{ route('master.users.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('master.users.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Manajemen Pengguna
                    </a>
                    @endif
                </div>
                @endif

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek', 'waka', 'guru_piket', 'wali_kelas']))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Presensi Sekolah</div>
                    
                    <a href="{{ route('attendance.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('attendance.index') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Jurnal Presensi
                    </a>

                    <a href="{{ route('attendance.sheet') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('attendance.sheet') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Lembar Absensi Rombel
                    </a>

                    <a href="{{ route('attendance.recap') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('attendance.recap*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Rekapitulasi Kehadiran
                    </a>

                    <a href="{{ route('attendance.gate-scanner') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('attendance.gate-scanner') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Pos Gerbang Kiosk
                    </a>
                </div>
                @endif

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek', 'waka', 'petugas_keagamaan', 'wali_kelas', 'guru']))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Monitoring Ibadah</div>
                    
                    <a href="{{ route('worship.sessions.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('worship.sessions.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Sesi Salat Hari Ini
                    </a>

                    <a href="{{ route('worship.recap.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('worship.recap.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Rekapitulasi Ibadah
                    </a>

                    @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'petugas_keagamaan', 'guru_bk']))
                    <a href="{{ route('worship.exemptions.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('worship.exemptions.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Dispensasi Syar'i
                    </a>
                    @endif
                </div>
                @endif

                @if(auth()->user()?->hasRole('orang_tua'))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Portal Wali Murid</div>
                    <a href="{{ route('parent.announcements') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('parent.announcements') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        Pengumuman
                    </a>
                    <a href="{{ route('parent.messages') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('parent.messages') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        Pesan Wali Kelas
                    </a>
                </div>
                @endif

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek', 'waka', 'wali_kelas']))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Komunikasi</div>
                    
                    <a href="{{ route('communication.announcements.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('communication.announcements.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        Pengumuman
                    </a>
                </div>
                @endif

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek', 'waka', 'guru', 'wali_kelas']))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Akademik</div>
                    
                    <a href="{{ route('academic.subjects.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('academic.subjects.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Mata Pelajaran
                    </a>

                    <a href="{{ route('academic.grades.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('academic.grades.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Penilaian Siswa
                    </a>
                </div>
                @endif

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek', 'waka', 'guru_piket', 'wali_kelas', 'guru_bk']))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Kedisiplinan</div>
                    
                    <a href="{{ route('discipline.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('discipline.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Buku Kedisiplinan
                    </a>
                </div>
                @endif

                @if(auth()->user()?->hasAnyRole(['guru_bk', 'super_admin']))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-rose-300 uppercase px-2.5 mb-1.5 tracking-wider flex items-center justify-between">
                        <span>Konseling BK</span>
                        <span class="text-[9px] bg-rose-950 px-1 py-0.5 rounded text-rose-300 border border-rose-800">Rahasia</span>
                    </div>
                    
                    <a href="{{ route('counseling.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('counseling.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Layanan Konseling
                    </a>
                </div>
                @endif

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek', 'waka', 'wali_kelas', 'guru', 'petugas_keagamaan']))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Kegiatan Siswa</div>
                    
                    <a href="{{ route('activities.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('activities.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Ekstrakurikuler & OSIM
                    </a>
                </div>
                @endif

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek']))
                <div class="pt-3 pb-1">
                    <div class="text-[10px] font-semibold text-slate-400 uppercase px-2.5 mb-1.5 tracking-wider">Sistem & Lembaga</div>
                    
                    <a href="{{ route('settings.index') }}" 
                       class="flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('settings.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-2.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Pengaturan Madrasah
                    </a>
                </div>
                @endif
            </nav>

            <!-- User Footer in Sidebar -->
            <div class="p-3 bg-slate-950/80 border-t border-slate-800/80 flex items-center justify-between">
                <a href="{{ route('profile.show') }}" class="flex items-center space-x-2.5 overflow-hidden hover:opacity-85 transition group min-w-0" title="Profil & Keamanan">
                    <div class="w-7 h-7 rounded-lg bg-emerald-700 flex items-center justify-center font-bold text-[11px] text-white shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'User', 0, 2)) }}
                    </div>
                    <div class="truncate min-w-0">
                        <div class="text-xs font-semibold text-white truncate group-hover:text-emerald-300 transition">{{ auth()->user()?->name ?? 'Pengguna' }}</div>
                        <div class="text-[10px] text-slate-400 capitalize truncate">{{ auth()->user()?->roles?->first()?->display_name ?? 'Pengguna' }}</div>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0 ml-1">
                    @csrf
                    <button type="submit" title="Keluar" class="text-slate-400 hover:text-rose-400 p-1 rounded-md transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Top Header Bar (Modern Clean 56px) -->
            <header class="h-14 bg-white border-b border-slate-200/80 flex items-center justify-between px-3.5 sm:px-6 z-10 shrink-0">
                <div class="flex items-center space-x-2.5 min-w-0">
                    <button @click="sidebarOpen = true" class="md:hidden text-slate-600 hover:text-slate-900 p-1.5 rounded-lg hover:bg-slate-100 transition shrink-0" aria-label="Buka Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-base font-semibold text-slate-900 truncate tracking-tight">{{ $header ?? $title ?? 'Sistem' }}</h1>
                    </div>
                </div>

                <div class="flex items-center space-x-2 sm:space-x-3 shrink-0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                        {{ auth()->user()?->roles?->first()?->display_name ?? 'Pengguna' }}
                    </span>
                    <a href="{{ route('profile.show') }}" 
                       class="flex items-center space-x-1.5 px-2 py-1 rounded-lg text-xs font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition border border-slate-200"
                       title="Profil Pengguna">
                        <div class="w-5 h-5 rounded-md bg-emerald-700 text-white font-bold text-[9px] flex items-center justify-center">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
                        </div>
                        <span class="hidden sm:inline truncate max-w-[120px]">{{ auth()->user()?->name }}</span>
                    </a>
                </div>
            </header>

            <!-- Alerts / Flash Messages & Main Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7 pb-20 md:pb-6 bg-slate-50">
                <div class="max-w-7xl mx-auto space-y-4 sm:space-y-5">
                    @if(session('success'))
                    <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 flex items-start space-x-2.5 text-emerald-800 text-xs font-medium" x-data="{ show: true }" x-show="show">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="flex-1">{{ session('success') }}</div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 text-sm leading-none">&times;</button>
                    </div>
                    @endif

                    @if(session('status'))
                    <div class="p-3.5 rounded-lg bg-blue-50 border border-blue-200 flex items-start space-x-2.5 text-blue-800 text-xs font-medium" x-data="{ show: true }" x-show="show">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="flex-1">{{ session('status') }}</div>
                        <button @click="show = false" class="text-blue-500 hover:text-blue-700 text-sm leading-none">&times;</button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center justify-between mb-1 font-semibold">
                            <span>Mohon periksa data berikut:</span>
                            <button @click="show = false" class="text-rose-500 hover:text-rose-700 text-sm leading-none">&times;</button>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 font-normal">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>

            <!-- Mobile Bottom Navigation Bar (Modern Compact 56px) -->
            <nav class="md:hidden fixed bottom-0 inset-x-0 h-14 bg-white/95 backdrop-blur-xs border-t border-slate-200 z-30 px-3 flex items-center justify-around shadow-sm">
                <a href="{{ route('dashboard') }}" 
                   class="flex flex-col items-center justify-center py-1 px-3 rounded-lg text-center transition {{ request()->routeIs('dashboard') ? 'text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-[10px] mt-0.5">Beranda</span>
                </a>

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek', 'waka', 'guru_piket', 'wali_kelas']))
                <a href="{{ route('attendance.index') }}" 
                   class="flex flex-col items-center justify-center py-1 px-3 rounded-lg text-center transition {{ request()->routeIs('attendance.*') ? 'text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('attendance.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span class="text-[10px] mt-0.5">Presensi</span>
                </a>
                @endif

                @if(auth()->user()?->hasAnyRole(['super_admin', 'admin', 'kepsek', 'waka', 'petugas_keagamaan', 'wali_kelas', 'guru']))
                <a href="{{ route('worship.sessions.index') }}" 
                   class="flex flex-col items-center justify-center py-1 px-3 rounded-lg text-center transition {{ request()->routeIs('worship.*') ? 'text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('worship.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[10px] mt-0.5">Ibadah</span>
                </a>
                @endif

                @if(auth()->user()?->hasRole('orang_tua'))
                <a href="{{ route('parent.messages') }}" 
                   class="flex flex-col items-center justify-center py-1 px-3 rounded-lg text-center transition {{ request()->routeIs('parent.messages*') ? 'text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('parent.messages*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span class="text-[10px] mt-0.5">Pesan</span>
                </a>
                @endif

                <button type="button" @click="sidebarOpen = true" 
                        class="flex flex-col items-center justify-center py-1 px-3 rounded-lg text-center text-slate-500 hover:text-slate-800 transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span class="text-[10px] mt-0.5">Menu</span>
                </button>
            </nav>
        </div>
    </div>

    @livewireScripts
</body>
</html>
