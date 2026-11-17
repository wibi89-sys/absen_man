@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-700 text-white shadow-lg">
        <div class="text-xs uppercase tracking-wider text-emerald-200 font-semibold mb-1">Portal Guru Mata Pelajaran</div>
        <h2 class="text-xl sm:text-2xl font-bold">Assalamu'alaikum, {{ $user->name }}</h2>
        <p class="text-emerald-100 text-xs sm:text-sm mt-1">
            Jurnal Mengajar, Kehadiran Jam Pelajaran, dan Capaian Belajar Siswa.
        </p>
    </div>

    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <h3 class="font-bold text-slate-900 text-base mb-2">Aktivitas Mengajar</h3>
        <p class="text-xs text-slate-500 mb-4">Fitur pencatatan jurnal kelas dan monitoring akademik terintegrasi akan diaktifkan pada <strong>Fase 5</strong>.</p>
    </div>
</div>
@endsection
