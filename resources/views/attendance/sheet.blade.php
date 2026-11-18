@extends('layouts.app')

@section('content')
<div class="space-y-5" x-data="{
    markAll(status) {
        document.querySelectorAll('input[type=radio][value=' + status + ']').forEach(el => {
            el.checked = true;
            el.dispatchEvent(new Event('change'));
        });
    }
}">
    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Lembar Input Presensi Rombel</h2>
            <p class="text-xs text-slate-500 mt-0.5">Input kehadiran siswa kelas secara serentak untuk Guru Piket dan Wali Kelas.</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('attendance.index') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition">
                &larr; Jurnal Harian
            </a>
        </div>
    </div>

    <!-- Filter Pemilihan Sesi & Rombel -->
    <div class="p-3.5 sm:p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('attendance.sheet') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Presensi</label>
                <input type="date" name="date" value="{{ $date }}" 
                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Jenis Presensi</label>
                <select name="type_id" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" {{ $type->id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Pilih Rombel / Kelas</label>
                <select name="class_id" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClass?->id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-1.5 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition">
                    Buka Lembar Kelas
                </button>
            </div>
        </form>
    </div>

    @if($selectedClass)
    <!-- Lembar Presensi Form -->
    <form action="{{ route('attendance.store-batch') }}" method="POST">
        @csrf
        <input type="hidden" name="session_id" value="{{ $session->id }}">

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Toolbar Cepat Lembar Kelas -->
            <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-slate-900 text-sm">
                        Kelas {{ $selectedClass->name }} 
                        <span class="text-xs font-normal text-slate-500">({{ $students->count() }} siswa terdaftar)</span>
                    </h3>
                    <div class="text-[11px] text-slate-500">
                        Sesi: <strong>{{ $type->name }}</strong> &bull; Wali Kelas: {{ $selectedClass->homeroomTeacher?->formatted_name ?? '-' }}
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="text-xs text-slate-500 font-medium">Aksi Cepat:</span>
                    <button type="button" @click="markAll('present')" class="px-2.5 py-1 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-medium border border-emerald-200/60 transition">
                        ✓ Semua Hadir
                    </button>
                </div>
            </div>

            <!-- Tabel Siswa -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left text-xs text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            <th class="py-2.5 px-3">Nama Siswa / NIS</th>
                            <th class="py-2.5 px-3 text-center">Status Kehadiran</th>
                            <th class="py-2.5 px-3">Catatan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $index => $student)
                        @php
                            $current = $existingAttendances->get($student->id);
                            $selectedStatus = old("attendances.{$student->id}.status", $current?->status ?? 'present');
                            $minutesLate = old("attendances.{$student->id}.minutes_late", $current?->minutes_late ?? 15);
                            $notes = old("attendances.{$student->id}.notes", $current?->notes ?? '');
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition" x-data="{ status: '{{ $selectedStatus }}' }">
                            <td class="py-2.5 px-3 text-center font-mono text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-medium text-slate-900">{{ $student->full_name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">NIS: {{ $student->nis }} ({{ $student->gender == 'L' ? 'L' : 'P' }})</div>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="flex items-center justify-center space-x-2 sm:space-x-3">
                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="present" 
                                               x-model="status"
                                               class="text-emerald-600 focus:ring-emerald-500">
                                        <span class="font-medium text-emerald-800 text-xs">Hadir</span>
                                    </label>

                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="late" 
                                               x-model="status"
                                               class="text-amber-600 focus:ring-amber-500">
                                        <span class="font-medium text-amber-800 text-xs">Terlambat</span>
                                    </label>

                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="sick" 
                                               x-model="status"
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="font-medium text-blue-800 text-xs">Sakit</span>
                                    </label>

                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="excused" 
                                               x-model="status"
                                               class="text-purple-600 focus:ring-purple-500">
                                        <span class="font-medium text-purple-800 text-xs">Izin</span>
                                    </label>

                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="absent" 
                                               x-model="status"
                                               class="text-rose-600 focus:ring-rose-500">
                                        <span class="font-medium text-rose-800 text-xs">Alpa</span>
                                    </label>
                                </div>

                                <!-- Input Menit Keterlambatan jika Terlambat -->
                                <div x-show="status === 'late'" class="mt-1 text-center" style="display: none;">
                                    <span class="text-[10px] text-slate-500">Terlambat:</span>
                                    <input type="number" name="attendances[{{ $student->id }}][minutes_late]" value="{{ $minutesLate }}" min="1" max="180"
                                           class="w-14 px-1.5 py-0.5 text-center rounded border border-slate-300 text-xs font-mono font-medium text-amber-700">
                                    <span class="text-[10px] text-slate-500">menit</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="text" name="attendances[{{ $student->id }}][notes]" value="{{ $notes }}" placeholder="Keterangan opsional..."
                                       class="w-full px-2 py-1 rounded-md border border-slate-200 text-xs text-slate-700 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">
                                Belum ada siswa yang terdaftar aktif di rombel ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->isNotEmpty())
            <!-- Submit Bottom Bar -->
            <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Pastikan seluruh pilihan presensi telah diperiksa sebelum menekan simpan.
                </span>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs shadow-xs transition">
                    Simpan Presensi Rombel
                </button>
            </div>
            @endif
        </div>
    </form>
    @endif
</div>
@endsection
