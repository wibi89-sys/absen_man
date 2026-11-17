@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-700 text-white shadow-lg">
        <div class="text-xs uppercase tracking-wider text-emerald-200 font-semibold mb-1">Bimbingan & Konseling (BK)</div>
        <h2 class="text-xl sm:text-2xl font-bold">Assalamu'alaikum, {{ $user->name }}</h2>
        <p class="text-emerald-100 text-xs sm:text-sm mt-1">
            Modul Konseling Siswa dengan Proteksi Privasi & Enkripsi Data Kasus.
        </p>
    </div>

    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <div class="flex items-start space-x-3 text-emerald-900 mb-4">
            <svg class="w-6 h-6 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <div>
                <h3 class="font-bold text-sm sm:text-base">Kerahasiaan Catatan Konseling Terjamin</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Sesuai panduan keamanan MADRASAH ONE, seluruh catatan konseling dan tindak lanjut siswa diisolasi secara ketat dan tidak dapat diakses oleh guru umum maupun orang tua murid. Modul ini dijadwalkan pada <strong>Fase 5</strong>.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
