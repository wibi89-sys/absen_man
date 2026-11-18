@extends('layouts.app')

@section('title', 'Portal Guru Mata Pelajaran')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 text-white shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs uppercase tracking-wider text-emerald-300 font-semibold mb-1">Portal Guru Mata Pelajaran</div>
            <h2 class="text-xl sm:text-2xl font-bold">Assalamu'alaikum, {{ $user->name }}</h2>
            <p class="text-emerald-100 text-xs sm:text-sm mt-1">
                Manajemen Nilai Asesmen, Mata Pelajaran, dan Catatan Apresiasi/Kedisiplinan Siswa.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('academic.grades.index') }}" class="px-4 py-2.5 rounded-xl bg-white text-emerald-900 hover:bg-emerald-50 text-xs font-bold transition shadow-xs">
                + Input Penilaian Siswa
            </a>
        </div>
    </div>

    <!-- Quick Operational Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Penilaian Siswa -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Penilaian Akademik</h3>
                    <p class="text-xs text-slate-500">Nilai tugas, harian, & ujian</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Entri nilai per rombel dan kontrol sakelar publikasi nilai ke portal orang tua secara fleksibel.
            </p>
            <div>
                <a href="{{ route('academic.grades.index') }}" class="inline-flex items-center text-xs font-bold text-emerald-600 hover:text-emerald-700">
                    Buka Lembar Penilaian &rarr;
                </a>
            </div>
        </div>

        <!-- Card 2: Mata Pelajaran & Penugasan -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Mata Pelajaran</h3>
                    <p class="text-xs text-slate-500">Penugasan pengampu kelas</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kelola daftar kurikulum mata pelajaran dan pemetaan guru pengampu pada setiap rombongan belajar.
            </p>
            <div>
                <a href="{{ route('academic.subjects.index') }}" class="inline-flex items-center text-xs font-bold text-teal-600 hover:text-teal-700">
                    Lihat Mata Pelajaran &rarr;
                </a>
            </div>
        </div>

        <!-- Card 3: Catatan Kedisiplinan -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Kedisiplinan & Prestasi</h3>
                    <p class="text-xs text-slate-500">Lapor pelanggaran / prestasi</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Catat apresiasi prestasi atau laporan pelanggaran tata tertib siswa secara langsung ke buku kedisiplinan madrasah.
            </p>
            <div>
                <a href="{{ route('discipline.index') }}" class="inline-flex items-center text-xs font-bold text-amber-600 hover:text-amber-700">
                    Buku Kedisiplinan &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
