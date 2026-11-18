@extends('layouts.app')

@section('title', 'Dashboard Monitoring Ibadah')

@section('content')
<div class="space-y-5">
    <!-- Header Card -->
    <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 border-l-4 border-l-emerald-600 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-semibold text-emerald-700 tracking-wide uppercase">Koordinator & Petugas Keagamaan</span>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">Assalamu'alaikum, {{ $user->name }}</h2>
            <p class="text-xs text-slate-500 mt-1">
                Monitoring Ibadah Terpadu: Salat Zuhur Bergelombang & Salat Asar Berjamaah.
            </p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('worship.sessions.create') }}" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs flex items-center gap-1.5">
                <span>+ Buka Sesi Salat Baru</span>
            </a>
        </div>
    </div>

    <!-- Quick Operational Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: Sesi Salat & QR Proyektor -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Sesi Salat & QR Dinamis</h3>
                        <p class="text-[11px] text-slate-500">Zuhur gelombang & Asar</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Buka sesi ibadah hari ini, tampilkan layar QR dinamis ke proyektor masjid, dan lakukan verifikasi kehadiran jamaah.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('worship.sessions.index') }}" class="inline-flex items-center text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    Buka Daftar Sesi &rarr;
                </a>
            </div>
        </div>

        <!-- Card 2: Dispensasi Syar'i -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Dispensasi Syar'i Siswi</h3>
                        <p class="text-[11px] text-slate-500">Kerahasiaan data terjamin</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Kelola dispensasi halangan syar'i siswi atau alasan medis dengan jaminan privasi (tidak ditampilkan di rekap publik).
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('worship.exemptions.index') }}" class="inline-flex items-center text-xs font-semibold text-purple-600 hover:text-purple-700">
                    Kelola Dispensasi &rarr;
                </a>
            </div>
        </div>

        <!-- Card 3: Rekapitulasi & Cetak Kop -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Rekap & Cetak Laporan</h3>
                        <p class="text-[11px] text-slate-500">Kepatuhan salat & ekspor</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pantau statistik kepatuhan salat berjamaah per kelas, ekspor rekap ke CSV, dan cetak laporan resmi bertanda tangan.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('worship.recap.index') }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-700">
                    Lihat Rekapitulasi &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
