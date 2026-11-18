@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <!-- Header Card -->
    <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 border-l-4 border-l-emerald-600 shadow-xs">
        <span class="text-[11px] font-semibold text-emerald-700 tracking-wide uppercase">Portal Wali Kelas</span>
        <h2 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">Assalamu'alaikum, {{ $teacher?->formatted_name ?? $user->name }}</h2>
        <p class="text-xs text-slate-500 mt-1">
            Kelas Binaan: <strong class="text-slate-800 font-medium">{{ $myClass?->name ?? 'Belum ditugaskan' }}</strong> 
            &bull; Kapasitas: {{ $myClass?->capacity ?? 0 }} siswa
        </p>
    </div>

    @if($myClass)
    <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-3.5">
            <div>
                <h3 class="font-semibold text-slate-900 text-sm">Daftar Siswa Kelas {{ $myClass->name }}</h3>
                <p class="text-xs text-slate-500">Jumlah siswa terdaftar: {{ $myClass->students->count() }} siswa</p>
            </div>
            <a href="{{ route('attendance.sheet', ['class_room_id' => $myClass->id]) }}" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-medium transition border border-emerald-200/60">
                Buka Absensi Kelas &rarr;
            </a>
        </div>

        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2 px-3">NIS</th>
                        <th class="py-2 px-3">Nama Lengkap</th>
                        <th class="py-2 px-3">JK</th>
                        <th class="py-2 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($myClass->students as $student)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2 px-3 font-mono font-medium text-slate-900">{{ $student->nis }}</td>
                        <td class="py-2 px-3 font-medium text-slate-800">{{ $student->full_name }}</td>
                        <td class="py-2 px-3">{{ $student->gender == 'L' ? 'L' : 'P' }}</td>
                        <td class="py-2 px-3">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                Aktif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-400">Belum ada siswa di kelas ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="p-6 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-center">
        <h3 class="font-semibold text-sm mb-1">Belum Ada Penugasan Rombel</h3>
        <p class="text-xs text-amber-700 max-w-md mx-auto">Akun Anda belum ditautkan sebagai wali kelas pada rombel yang aktif. Silakan hubungi Administrator Madrasah.</p>
    </div>
    @endif
</div>
@endsection
