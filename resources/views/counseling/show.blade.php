@extends('layouts.app')

@section('title', 'Berkas Rahasia Konseling: ' . $counseling->student->full_name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Berkas Bimbingan Konseling</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                    Sangat Rahasia
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Hanya dapat diakses oleh Guru BK dan Pejabat Berwenang.</p>
        </div>
        <a href="{{ route('counseling.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <!-- Student Metadata Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white font-extrabold flex items-center justify-center text-base shadow-sm">
                {{ strtoupper(substr($counseling->student->full_name, 0, 2)) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $counseling->student->full_name }}</h2>
                <p class="text-xs text-slate-500 font-mono">NIS: {{ $counseling->student->nis }} &bull; Rombel: {{ $counseling->student->currentClass?->name ?? '-' }}</p>
                <span class="text-xs text-emerald-800 font-medium">Konselor: <strong>{{ $counseling->counselor->user->name }}</strong></span>
            </div>
        </div>

        <div class="text-right">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $counseling->status_badge }}">
                {{ $counseling->status_label }}
            </span>
            <span class="text-xs text-slate-400 block mt-1 font-mono">Tanggal: {{ $counseling->session_date->format('d M Y') }}</span>
        </div>
    </div>

    <!-- Case Details Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-5">
        <div>
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Kategori Bimbingan</span>
            <h3 class="text-base font-bold text-slate-800 mt-0.5">{{ $counseling->category_label }}</h3>
        </div>

        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Latar Belakang Kasus & Catatan Sesi</span>
            <p class="text-sm text-slate-800 whitespace-pre-line leading-relaxed">{{ $counseling->case_background }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100 space-y-1">
            <span class="text-xs font-bold text-blue-800 uppercase tracking-wider block">Rencana Tindakan (Action Plan)</span>
            <p class="text-sm text-blue-950 whitespace-pre-line leading-relaxed">{{ $counseling->action_plan ?? 'Belum ada rencana tindakan tertulis.' }}</p>
        </div>

        @if($counseling->recommendations)
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 space-y-1">
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Rekomendasi Konselor</span>
            <p class="text-sm text-emerald-950 whitespace-pre-line leading-relaxed">{{ $counseling->recommendations }}</p>
        </div>
        @endif

        @if($counseling->follow_up_date)
        <div class="flex items-center gap-2 text-xs font-medium text-slate-700 bg-amber-50 p-3 rounded-xl border border-amber-200">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>Jadwal Tindak Lanjut: <strong>{{ $counseling->follow_up_date->isoFormat('dddd, D MMMM Y') }}</strong></span>
        </div>
        @endif
    </div>

    <!-- Update Progress Form -->
    <form method="POST" action="{{ route('counseling.update', $counseling) }}" class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
        @csrf
        @method('PUT')
        <h4 class="text-sm font-bold text-slate-900">Perbarui Perkembangan Kasus</h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Penanganan</label>
                <select name="status" class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">
                    <option value="scheduled" {{ $counseling->status === 'scheduled' ? 'selected' : '' }}>Dijadwalkan</option>
                    <option value="in_progress" {{ $counseling->status === 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="completed" {{ $counseling->status === 'completed' ? 'selected' : '' }}>Selesai / Tuntas</option>
                    <option value="follow_up_needed" {{ $counseling->status === 'follow_up_needed' ? 'selected' : '' }}>Perlu Tindak Lanjut</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Tindak Lanjut</label>
                <input type="date" name="follow_up_date" value="{{ $counseling->follow_up_date?->toDateString() }}" class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Perbarui Rencana Tindakan</label>
            <textarea name="action_plan" rows="2" class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">{{ $counseling->action_plan }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Perbarui Rekomendasi</label>
            <textarea name="recommendations" rows="2" class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">{{ $counseling->recommendations }}</textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition">
                Simpan Perkembangan
            </button>
        </div>
    </form>
</div>
@endsection
