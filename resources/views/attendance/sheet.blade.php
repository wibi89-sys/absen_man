@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
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
            <h2 class="text-xl font-bold text-slate-900">Lembar Input Presensi Rombel</h2>
            <p class="text-xs text-slate-500 mt-0.5">Input kehadiran siswa kelas secara cepat untuk Guru Piket dan Wali Kelas.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('attendance.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Jurnal Harian
            </a>
        </div>
    </div>

    <!-- Filter Pemilihan Sesi & Rombel -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('attendance.sheet') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Presensi</label>
                <input type="date" name="date" value="{{ $date }}" 
                       class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Presensi</label>
                <select name="type_id" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" {{ $type->id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Rombel / Kelas</label>
                <select name="class_id" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClass?->id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
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

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <!-- Toolbar Cepat Lembar Kelas -->
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">
                        Kelas {{ $selectedClass->name }} 
                        <span class="text-xs font-normal text-slate-500">({{ $students->count() }} siswa terdaftar)</span>
                    </h3>
                    <div class="text-[11px] text-slate-500">
                        Sesi: <strong>{{ $type->name }}</strong> | Wali Kelas: {{ $selectedClass->homeroomTeacher?->formatted_name ?? '-' }}
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="text-xs text-slate-500 font-medium">Aksi Cepat:</span>
                    <button type="button" @click="markAll('present')" class="px-3 py-1.5 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-bold transition">
                        ✓ Semua Hadir
                    </button>
                </div>
            </div>

            <!-- Tabel Siswa -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left text-xs text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama Siswa / NIS</th>
                            <th class="py-3 px-4 text-center">Status Kehadiran</th>
                            <th class="py-3 px-4">Catatan / Alasan</th>
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
                        <tr class="hover:bg-slate-50 transition" x-data="{ status: '{{ $selectedStatus }}' }">
                            <td class="py-3 px-4 text-center font-mono text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $student->full_name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">NIS: {{ $student->nis }} ({{ $student->gender == 'L' ? 'L' : 'P' }})</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-center space-x-2 sm:space-x-4">
                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="present" 
                                               x-model="status"
                                               class="text-emerald-600 focus:ring-emerald-500">
                                        <span class="font-semibold text-emerald-800 text-xs">Hadir</span>
                                    </label>

                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="late" 
                                               x-model="status"
                                               class="text-amber-600 focus:ring-amber-500">
                                        <span class="font-semibold text-amber-800 text-xs">Terlambat</span>
                                    </label>

                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="sick" 
                                               x-model="status"
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="font-semibold text-blue-800 text-xs">Sakit</span>
                                    </label>

                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="excused" 
                                               x-model="status"
                                               class="text-purple-600 focus:ring-purple-500">
                                        <span class="font-semibold text-purple-800 text-xs">Izin</span>
                                    </label>

                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}][status]" value="absent" 
                                               x-model="status"
                                               class="text-rose-600 focus:ring-rose-500">
                                        <span class="font-semibold text-rose-800 text-xs">Alpa</span>
                                    </label>
                                </div>

                                <!-- Input Menit Keterlambatan jika Terlambat -->
                                <div x-show="status === 'late'" class="mt-2 text-center" style="display: none;">
                                    <span class="text-[10px] text-slate-500">Terlambat:</span>
                                    <input type="number" name="attendances[{{ $student->id }}][minutes_late]" value="{{ $minutesLate }}" min="1" max="180"
                                           class="w-16 px-2 py-0.5 text-center rounded border border-slate-300 text-xs font-mono font-bold text-amber-700">
                                    <span class="text-[10px] text-slate-500">menit</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <input type="text" name="attendances[{{ $student->id }}][notes]" value="{{ $notes }}" placeholder="Keterangan opsional..."
                                       class="w-full px-2.5 py-1 rounded-lg border border-slate-200 text-xs text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">
                                Belum ada siswa yang terdaftar aktif di rombel ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->isNotEmpty())
            <!-- Submit Bottom Bar -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Pastikan seluruh pilihan presensi telah diperiksa sebelum menekan simpan.
                </span>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition">
                    Simpan Presensi Rombel
                </button>
            </div>
            @endif
        </div>
    </form>
    @endif
</div>
@endsection
