@extends('layouts.app')

@section('title', 'Dashboard Monitoring Ibadah')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 text-white shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs uppercase tracking-wider text-emerald-300 font-semibold mb-1">Koordinator & Petugas Keagamaan</div>
            <h2 class="text-xl sm:text-2xl font-bold">Assalamu'alaikum, {{ $user->name }}</h2>
            <p class="text-emerald-100 text-xs sm:text-sm mt-1">
                Monitoring Ibadah Terpadu: Salat Zuhur Bergelombang & Salat Asar Berjamaah.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('worship.sessions.create') }}" class="px-4 py-2.5 rounded-xl bg-white text-emerald-900 hover:bg-emerald-50 text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                <span>+ Buka Sesi Salat Baru</span>
            </a>
        </div>
    </div>

    <!-- Quick Operational Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Sesi Salat & QR Proyektor -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Sesi Salat & QR Dinamis</h3>
                    <p class="text-xs text-slate-500">Zuhur gelombang & Asar</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Buka sesi ibadah hari ini, tampilkan layar QR dinamis ke proyektor masjid, dan lakukan verifikasi kehadiran jamaah.
            </p>
            <div>
                <a href="{{ route('worship.sessions.index') }}" class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-700">
                    Buka Daftar Sesi &rarr;
                </a>
            </div>
        </div>

        <!-- Card 2: Dispensasi Syar'i -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Dispensasi Syar'i Siswi</h3>
                    <p class="text-xs text-slate-500">Kerahasiaan data terjamin</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kelola dispensasi halangan syar'i siswi atau alasan medis dengan jaminan privasi (tidak ditampilkan di rekap publik).
            </p>
            <div>
                <a href="{{ route('worship.exemptions.index') }}" class="inline-flex items-center text-xs font-bold text-purple-600 hover:text-purple-700">
                    Kelola Dispensasi &rarr;
                </a>
            </div>
        </div>

        <!-- Card 3: Rekapitulasi & Cetak Kop -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Rekap & Cetak Laporan</h3>
                    <p class="text-xs text-slate-500">Kepatuhan salat & ekspor</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Pantau statistik kepatuhan salat berjamaah per kelas, ekspor rekap ke CSV, dan cetak laporan resmi bertanda tangan.
            </p>
            <div>
                <a href="{{ route('worship.recap.index') }}" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-700">
                    Lihat Rekapitulasi &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
