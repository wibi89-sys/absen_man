@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Jurnal Presensi Harian Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pantau rekaman kehadiran gerbang masuk, keterlambatan, dan riwayat koreksi.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('attendance.recap') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Rekap Rombel
            </a>
            <a href="{{ route('attendance.sheet') }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Lembar Absensi Rombel
            </a>
        </div>
    </div>

    <!-- Quick Stat Cards Harian -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Total Presensi</span>
            <div class="text-xl font-bold text-slate-900 mt-1">{{ $stats['total_recorded'] }}</div>
        </div>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 shadow-xs">
            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Hadir Tepat</span>
            <div class="text-xl font-bold text-emerald-700 mt-1">{{ $stats['present'] }}</div>
        </div>
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 shadow-xs">
            <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Terlambat</span>
            <div class="text-xl font-bold text-amber-700 mt-1">{{ $stats['late'] }}</div>
        </div>
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 shadow-xs">
            <span class="text-[10px] font-bold text-blue-800 uppercase tracking-wider block">Sakit (S)</span>
            <div class="text-xl font-bold text-blue-700 mt-1">{{ $stats['sick'] }}</div>
        </div>
        <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 shadow-xs">
            <span class="text-[10px] font-bold text-purple-800 uppercase tracking-wider block">Izin (I)</span>
            <div class="text-xl font-bold text-purple-700 mt-1">{{ $stats['excused'] }}</div>
        </div>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 shadow-xs">
            <span class="text-[10px] font-bold text-rose-800 uppercase tracking-wider block">Alpa (A)</span>
            <div class="text-xl font-bold text-rose-700 mt-1">{{ $stats['absent'] }}</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" 
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
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Presensi</label>
                <select name="type_id" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Jenis</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" {{ $typeId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Kehadiran</label>
                <select name="status" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="present" {{ $status == 'present' ? 'selected' : '' }}>Hadir</option>
                    <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="sick" {{ $status == 'sick' ? 'selected' : '' }}>Sakit</option>
                    <option value="excused" {{ $status == 'excused' ? 'selected' : '' }}>Izin</option>
                    <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                    Terapkan
                </button>
                <a href="{{ route('attendance.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Hari Ini
                </a>
            </div>
        </form>
    </div>

    <!-- Table Presensi -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Siswa</th>
                        <th class="py-3 px-4">Rombel</th>
                        <th class="py-3 px-4">Sesi Presensi</th>
                        <th class="py-3 px-4">Jam Masuk</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Petugas / Sumber</th>
                        <th class="py-3 px-4 text-right">Koreksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                    <tr class="hover:bg-slate-50 transition" x-data="{ openCorrection: false }">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $att->student->full_name }}</div>
                            <div class="text-[10px] font-mono text-slate-400">NIS: {{ $att->student->nis }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                {{ $att->student->currentClass?->name ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            {{ $att->session->attendanceType?->name ?? 'Presensi' }}
                        </td>
                        <td class="py-3 px-4 font-mono">
                            {{ $att->check_in_time ? substr($att->check_in_time, 0, 5) : '-' }}
                            @if($att->minutes_late > 0)
                                <span class="text-[10px] text-amber-600 font-semibold block">+{{ $att->minutes_late }} mnt</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $att->status_badge }}">
                                {{ $att->status_label }}
                            </span>
                            @if($att->notes)
                                <div class="text-[10px] text-slate-400 italic mt-0.5">{{ $att->notes }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-[11px] text-slate-500">
                            <div>{{ $att->recorder?->name ?? 'Sistem' }}</div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider">{{ str_replace('_', ' ', $att->source) }}</div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <button @click="openCorrection = true" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                Koreksi
                            </button>

                            <!-- Modal Koreksi -->
                            <div x-show="openCorrection" class="fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4" style="display: none;">
                                <div @click.away="openCorrection = false" class="bg-white rounded-2xl p-6 max-w-sm w-full text-left shadow-xl space-y-4">
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-900">Koreksi Kehadiran Siswa</h4>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $att->student->full_name }} (NIS: {{ $att->student->nis }})</p>
                                    </div>
                                    <form action="{{ route('attendance.correct', $att) }}" method="POST" class="space-y-3" x-data="{ newStatus: '{{ $att->status }}' }">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status Baru *</label>
                                            <select name="new_status" x-model="newStatus" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                                <option value="present">Hadir</option>
                                                <option value="late">Terlambat</option>
                                                <option value="sick">Sakit (Surat Dokter/Ortu)</option>
                                                <option value="excused">Izin Sah</option>
                                                <option value="absent">Alpa (Tanpa Keterangan)</option>
                                                <option value="early_departure">Pulang Lebih Awal</option>
                                            </select>
                                        </div>

                                        <div x-show="newStatus === 'late'">
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Menit Terlambat</label>
                                            <input type="number" name="minutes_late" value="{{ $att->minutes_late ?: 15 }}" min="1"
                                                   class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Koreksi (Audit Trail) *</label>
                                            <textarea name="reason" rows="2" required placeholder="Contoh: Surat sakit dokter diserahkan wali murid pukul 09.00"
                                                      class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                                        </div>

                                        <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                                            <button type="button" @click="openCorrection = false" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">Batal</button>
                                            <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Simpan Koreksi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Belum ada rekaman presensi pada tanggal {{ Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
