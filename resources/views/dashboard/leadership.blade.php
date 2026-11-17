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

        <!-- Right: Status Roadmap Modul -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <h3 class="font-bold text-slate-900 text-sm sm:text-base mb-3">Status Modul Sistem</h3>
            <p class="text-xs text-slate-500 mb-4">Pengembangan aplikasi secara bertahap sesuai instruksi Master Spec Antigravity.</p>

            <div class="space-y-3">
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-xs text-emerald-900">Fase 1: Fondasi & Master Data</div>
                        <div class="text-[10px] text-emerald-700">Autentikasi, RBAC 11 Role, CRUD Master</div>
                    </div>
                    <span class="px-2 py-1 rounded-md bg-emerald-600 text-white font-bold text-[10px]">Aktif</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-800">Fase 2: Absensi Sekolah</div>
                        <div class="text-[10px] text-slate-500">Pencatatan Gerbang, Keterlambatan, Koreksi</div>
                    </div>
                    <span class="px-2 py-1 rounded-md bg-slate-200 text-slate-700 font-medium text-[10px]">Berikutnya</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-800">Fase 3: Monitoring Ibadah</div>
                        <div class="text-[10px] text-slate-500">Zuhur Gelombang, Asar, QR Dinamis</div>
                    </div>
                    <span class="px-2 py-1 rounded-md bg-slate-200 text-slate-700 font-medium text-[10px]">Terjadwal</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-xs text-slate-800">Fase 4: Portal Orang Tua</div>
                        <div class="text-[10px] text-slate-500">Verifikasi Relasi, Informasi Terverifikasi</div>
                    </div>
                    <span class="px-2 py-1 rounded-md bg-slate-200 text-slate-700 font-medium text-[10px]">Terjadwal</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
