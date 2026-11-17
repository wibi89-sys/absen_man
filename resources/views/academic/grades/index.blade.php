@extends('layouts.app')

@section('title', 'Penilaian Capaian Akademik')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Penilaian Capaian Akademik</h1>
            <p class="text-sm text-slate-500 mt-1">Input nilai tugas, ulangan harian, dan publikasi capaian belajar siswa kepada orang tua.</p>
        </div>
    </div>

    <!-- Filter Bar: Class & Subject Selector -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('academic.grades.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Rombel / Kelas</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full text-sm border-slate-300 rounded-xl px-3 py-2 border">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Mata Pelajaran</label>
                <select name="class_subject_id" onchange="this.form.submit()" class="w-full text-sm border-slate-300 rounded-xl px-3 py-2 border">
                    @forelse($classSubjects as $cs)
                        <option value="{{ $cs->id }}" {{ $selectedSubjectId == $cs->id ? 'selected' : '' }}>
                            {{ $cs->subject->name }} (Guru: {{ $cs->teacher->user->name }})
                        </option>
                    @empty
                        <option value="" disabled>Belum ada mapel ditugaskan pada rombel ini</option>
                    @endforelse
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold py-2 px-4 rounded-xl transition">
                    Pilih & Muat Siswa
                </button>
            </div>
        </form>
    </div>

    @if($currentClassSubject)
    <!-- Batch Grade Input Card -->
    <form method="POST" action="{{ route('academic.grades.batch') }}" class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        @csrf
        <input type="hidden" name="class_subject_id" value="{{ $currentClassSubject->id }}">

        <div class="p-5 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Entri Nilai: {{ $currentClassSubject->subject->name }}</h3>
                <span class="text-xs text-slate-500">Kelas: <strong>{{ $currentClassSubject->schoolClass->name }}</strong> &bull; Guru Pengampu: {{ $currentClassSubject->teacher->user->name }}</span>
            </div>

            <div class="flex items-center gap-3">
                <label class="text-xs font-semibold text-slate-700">Nama Asesmen / Penilaian:</label>
                <input type="text" name="assessment_name" required value="Tugas 1" placeholder="Contoh: Tugas 1, UH 1, PTS" class="text-xs border-slate-300 rounded-xl px-3 py-1.5 border bg-white font-bold w-40">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">NIS</th>
                        <th class="py-3 px-4 w-36">Nilai (0 - 100)</th>
                        <th class="py-3 px-4">Catatan Guru (Opsional)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($students as $index => $st)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center font-bold text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900 text-sm">{{ $st->full_name }}</td>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $st->nis }}</td>
                        <td class="py-3 px-4">
                            <input type="number" step="0.1" min="0" max="100" name="scores[{{ $st->id }}]" placeholder="85.0" class="w-full text-sm font-bold border-slate-300 rounded-xl px-3 py-1.5 border text-center font-mono">
                        </td>
                        <td class="py-3 px-4">
                            <input type="text" name="notes[{{ $st->id }}]" placeholder="Catatan perkembangan..." class="w-full text-xs border-slate-300 rounded-xl px-3 py-1.5 border">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400">
                            Tidak ada siswa aktif di rombel ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end">
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Seluruh Nilai
            </button>
        </div>
    </form>
    @endif
</div>
@endsection
