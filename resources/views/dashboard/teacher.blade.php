@extends('layouts.app')

@section('title', 'Portal Guru Mata Pelajaran')

@section('content')
<div class="space-y-5">
    <!-- Header Card -->
    <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 border-l-4 border-l-emerald-600 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-semibold text-emerald-700 tracking-wide uppercase">Portal Guru Mata Pelajaran</span>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">Assalamu'alaikum, {{ $user->name }}</h2>
            <p class="text-xs text-slate-500 mt-1">
                Manajemen Nilai Asesmen, Mata Pelajaran, dan Catatan Apresiasi/Kedisiplinan Siswa.
            </p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('academic.grades.index') }}" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs">
                + Input Penilaian Siswa
            </a>
        </div>
    </div>

    <!-- Quick Operational Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: Penilaian Siswa -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Penilaian Akademik</h3>
                        <p class="text-[11px] text-slate-500">Nilai tugas, harian, & ujian</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Entri nilai per rombel dan kontrol sakelar publikasi nilai ke portal orang tua secara fleksibel.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('academic.grades.index') }}" class="inline-flex items-center text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    Buka Lembar Penilaian &rarr;
                </a>
            </div>
        </div>

        <!-- Card 2: Mata Pelajaran & Penugasan -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Mata Pelajaran</h3>
                        <p class="text-[11px] text-slate-500">Penugasan pengampu kelas</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Kelola daftar kurikulum mata pelajaran dan pemetaan guru pengampu pada setiap rombongan belajar.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('academic.subjects.index') }}" class="inline-flex items-center text-xs font-semibold text-teal-600 hover:text-teal-700">
                    Lihat Mata Pelajaran &rarr;
                </a>
            </div>
        </div>

        <!-- Card 3: Catatan Kedisiplinan -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3 flex flex-col justify-between">
            <div class="space-y-2.5">
                <div class="flex items-center space-x-2.5 pb-2.5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Kedisiplinan & Prestasi</h3>
                        <p class="text-[11px] text-slate-500">Lapor pelanggaran / prestasi</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Catat apresiasi prestasi atau laporan pelanggaran tata tertib siswa secara langsung ke buku kedisiplinan madrasah.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('discipline.index') }}" class="inline-flex items-center text-xs font-semibold text-amber-600 hover:text-amber-700">
                    Buku Kedisiplinan &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
