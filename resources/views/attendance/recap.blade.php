@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Rekapitulasi Kehadiran Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Analisis tingkat kehadiran, keterlambatan, dan ketidakhadiran per rombel.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('attendance.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Jurnal Harian
            </a>
            <a href="{{ route('attendance.sheet') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
                + Lembar Absensi
            </a>
        </div>
    </div>

    <!-- Filter Periode & Rombel -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('attendance.recap') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Rombel / Kelas</label>
                <select name="class_id" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Rombel</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                    Tampilkan Rekap
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Rekapitulasi Rombel -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">
                Rekapitulasi Periode: {{ Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-xs text-slate-600">
                <thead class="bg-slate-100/70 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Rombel / Kelas</th>
                        <th class="py-3 px-4">Wali Kelas</th>
                        <th class="py-3 px-4 text-center">Siswa</th>
                        <th class="py-3 px-4 text-center text-emerald-800">Hadir</th>
                        <th class="py-3 px-4 text-center text-amber-800">Terlambat</th>
                        <th class="py-3 px-4 text-center text-blue-800">Sakit</th>
                        <th class="py-3 px-4 text-center text-purple-800">Izin</th>
                        <th class="py-3 px-4 text-center text-rose-800">Alpa</th>
                        <th class="py-3 px-4 text-center">Tingkat Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classRecaps as $row)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $row['class']->name }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $row['class']->homeroomTeacher?->formatted_name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-medium">
                            {{ $row['total_students'] }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-emerald-700">
                            {{ $row['present'] }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-semibold text-amber-700">
                            {{ $row['late'] }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-semibold text-blue-700">
                            {{ $row['sick'] }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-semibold text-purple-700">
                            {{ $row['excused'] }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-semibold text-rose-700">
                            {{ $row['absent'] }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center space-x-2">
                                <span class="font-bold font-mono text-xs {{ $row['rate'] >= 85 ? 'text-emerald-700' : ($row['rate'] >= 70 ? 'text-amber-700' : 'text-rose-700') }}">
                                    {{ $row['rate'] }}%
                                </span>
                                <div class="w-16 bg-slate-200 rounded-full h-1.5 overflow-hidden hidden sm:block">
                                    <div class="h-1.5 rounded-full {{ $row['rate'] >= 85 ? 'bg-emerald-600' : ($row['rate'] >= 70 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $row['rate'] }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">
                            Belum ada rekaman presensi pada rentang tanggal terpilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
