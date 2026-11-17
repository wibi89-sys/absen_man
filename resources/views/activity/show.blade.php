@extends('layouts.app')

@section('title', 'Detail Kegiatan: ' . $activity->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 capitalize">
                    {{ $activity->category }}
                </span>
                <span class="text-xs text-slate-500 font-semibold">{{ $activity->day_name }} &bull; {{ $activity->location ?? 'Madrasah' }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $activity->name }}</h1>
            <p class="text-xs text-slate-500">
                Guru Pembina: <strong class="text-slate-800">{{ $activity->supervisor?->user?->name ?? '-' }}</strong> &bull;
                Total Anggota Aktif: <strong class="text-emerald-700">{{ $members->count() }} Siswa</strong>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Integration Button: Generate Sesi Asar -->
            <form method="POST" action="{{ route('activities.generate-asar', $activity) }}" onsubmit="return confirm('Buat sesi monitoring Salat Asar untuk anggota kegiatan {{ $activity->name }}?')">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Buat Sesi Salat Asar Hari Ini
                </button>
            </form>

            <a href="{{ route('activities.index') }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Attendance Form for Activity Session -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Attendance Sheet -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-5 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-sm font-bold text-slate-900">Lembar Presensi Latihan / Kegiatan</h3>
                <form method="GET" action="{{ route('activities.show', $activity) }}" class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-600">Tanggal:</label>
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="text-xs border-slate-300 rounded-lg px-2.5 py-1 border bg-white">
                </form>
            </div>

            <form method="POST" action="{{ route('activities.attendance.store', $activity) }}">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4">Nama Anggota</th>
                                <th class="py-3 px-4">Rombel</th>
                                <th class="py-3 px-4">Peran</th>
                                <th class="py-3 px-4 text-center">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($members as $m)
                            @php
                                $att = $attendances->get($m->student_id);
                                $status = $att?->status ?? 'present';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $m->student->full_name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $m->student->nis }}</div>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    {{ $m->student->currentClass?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 capitalize font-medium text-slate-600">
                                    {{ $m->role }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <select name="attendances[{{ $m->student_id }}]" class="text-xs border-slate-300 rounded-lg px-2 py-1 border bg-white">
                                        <option value="present" {{ $status === 'present' ? 'selected' : '' }}>Hadir</option>
                                        <option value="absent" {{ $status === 'absent' ? 'selected' : '' }}>Alfa / Tidak Hadir</option>
                                        <option value="excused" {{ $status === 'excused' ? 'selected' : '' }}>Izin</option>
                                    </select>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">
                                    Belum ada anggota yang terdaftar pada kegiatan ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($members->isNotEmpty())
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition">
                        Simpan Presensi Kegiatan
                    </button>
                </div>
                @endif
            </form>
        </div>

        <!-- Add Member Form -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4" x-data="{
            selectedClassId: '',
            students: [],
            loadStudents() {
                if (!this.selectedClassId) return;
                fetch(`/api/classes/${this.selectedClassId}/students`)
                    .then(r => r.json())
                    .then(data => { this.students = data; });
            }
        }">
            <h3 class="text-base font-bold text-slate-900">Tambah Anggota Siswa</h3>
            <form method="POST" action="{{ route('activities.members.store', $activity) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Rombel Siswa</label>
                    <select x-model="selectedClassId" @change="loadStudents()" class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">
                        <option value="">-- Pilih Rombel --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Siswa <span class="text-rose-500">*</span></label>
                    <select name="student_id" required class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">
                        <option value="">-- Pilih Siswa --</option>
                        <template x-for="st in students" :key="st.id">
                            <option :value="st.id" x-text="`${st.full_name} (${st.nis})`"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Peran dalam Kegiatan <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">
                        <option value="anggota">Anggota</option>
                        <option value="pengurus">Pengurus / Seksi</option>
                        <option value="ketua">Ketua / Koordinator</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Bergabung <span class="text-rose-500">*</span></label>
                    <input type="date" name="joined_date" required value="{{ now()->toDateString() }}" class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">
                </div>

                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                    Daftarkan Siswa
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
