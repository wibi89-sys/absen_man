@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-700 text-white shadow-lg">
        <div class="text-xs uppercase tracking-wider text-emerald-200 font-semibold mb-1">Guru Piket Madrasah</div>
        <h2 class="text-xl sm:text-2xl font-bold">Assalamu'alaikum, {{ $user->name }}</h2>
        <p class="text-emerald-100 text-xs sm:text-sm mt-1">
            Pencatatan Gerbang Masuk Harian, Deteksi Siswa Terlambat, dan Izin Keluar Sekolah.
        </p>
    </div>

    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <h3 class="font-bold text-slate-900 text-base mb-2">Pencatatan Absensi Harian Gerbang</h3>
        <p class="text-xs text-slate-500 mb-4">Fitur input cepat absensi gerbang masuk dan rekap keterlambatan harian akan diaktifkan penuh pada <strong>Fase 2</strong>.</p>
    </div>
</div>
@endsection
