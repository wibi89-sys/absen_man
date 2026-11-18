@extends('layouts.app')

@section('title', 'Dashboard Bimbingan & Konseling (BK)')

@section('content')
<div class="space-y-5">
    <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 border-l-4 border-l-emerald-600 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-semibold text-emerald-700 tracking-wide uppercase">Bimbingan & Konseling (BK)</span>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">Assalamu'alaikum, {{ $user->name }}</h2>
            <p class="text-xs text-slate-500 mt-1">
                Layanan Konseling Siswa Terenkripsi & Proteksi Kerahasiaan Tingkat Tinggi.
            </p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('counseling.create') }}" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs">
                + Kasus Baru
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Card 1: Layanan Konseling -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Rekam Medis & Sesi Konseling</h3>
                            <p class="text-[11px] text-slate-500">Akses privat Guru BK & Kepala Madrasah</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200/60">Rahasia</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Seluruh catatan bimbingan, keluhan siswa, konseling orang tua, dan rencana tindak lanjut diisolasi secara ketat dan diaudit oleh sistem.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('counseling.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-medium transition">
                    Buka Buku Kasus &rarr;
                </a>
            </div>
        </div>

        <!-- Card 2: Buku Kedisiplinan -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Buku Kedisiplinan & Prestasi</h3>
                            <p class="text-[11px] text-slate-500">Poin pembinaan & penghargaan siswa</p>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pantau akumulasi poin kedisiplinan dan apresiasi prestasi siswa untuk memetakan penanganan konseling yang tepat sasaran.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('discipline.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium transition">
                    Lihat Kedisiplinan &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
