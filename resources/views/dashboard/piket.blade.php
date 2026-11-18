@extends('layouts.app')

@section('title', 'Dashboard Guru Piket')

@section('content')
<div class="space-y-5">
    <!-- Header Card -->
    <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 border-l-4 border-l-emerald-600 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-semibold text-emerald-700 tracking-wide uppercase">Guru Piket Madrasah</span>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">Assalamu'alaikum, {{ $user->name }}</h2>
            <p class="text-xs text-slate-500 mt-1">
                Pencatatan Gerbang Masuk Harian, Deteksi Keterlambatan, dan Koreksi Kehadiran Siswa.
            </p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('attendance.gate-scanner') }}" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Buka Pos Gerbang Kiosk</span>
            </a>
        </div>
    </div>

    <!-- Quick Operational Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: Pos Gerbang Kiosk -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Kiosk Scanner Gerbang</h3>
                        <p class="text-[11px] text-slate-500">Scan QR / Barcode / RFID</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Antarmuka pemindaian otomatis di gerbang masuk madrasah dengan deteksi status jam masuk secara real-time.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('attendance.gate-scanner') }}" class="inline-flex items-center text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    Buka Scanner Gerbang &rarr;
                </a>
            </div>
        </div>

        <!-- Card 2: Lembar Absensi Rombel -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Lembar Absensi Rombel</h3>
                        <p class="text-[11px] text-slate-500">Pencatatan serentak kelas</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Tandai kehadiran kelas binaan secara serentak dengan tombol cepat "Tandai Semua Hadir".
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('attendance.sheet') }}" class="inline-flex items-center text-xs font-semibold text-teal-600 hover:text-teal-700">
                    Buka Lembar Kelas &rarr;
                </a>
            </div>
        </div>

        <!-- Card 3: Jurnal & Koreksi -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Jurnal & Alert WA Ortu</h3>
                        <p class="text-[11px] text-slate-500">Koreksi & notifikasi instan</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pantau siswa terlambat hari ini, lakukan koreksi status izin/sakit, dan kirim pesan WA ke orang tua dengan 1-klik.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('attendance.index') }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700">
                    Lihat Jurnal Hari Ini &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
