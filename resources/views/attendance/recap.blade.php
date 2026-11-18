@extends('layouts.app')

@section('title', 'Rekapitulasi Kehadiran Siswa')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Rekapitulasi Kehadiran Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Analisis tingkat kehadiran, keterlambatan, dan ketidakhadiran per rombel.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('attendance.recap.print', ['start_date' => $startDate, 'end_date' => $endDate, 'class_id' => $classId]) }}" target="_blank"
               class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Rekap Resmi</span>
            </a>
            <a href="{{ route('attendance.recap.export', ['start_date' => $startDate, 'end_date' => $endDate, 'class_id' => $classId]) }}" 
               class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200/60 font-medium text-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Ekspor CSV</span>
            </a>
            <a href="{{ route('attendance.index') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition">
                &larr; Jurnal Harian
            </a>
            <a href="{{ route('attendance.sheet') }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs shadow-xs transition">
                + Lembar Absensi
            </a>
        </div>
    </div>

    <!-- Filter Periode & Rombel -->
    <div class="p-3.5 sm:p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('attendance.recap') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Rombel / Kelas</label>
                <select name="class_id" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                    <option value="">Semua Rombel</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-1.5 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition">
                    Tampilkan Rekap
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Rekapitulasi Rombel -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">
                Rekapitulasi Rombel: {{ Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-xs text-slate-600">
                <thead class="bg-slate-100/70 text-slate-700 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">Rombel / Kelas</th>
                        <th class="py-2.5 px-3">Wali Kelas</th>
                        <th class="py-2.5 px-3 text-center">Siswa</th>
                        <th class="py-2.5 px-3 text-center text-emerald-800">Hadir</th>
                        <th class="py-2.5 px-3 text-center text-amber-800">Terlambat</th>
                        <th class="py-2.5 px-3 text-center text-blue-800">Sakit</th>
                        <th class="py-2.5 px-3 text-center text-purple-800">Izin</th>
                        <th class="py-2.5 px-3 text-center text-rose-800">Alpa</th>
                        <th class="py-2.5 px-3 text-center">Tingkat Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($classRecaps as $row)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 px-3 font-semibold text-slate-900">
                            {{ $row['class']->name }}
                        </td>
                        <td class="py-2.5 px-3 text-slate-600">
                            {{ $row['class']->homeroomTeacher?->formatted_name ?? '-' }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono font-medium">
                            {{ $row['total_students'] }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono font-semibold text-emerald-700">
                            {{ $row['present'] }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono font-medium text-amber-700">
                            {{ $row['late'] }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono font-medium text-blue-700">
                            {{ $row['sick'] }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono font-medium text-purple-700">
                            {{ $row['excused'] }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono font-medium text-rose-700">
                            {{ $row['absent'] }}
                        </td>
                        <td class="py-2.5 px-3 text-center">
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
                        <td colspan="9" class="py-6 text-center text-slate-400">
                            Belum ada rekaman presensi pada rentang tanggal terpilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabel Rincian Per Siswa Jika Memilih Rombel -->
    @if($selectedClass && !empty($studentRecaps))
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">
                    Rincian Kehadiran Siswa: Kelas {{ $selectedClass->name }}
                </h3>
                <p class="text-[11px] text-slate-500">Wali Kelas: {{ $selectedClass->homeroomTeacher?->full_name ?? '-' }} &bull; Total Siswa: {{ count($studentRecaps) }}</p>
            </div>
            <a href="{{ route('attendance.recap.print', ['start_date' => $startDate, 'end_date' => $endDate, 'class_id' => $classId]) }}" target="_blank"
               class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition flex items-center gap-1 shadow-2xs">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Kelas Ini</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[750px] text-left text-xs text-slate-600">
                <thead class="bg-slate-100/70 text-slate-700 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-3">Nama Siswa & NIS</th>
                        <th class="py-2.5 px-3 text-center">L/P</th>
                        <th class="py-2.5 px-3 text-center text-emerald-800">Hadir</th>
                        <th class="py-2.5 px-3 text-center text-amber-800">Terlambat</th>
                        <th class="py-2.5 px-3 text-center text-blue-800">Sakit</th>
                        <th class="py-2.5 px-3 text-center text-purple-800">Izin</th>
                        <th class="py-2.5 px-3 text-center text-rose-800">Alpa</th>
                        <th class="py-2.5 px-3 text-center">Total</th>
                        <th class="py-2.5 px-3 text-center">Tingkat Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($studentRecaps as $idx => $st)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3">
                            <div class="font-semibold text-slate-900">{{ $st['student']->full_name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">NIS: {{ $st['student']->nis }}</div>
                        </td>
                        <td class="py-2.5 px-3 text-center">{{ $st['student']->gender ?? '-' }}</td>
                        <td class="py-2.5 px-3 text-center font-mono font-semibold text-emerald-700">{{ $st['present'] }}</td>
                        <td class="py-2.5 px-3 text-center font-mono font-medium text-amber-700">{{ $st['late'] }}</td>
                        <td class="py-2.5 px-3 text-center font-mono font-medium text-blue-700">{{ $st['sick'] }}</td>
                        <td class="py-2.5 px-3 text-center font-mono font-medium text-purple-700">{{ $st['excused'] }}</td>
                        <td class="py-2.5 px-3 text-center font-mono font-semibold {{ $st['absent'] > 0 ? 'text-rose-700' : 'text-slate-400' }}">{{ $st['absent'] }}</td>
                        <td class="py-2.5 px-3 text-center font-mono">{{ $st['total'] }}</td>
                        <td class="py-2.5 px-3 text-center">
                            <span class="font-bold font-mono text-xs {{ $st['rate'] >= 85 ? 'text-emerald-700' : ($st['rate'] >= 70 ? 'text-amber-700' : 'text-rose-700') }}">
                                {{ $st['rate'] }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
