@extends('layouts.app')

@section('title', 'Catat Dispensasi Ibadah')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Catat Dispensasi Ibadah</h1>
            <p class="text-sm text-slate-500 mt-1">Pencatatan izin berhalangan atau kondisi syar'i siswa dengan enkripsi & hak akses terbatas.</p>
        </div>
        <a href="{{ route('worship.exemptions.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <!-- Security & Ethics Alert -->
    <div class="bg-purple-50 border border-purple-200 rounded-2xl p-4 text-purple-950 text-sm flex gap-3">
        <svg class="w-5 h-5 text-purple-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <div>
            <strong class="font-semibold block mb-0.5">Jaminan Privasi & Perlindungan Siswa:</strong>
            <p class="text-xs text-purple-800 leading-relaxed">
                Informasi alasan detail (terutama halangan syar'i mahasiswi/siswi) tidak akan pernah ditampilkan pada portal umum atau rapor publik. Siswa hanya akan ditandai dengan label netral "Dispensasi".
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('worship.exemptions.store') }}" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-5" x-data="{
        selectedClassId: '',
        searchStudent: '',
        students: [],
        loadStudents() {
            if (!this.selectedClassId) return;
            fetch(`/api/classes/${this.selectedClassId}/students`)
                .then(r => r.json())
                .then(data => { this.students = data; });
        }
    }">
        @csrf

        <!-- Class Filter & Student Selection -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Rombel / Kelas <span class="text-rose-500">*</span></label>
            <select x-model="selectedClassId" @change="loadStudents()" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-purple-500 focus:border-purple-500 border">
                <option value="">-- Pilih Rombel Dahulu --</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Siswa <span class="text-rose-500">*</span></label>
            <select name="student_id" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-purple-500 focus:border-purple-500 border @error('student_id') border-rose-500 @enderror">
                <option value="">-- Pilih Siswa --</option>
                <template x-for="st in students" :key="st.id">
                    <option :value="st.id" x-text="`${st.full_name} (${st.nis})`"></option>
                </template>
            </select>
            @error('student_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Date Range -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai <span class="text-rose-500">*</span></label>
                <input type="date" name="start_date" required value="{{ old('start_date', now()->toDateString()) }}" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-purple-500 focus:border-purple-500 border @error('start_date') border-rose-500 @enderror">
                @error('start_date') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Berakhir <span class="text-rose-500">*</span></label>
                <input type="date" name="end_date" required value="{{ old('end_date', now()->addDays(6)->toDateString()) }}" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-purple-500 focus:border-purple-500 border @error('end_date') border-rose-500 @enderror">
                @error('end_date') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Category -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori Alasan <span class="text-rose-500">*</span></label>
            <select name="reason_category" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-purple-500 focus:border-purple-500 border @error('reason_category') border-rose-500 @enderror">
                <option value="sharia_halangan" {{ old('reason_category') == 'sharia_halangan' ? 'selected' : '' }}>Halangan Syar'i (Siswi / Haid / Nifas)</option>
                <option value="sick" {{ old('reason_category') == 'sick' ? 'selected' : '' }}>Sakit / Rawat Medis</option>
                <option value="emergency" {{ old('reason_category') == 'emergency' ? 'selected' : '' }}>Kondisi Darurat Keluarga</option>
                <option value="dispensation" {{ old('reason_category') == 'dispensation' ? 'selected' : '' }}>Dispensasi Tugas Sekolah Resmi</option>
            </select>
            @error('reason_category') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Confidential Notes -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Catatan Tertutup (Opsional)</label>
            <textarea name="notes" rows="2" placeholder="Catatan internal verifikator (hanya dapat dibaca oleh role berwenang)..." class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-purple-500 focus:border-purple-500 border">{{ old('notes') }}</textarea>
            <span class="text-[11px] text-slate-400 mt-1 block">Catatan ini terlindungi secara kriptografis dan tidak dapat diakses orang lain.</span>
        </div>

        <!-- Confirmation Checkbox -->
        <div class="pt-2">
            <label class="flex items-start gap-2.5 text-xs text-slate-700 cursor-pointer">
                <input type="checkbox" name="is_confidential" value="1" checked class="rounded border-slate-300 text-purple-600 focus:ring-purple-500 mt-0.5">
                <span>Saya mengonfirmasi bahwa pencatatan dispensasi ini sah dan wajib dijaga kerahasiaan datanya oleh madrasah.</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('worship.exemptions.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-xs transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Dispensasi
            </button>
        </div>
    </form>
</div>
@endsection
