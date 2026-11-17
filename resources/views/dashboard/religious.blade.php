@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-700 text-white shadow-lg">
        <div class="text-xs uppercase tracking-wider text-emerald-200 font-semibold mb-1">Koordinator & Petugas Keagamaan</div>
        <h2 class="text-xl sm:text-2xl font-bold">Assalamu'alaikum, {{ $user->name }}</h2>
        <p class="text-emerald-100 text-xs sm:text-sm mt-1">
            Modul Monitoring Ibadah Terpadu (Salat Zuhur Gelombang & Salat Asar Berjamaah).
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 text-base mb-2">Salat Zuhur Bergelombang</h3>
            <p class="text-xs text-slate-500 mb-4">Pengaturan sesi ibadah bergelombang per rombel untuk menjaga kapasitas masjid sekolah.</p>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                Fitur generator QR dinamis dan kunci denominator peserta akan aktif pada <strong>Fase 3</strong>.
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 text-base mb-2">Dispensasi & Halangan Syar'i</h3>
            <p class="text-xs text-slate-500 mb-4">Pencatatan halangan ibadah siswi dengan perlindungan kerahasiaan tinggi.</p>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                Data sensitif dilindungi dengan otorisasi khusus dan tidak ditampilkan pada rekap umum.
            </div>
        </div>
    </div>
</div>
@endsection
