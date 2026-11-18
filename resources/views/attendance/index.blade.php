@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Jurnal Presensi Harian Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pantau rekaman kehadiran gerbang masuk, keterlambatan, dan riwayat koreksi.</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('attendance.recap') }}" 
               class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Rekap Rombel
            </a>
            <a href="{{ route('attendance.sheet') }}" 
               class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs shadow-xs transition">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Lembar Absensi
            </a>
        </div>
    </div>

    <!-- Quick Stat Cards Harian -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
        <div class="p-3 rounded-lg bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wider block">Total Presensi</span>
            <div class="text-lg sm:text-xl font-bold tracking-tight text-slate-900 mt-0.5">{{ $stats['total_recorded'] }}</div>
        </div>
        <div class="p-3 rounded-lg bg-emerald-50/70 border border-emerald-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-emerald-800 uppercase tracking-wider block">Hadir Tepat</span>
            <div class="text-lg sm:text-xl font-bold tracking-tight text-emerald-700 mt-0.5">{{ $stats['present'] }}</div>
        </div>
        <div class="p-3 rounded-lg bg-amber-50/70 border border-amber-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-amber-800 uppercase tracking-wider block">Terlambat</span>
            <div class="text-lg sm:text-xl font-bold tracking-tight text-amber-700 mt-0.5">{{ $stats['late'] }}</div>
        </div>
        <div class="p-3 rounded-lg bg-blue-50/70 border border-blue-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-blue-800 uppercase tracking-wider block">Sakit (S)</span>
            <div class="text-lg sm:text-xl font-bold tracking-tight text-blue-700 mt-0.5">{{ $stats['sick'] }}</div>
        </div>
        <div class="p-3 rounded-lg bg-purple-50/70 border border-purple-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-purple-800 uppercase tracking-wider block">Izin (I)</span>
            <div class="text-lg sm:text-xl font-bold tracking-tight text-purple-700 mt-0.5">{{ $stats['excused'] }}</div>
        </div>
        <div class="p-3 rounded-lg bg-rose-50/70 border border-rose-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-rose-800 uppercase tracking-wider block">Alpa (A)</span>
            <div class="text-lg sm:text-xl font-bold tracking-tight text-rose-700 mt-0.5">{{ $stats['absent'] }}</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-3.5 sm:p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-2.5">
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" 
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
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Jenis Presensi</label>
                <select name="type_id" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                    <option value="">Semua Jenis</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" {{ $typeId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status Kehadiran</label>
                <select name="status" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                    <option value="">Semua Status</option>
                    <option value="present" {{ $status == 'present' ? 'selected' : '' }}>Hadir</option>
                    <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="sick" {{ $status == 'sick' ? 'selected' : '' }}>Sakit</option>
                    <option value="excused" {{ $status == 'excused' ? 'selected' : '' }}>Izin</option>
                    <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition">
                    Terapkan
                </button>
                <a href="{{ route('attendance.index') }}" class="py-1.5 px-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition">
                    Hari Ini
                </a>
            </div>
        </form>
    </div>

    <!-- Table Presensi -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[650px] text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">Siswa</th>
                        <th class="py-2.5 px-3">Rombel</th>
                        <th class="py-2.5 px-3">Sesi</th>
                        <th class="py-2.5 px-3">Jam Masuk</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Petugas</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
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
                            <div class="flex items-center justify-end space-x-2">
                                @if(in_array($att->status, ['late', 'absent']))
                                    @php
                                        $parentContact = $att->student->getPrimaryParentContact();
                                        $waText = "Assalamu'alaikum Wr. Wb. Yth. Bapak/Ibu Wali dari {$att->student->full_name} ({$att->student->currentClass?->name}). Kami dari " . ($schoolSetting->school_name ?? 'Madrasah') . " menginformasikan bahwa ananda tercatat " . strtoupper($att->status_label) . ($att->minutes_late > 0 ? " ({$att->minutes_late} menit)" : "") . " pada presensi hari ini (" . Carbon\Carbon::parse($att->date)->isoFormat('dddd, D MMMM Y') . "). Mohon konfirmasi atau perhatian Bapak/Ibu demi kelancaran belajar ananda. Terima kasih.";
                                    @endphp
                                    @if($parentContact)
                                        <a href="https://wa.me/{{ $parentContact['phone'] }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[11px] font-semibold border border-emerald-200 transition"
                                           title="Kirim Notifikasi WhatsApp ke {{ $parentContact['name'] }}">
                                            <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                            WA Ortu
                                        </a>
                                    @endif
                                @endif
                                <button @click="openCorrection = true" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                    Koreksi
                                </button>
                            </div>

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
