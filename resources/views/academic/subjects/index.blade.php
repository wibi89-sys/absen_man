@extends('layouts.app')

@section('title', 'Mata Pelajaran & Penugasan Guru')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Mata Pelajaran & Penugasan Guru</h1>
            <p class="text-sm text-slate-500 mt-1">Konfigurasi kurikulum mata pelajaran dan penugasan guru pengampu per rombel.</p>
        </div>
        <div class="text-xs text-slate-500 font-semibold px-3 py-1.5 rounded-xl bg-slate-100">
            Tahun Ajaran: <strong class="text-emerald-700">{{ $activeYear?->name ?? 'Aktif' }}</strong>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Form Tambah Mata Pelajaran -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
            <h3 class="text-base font-bold text-slate-900">Tambah Mata Pelajaran</h3>
            <form method="POST" action="{{ route('academic.subjects.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Mapel <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" required placeholder="Contoh: PAI-01, MTK-WAJIB" class="w-full text-sm border-slate-300 rounded-xl px-3 py-2 border uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Al-Qur'an Hadis, Matematika" class="w-full text-sm border-slate-300 rounded-xl px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Urut Tampil</label>
                    <input type="number" name="order" value="1" class="w-full text-sm border-slate-300 rounded-xl px-3 py-2 border">
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                    Simpan Mata Pelajaran
                </button>
            </form>

            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Daftar Mapel Terdaftar ({{ $subjects->count() }})</h4>
                <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 text-xs">
                    @foreach($subjects as $s)
                    <div class="py-2 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800">{{ $s->name }}</span>
                            <span class="text-[10px] text-slate-400 block font-mono">{{ $s->code }}</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold">
                            {{ $s->class_subjects_count }} Rombel
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Penugasan Guru per Rombel -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-base font-bold text-slate-900">Penugasan Guru per Rombel</h3>
                <form method="GET" action="{{ route('academic.subjects.index') }}" class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-600">Pilih Rombel:</label>
                    <select name="class_id" onchange="this.form.submit()" class="text-xs border-slate-300 rounded-lg px-2.5 py-1.5 border">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Form Assign -->
            <form method="POST" action="{{ route('academic.subjects.assign') }}" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
                @csrf
                <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
                <input type="hidden" name="academic_year_id" value="{{ $activeYear?->id ?? 1 }}">
                <input type="hidden" name="semester_id" value="{{ $activeSemester?->id }}">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select name="subject_id" required class="w-full text-xs border-slate-300 rounded-lg px-2.5 py-2 border bg-white">
                        <option value="">-- Pilih Mapel --</option>
                        @foreach($subjects as $sb)
                            <option value="{{ $sb->id }}">{{ $sb->name }} ({{ $sb->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Guru Pengampu <span class="text-rose-500">*</span></label>
                    <select name="teacher_id" required class="w-full text-xs border-slate-300 rounded-lg px-2.5 py-2 border bg-white">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition">
                        Tugaskan ke Rombel
                    </button>
                </div>
            </form>

            <!-- Table Assigned Subjects -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4">Guru Pengampu</th>
                            <th class="py-3 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($assignedSubjects as $as)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-600">{{ $as->subject->code }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $as->subject->name }}</td>
                            <td class="py-3 px-4 text-emerald-800 font-semibold">{{ $as->teacher->user->name }}</td>
                            <td class="py-3 px-4 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                    Aktif Mengajar
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">
                                Belum ada mata pelajaran yang ditugaskan pada rombel ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
