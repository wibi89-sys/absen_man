@extends('layouts.app')

@section('title', 'Buat Sesi Ibadah Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Buat Sesi Ibadah Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Konfigurasi sesi ibadah gelombang dan penguncian peserta wajib (deterministic denominator).</p>
        </div>
        <a href="{{ route('worship.sessions.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <!-- Mandatory Rule Notice -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-blue-900 text-sm flex gap-3">
        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <strong class="font-semibold block mb-1">Ketetapan Denominator & Proteksi Kerahasiaan:</strong>
            <p class="text-xs text-blue-800 leading-relaxed">
                Seluruh siswa aktif di kelas yang dipilih akan otomatis didaftarkan ke tabel peserta sesi saat sesi dibuat. Siswa yang tidak melakukan scan akan tetap terhitung dalam denominator laporan (sebagai 'Tidak Hadir' setelah finalisasi). Siswa yang memiliki dispensasi aktif (seperti halangan syar'i mahasiswi/siswi) otomatis berstatus 'Dispensasi' tanpa menampilkan alasan sensitif ke publik.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('worship.sessions.store') }}" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6" x-data="{
        selectAll: false,
        toggleAll() {
            let boxes = document.querySelectorAll('.class-checkbox');
            boxes.forEach(b => b.checked = this.selectAll);
        }
    }">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Worship Type -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Kegiatan Ibadah <span class="text-rose-500">*</span></label>
                <select name="worship_type_id" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border @error('worship_type_id') border-rose-500 @enderror">
                    @foreach($worshipTypes as $type)
                        <option value="{{ $type->id }}" {{ old('worship_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ strtoupper($type->code) }})
                        </option>
                    @endforeach
                </select>
                @error('worship_type_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Date -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Pelaksanaan <span class="text-rose-500">*</span></label>
                <input type="date" name="date" required value="{{ old('date', now()->toDateString()) }}" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border @error('date') border-rose-500 @enderror">
                @error('date') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Wave Number -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Gelombang <span class="text-rose-500">*</span></label>
                <select name="wave_number" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border @error('wave_number') border-rose-500 @enderror">
                    <option value="1" {{ old('wave_number') == 1 ? 'selected' : '' }}>Gelombang 1 (Utama)</option>
                    <option value="2" {{ old('wave_number') == 2 ? 'selected' : '' }}>Gelombang 2</option>
                    <option value="3" {{ old('wave_number') == 3 ? 'selected' : '' }}>Gelombang 3</option>
                </select>
                @error('wave_number') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi Tempat Ibadah</label>
                <select name="location_id" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border @error('location_id') border-rose-500 @enderror">
                    <option value="">-- Pilih Lokasi (Opsional) --</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} (Kapasitas: {{ $loc->capacity }} jamaah)
                        </option>
                    @endforeach
                </select>
                @error('location_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Class Selection for Denominator Locking -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-semibold text-slate-700">
                    Rombel / Kelas yang Dijadwalkan <span class="text-rose-500">*</span>
                </label>
                <label class="inline-flex items-center text-xs font-semibold text-emerald-700 cursor-pointer">
                    <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mr-2">
                    Pilih Semua Kelas
                </label>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl max-h-60 overflow-y-auto">
                @foreach($classes as $c)
                <label class="flex items-center space-x-2 text-sm text-slate-800 bg-white p-2.5 rounded-lg border border-slate-200 hover:border-emerald-300 cursor-pointer transition">
                    <input type="checkbox" name="class_ids[]" value="{{ $c->id }}" class="class-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" {{ is_array(old('class_ids')) && in_array($c->id, old('class_ids')) ? 'checked' : '' }}>
                    <span class="font-medium text-xs">{{ $c->name }}</span>
                </label>
                @endforeach
            </div>
            @error('class_ids') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Catatan Tambahan (Opsional)</label>
            <textarea name="notes" rows="2" placeholder="Contoh: Gelombang 1 untuk rombel putra kelas X & XI..." class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('worship.sessions.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Buat Sesi & Kunci Peserta
            </button>
        </div>
    </form>
</div>
@endsection
