@extends('layouts.app')

@section('title', 'Catat Pelanggaran / Prestasi Siswa')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Catat Kejadian Disiplin / Prestasi</h1>
            <p class="text-sm text-slate-500 mt-1">Pencatatan pelanggaran atau apresiasi penghargaan siswa madrasah.</p>
        </div>
        <a href="{{ route('discipline.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('discipline.store') }}" class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-5" x-data="{
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
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Siswa <span class="text-rose-500">*</span></label>
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
                <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori Kejadian <span class="text-rose-500">*</span></label>
                <select name="category_id" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">
                            [{{ strtoupper($cat->type) }}] {{ $cat->name }} ({{ $cat->points > 0 ? '+' : '' }}{{ $cat->points }} Poin)
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Kejadian <span class="text-rose-500">*</span></label>
                <input type="date" name="date" required value="{{ old('date', now()->toDateString()) }}" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Kronologi / Deskripsi Kejadian <span class="text-rose-500">*</span></label>
            <textarea name="description" rows="3" required placeholder="Tuliskan kronologi singkat, waktu, dan tempat kejadian..." class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Tindakan / Pembinaan Awal</label>
            <textarea name="action_taken" rows="2" placeholder="Contoh: Teguran lisan, pemanggilan orang tua, atau penyerahan piagam penghargaan..." class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">{{ old('action_taken') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Status Penanganan <span class="text-rose-500">*</span></label>
            <select name="status" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
                <option value="reported">Dilaporkan</option>
                <option value="processed">Dalam Penanganan</option>
                <option value="resolved">Terselesaikan</option>
            </select>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('discipline.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Catatan
            </button>
        </div>
    </form>
</div>
@endsection
