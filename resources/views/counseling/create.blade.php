@extends('layouts.app')

@section('title', 'Jadwalkan Sesi Konseling')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Jadwalkan Sesi Konseling BK</h1>
            <p class="text-sm text-slate-500 mt-1">Pencatatan sesi bimbingan konseling dengan hak akses sangat terbatas.</p>
        </div>
        <a href="{{ route('counseling.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('counseling.store') }}" class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-5" x-data="{
        selectedClassId: '',
        students: [],
        loadStudents() {
            if (!this.selectedClassId) return;
            fetch(`/api/classes/${this.selectedClassId}/students`)
                .then(r => r.json())
                .then(data => { this.students = data; });
        }
    }">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Rombel <span class="text-rose-500">*</span></label>
                <select x-model="selectedClassId" @change="loadStudents()" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
                    <option value="">-- Pilih Rombel --</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Siswa Konseli <span class="text-rose-500">*</span></label>
                <select name="student_id" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border @error('student_id') border-rose-500 @enderror">
                    <option value="">-- Pilih Siswa --</option>
                    <template x-for="st in students" :key="st.id">
                        <option :value="st.id" x-text="`${st.full_name} (${st.nis})`"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Guru BK Konselor <span class="text-rose-500">*</span></label>
                <select name="counselor_id" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
                    @foreach($counselors as $tc)
                        <option value="{{ $tc->id }}">{{ $tc->user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Sesi <span class="text-rose-500">*</span></label>
                <input type="date" name="session_date" required value="{{ old('session_date', now()->toDateString()) }}" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori Kasus / Bimbingan <span class="text-rose-500">*</span></label>
                <select name="category" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
                    <option value="pribadi">Konseling Masalah Pribadi</option>
                    <option value="sosial">Hubungan Teman / Sosial</option>
                    <option value="belajar">Kesulitan Belajar / Akademik</option>
                    <option value="karir">Bimbingan Karir & Studi Lanjut</option>
                    <option value="kedisiplinan">Pelanggaran Disiplin Berat</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tingkat Kerahasiaan <span class="text-rose-500">*</span></label>
                <select name="confidentiality_level" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
                    <option value="strictly_confidential">Sangat Rahasia (Strictly Confidential)</option>
                    <option value="standard">Standar (Hanya Guru BK & Pimpinan)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Latar Belakang Kasus / Pokok Masalah <span class="text-rose-500">*</span></label>
            <textarea name="case_background" rows="3" required placeholder="Uraikan pokok keluhan atau masalah siswa secara komprehensif..." class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">{{ old('case_background') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Rencana Tindakan (Action Plan)</label>
            <textarea name="action_plan" rows="2" placeholder="Langkah-langkah intervensi bimbingan yang disepakati..." class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">{{ old('action_plan') }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status Sesi <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
                    <option value="scheduled">Dijadwalkan</option>
                    <option value="in_progress">Sedang Berjalan</option>
                    <option value="completed">Selesai</option>
                    <option value="follow_up_needed">Perlu Tindak Lanjut</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Tindak Lanjut</label>
                <input type="date" name="follow_up_date" value="{{ old('follow_up_date') }}" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('counseling.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-xs transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Berkas Konseling
            </button>
        </div>
    </form>
</div>
@endsection
