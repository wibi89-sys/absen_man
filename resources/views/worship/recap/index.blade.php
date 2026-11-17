@extends('layouts.app')

@section('title', 'Rekapitulasi Monitoring Ibadah')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Rekapitulasi Ibadah Terpadu</h1>
            <p class="text-sm text-slate-500 mt-1">Laporan kehadiran salat berjamaah berperiode, per rombel, dan pemantauan kepatuhan tanpa melanggar privasi siswi.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('worship.recap.export', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Ekspor CSV
            </a>
        </div>
    </div>

    <!-- Macro Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Sesi Terlaksana</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ $totalSessions }} <span class="text-sm font-normal text-slate-400">sesi</span></div>
            <span class="text-xs text-slate-400 mt-1 block">Dalam periode terpilih</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">Tingkat Kehadiran Rata-rata</span>
            <div class="text-2xl font-bold text-emerald-700 mt-2">{{ $overallRate }}%</div>
            <span class="text-xs text-emerald-600 mt-1 block">Proporsional (net dispensasi)</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider block">Total Dispensasi</span>
            <div class="text-2xl font-bold text-purple-700 mt-2">{{ number_format($overallStats->total_exempt ?? 0) }}</div>
            <span class="text-xs text-purple-500 mt-1 block">Kategori syar'i & medis</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider block">Total Tidak Hadir</span>
            <div class="text-2xl font-bold text-rose-700 mt-2">{{ number_format($overallStats->total_absent ?? 0) }}</div>
            <span class="text-xs text-rose-500 mt-1 block">Tanpa konfirmasi / izin</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('worship.recap.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Rombel / Kelas</label>
                <select name="class_id" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
                    <option value="">Semua Rombel</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Ibadah</label>
                <select name="worship_type_id" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
                    <option value="">Semua Ibadah (Zuhur & Asar)</option>
                    @foreach($worshipTypes as $type)
                        <option value="{{ $type->id }}" {{ $worshipTypeId == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                    Tampilkan
                </button>
                <a href="{{ route('worship.recap.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-3 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Rekapitulasi Kehadiran Siswa</h3>
            <span class="text-xs text-slate-500">{{ $students->total() }} siswa aktif</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Rombel</th>
                        <th class="py-3 px-4 text-center">Total Terjadwal</th>
                        <th class="py-3 px-4 text-center text-emerald-700">Hadir</th>
                        <th class="py-3 px-4 text-center text-orange-700">Masbuk</th>
                        <th class="py-3 px-4 text-center text-purple-700">Dispensasi</th>
                        <th class="py-3 px-4 text-center text-blue-700">Izin</th>
                        <th class="py-3 px-4 text-center text-rose-700">Tidak Hadir</th>
                        <th class="py-3 px-4 text-right">Persentase (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($students as $st)
                    @php
                        $stat = $attendanceStats->get($st->id);
                        $totalReg = $stat->total_registered ?? 0;
                        $present = $stat->count_present ?? 0;
                        $late = $stat->count_late ?? 0;
                        $exempt = $stat->count_exempt ?? 0;
                        $excused = $stat->count_excused ?? 0;
                        $absent = $stat->count_absent ?? 0;

                        // Calculate attendance rate (dispensation is not counted as unexcused failure)
                        $effectiveDenominator = max(1, $totalReg - $exempt);
                        $rate = $totalReg > 0 ? round((($present + $late) / $effectiveDenominator) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $st->full_name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">NIS: {{ $st->nis }}</div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700">
                            {{ $st->currentClass?->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">
                            {{ $totalReg }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/40">
                            {{ $present }}
                        </td>
                        <td class="py-3 px-4 text-center font-semibold text-orange-700">
                            {{ $late }}
                        </td>
                        <td class="py-3 px-4 text-center font-semibold text-purple-700 bg-purple-50/30">
                            {{ $exempt }}
                        </td>
                        <td class="py-3 px-4 text-center font-semibold text-blue-700">
                            {{ $excused }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-rose-700 bg-rose-50/30">
                            {{ $absent }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <span class="font-bold {{ $rate >= 80 ? 'text-emerald-700' : ($rate >= 60 ? 'text-amber-700' : 'text-rose-700') }}">
                                    {{ $rate }}%
                                </span>
                                <div class="w-16 bg-slate-200 rounded-full h-1.5 overflow-hidden hidden sm:block">
                                    <div class="h-1.5 rounded-full {{ $rate >= 80 ? 'bg-emerald-500' : ($rate >= 60 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min(100, $rate) }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">
                            Tidak ada siswa yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
