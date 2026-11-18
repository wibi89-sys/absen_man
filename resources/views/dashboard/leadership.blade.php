@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-700 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs uppercase tracking-wider text-emerald-200 font-semibold mb-1">Executive Monitoring Dashboard</div>
            <h2 class="text-xl sm:text-2xl font-bold">Assalamu'alaikum, {{ $user->name }}</h2>
            <p class="text-emerald-100 text-xs sm:text-sm mt-1">
                Tahun Ajaran Aktif: <strong class="text-white">{{ $stats['active_academic_year']?->name ?? 'Belum ditentukan' }}</strong>
                ({{ $stats['active_academic_year']?->semesters->where('is_active', true)->first()?->semester == '1' ? 'Semester Ganjil' : 'Semester Genap' }})
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-white/20 backdrop-blur-md text-xs font-semibold text-white border border-white/20">
                <span class="w-2 h-2 rounded-full bg-emerald-300 mr-2 animate-pulse"></span>
                Sistem Berjalan Stabil
            </span>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Siswa Aktif</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['total_students'] }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Terdaftar dalam rombel aktif</div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rombel / Kelas</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['total_classes'] }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Tingkat X, XI, XII</div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Guru & Tendik</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['total_teachers'] }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Tenaga pendidik aktif</div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Akun Pengguna</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['total_users'] }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Staf, Guru, Siswa & Ortu</div>
            </div>
        </div>
    </div>

    <!-- Integrations Overview Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Siswa Terbaru -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Data Siswa Terbaru</h3>
                <a href="{{ route('master.students.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua Siswa &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">NIS</th>
                            <th class="py-2.5 px-3">Nama Siswa</th>
                            <th class="py-2.5 px-3">Kelas</th>
                            <th class="py-2.5 px-3">JK</th>
                            <th class="py-2.5 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentStudents as $s)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-3 font-mono font-medium text-slate-900">{{ $s->nis }}</td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $s->full_name }}</td>
                            <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold text-[11px]">{{ $s->currentClass?->name ?? '-' }}</span></td>
                            <td class="py-2.5 px-3">{{ $s->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                    Aktif
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-400">Belum ada data siswa.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Pusat Operasional & Akses Cepat -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Pusat Akses Operasional</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Semua Modul Aktif</span>
            </div>
            <p class="text-xs text-slate-500 mb-4">Pintasan cepat untuk memantau presensi, ibadah, kesiswaan, dan pelaporan resmi madrasah.</p>

            <div class="space-y-2.5">
                <a href="{{ route('attendance.index') }}" 
                   class="p-3 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 flex items-center justify-between transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-900 group-hover:text-emerald-900">Jurnal Presensi & Kiosk Gerbang</div>
                            <div class="text-[10px] text-slate-500">Rekapitulasi kehadiran, scan kartu, dan alert WA ortu</div>
                        </div>
                    </div>
                    <span class="text-slate-400 group-hover:text-emerald-600 text-xs">&rarr;</span>
                </a>

                <a href="{{ route('worship.sessions.index') }}" 
                   class="p-3 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 flex items-center justify-between transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-900 group-hover:text-emerald-900">Monitoring Ibadah Terpadu</div>
                            <div class="text-[10px] text-slate-500">Salat Zuhur gelombang, Asar, dan QR proyektor</div>
                        </div>
                    </div>
                    <span class="text-slate-400 group-hover:text-emerald-600 text-xs">&rarr;</span>
                </a>

                <a href="{{ route('attendance.recap') }}" 
                   class="p-3 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 flex items-center justify-between transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-900 group-hover:text-emerald-900">Laporan Resmi & Cetak Kop</div>
                            <div class="text-[10px] text-slate-500">Cetak dokumen berkop resmi & ekspor data Excel/CSV</div>
                        </div>
                    </div>
                    <span class="text-slate-400 group-hover:text-emerald-600 text-xs">&rarr;</span>
                </a>

                <a href="{{ route('settings.index') }}" 
                   class="p-3 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 flex items-center justify-between transition group">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-900 group-hover:text-emerald-900">Pengaturan Identitas & Logo</div>
                            <div class="text-[10px] text-slate-500">Kelola identitas madrasah, kepala sekolah, dan NIP</div>
                        </div>
                    </div>
                    <span class="text-slate-400 group-hover:text-emerald-600 text-xs">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
