@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-700 text-white shadow-lg">
        <div class="text-xs uppercase tracking-wider text-emerald-200 font-semibold mb-1">Portal Wali Kelas</div>
        <h2 class="text-xl sm:text-2xl font-bold">Assalamu'alaikum, {{ $teacher?->formatted_name ?? $user->name }}</h2>
        <p class="text-emerald-100 text-xs sm:text-sm mt-1">
            Kelas Binaan: <strong class="text-white">{{ $myClass?->name ?? 'Belum ditugaskan' }}</strong> 
            (Kapasitas: {{ $myClass?->capacity ?? 0 }} siswa)
        </p>
    </div>

    @if($myClass)
    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Daftar Siswa Kelas {{ $myClass->name }}</h3>
                <p class="text-xs text-slate-500">Jumlah siswa terdaftar: {{ $myClass->students->count() }} siswa</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">NIS</th>
                        <th class="py-2.5 px-3">Nama Lengkap</th>
                        <th class="py-2.5 px-3">Jenis Kelamin</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($myClass->students as $student)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3 font-mono font-medium text-slate-900">{{ $student->nis }}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $student->full_name }}</td>
                        <td class="py-2.5 px-3">{{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
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
    <div class="p-8 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-center">
        <h3 class="font-bold text-base mb-1">Belum Ada Penugasan Rombel</h3>
        <p class="text-xs text-amber-700">Akun Anda belum ditautkan sebagai wali kelas pada rombel yang aktif. Silakan hubungi Administrator Madrasah.</p>
    </div>
    @endif
</div>
@endsection
