@extends('layouts.app')

@section('title', 'Dashboard Bimbingan & Konseling (BK)')

@section('content')
<div class="space-y-6">
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 text-white shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs uppercase tracking-wider text-emerald-300 font-semibold mb-1">Bimbingan & Konseling (BK)</div>
            <h2 class="text-xl sm:text-2xl font-bold">Assalamu'alaikum, {{ $user->name }}</h2>
            <p class="text-emerald-100 text-xs sm:text-sm mt-1">
                Layanan Konseling Siswa Terenkripsi & Proteksi Kerahasiaan Tingkat Tinggi.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('counseling.create') }}" class="px-4 py-2 rounded-xl bg-white text-emerald-900 hover:bg-emerald-50 text-xs font-bold transition shadow-xs">
                + Kasus Baru
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1: Layanan Konseling -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Rekam Medis & Sesi Konseling</h3>
                        <p class="text-xs text-slate-500">Akses privat Guru BK & Kepala Madrasah</p>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Rahasia</span>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Seluruh catatan bimbingan, keluhan siswa, konseling orang tua, dan rencana tindak lanjut diisolasi secara ketat dan diaudit oleh sistem.
            </p>
            <div class="pt-2">
                <a href="{{ route('counseling.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                    Buka Buku Kasus Konseling &rarr;
                </a>
            </div>
        </div>

        <!-- Card 2: Buku Kedisiplinan -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Buku Kedisiplinan & Prestasi</h3>
                        <p class="text-xs text-slate-500">Poin pembinaan & penghargaan siswa</p>
                    </div>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Pantau akumulasi poin kedisiplinan dan apresiasi prestasi siswa untuk memetakan penanganan konseling yang tepat sasaran.
            </p>
            <div class="pt-2">
                <a href="{{ route('discipline.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition">
                    Lihat Catatan Kedisiplinan &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
