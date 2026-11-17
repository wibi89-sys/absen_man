@extends('layouts.app')

@section('title', 'Edit Pengumuman')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Pengumuman</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi resmi pengumuman madrasah.</p>
        </div>
        <a href="{{ route('communication.announcements.index') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('communication.announcements.update', $announcement) }}" class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Pengumuman <span class="text-rose-500">*</span></label>
            <input type="text" name="title" required value="{{ old('title', $announcement->title) }}" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border @error('title') border-rose-500 @enderror">
            @error('title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori <span class="text-rose-500">*</span></label>
                <select name="category" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border">
                    <option value="general" {{ old('category', $announcement->category) == 'general' ? 'selected' : '' }}>Umum</option>
                    <option value="academic" {{ old('category', $announcement->category) == 'academic' ? 'selected' : '' }}>Akademik</option>
                    <option value="attendance" {{ old('category', $announcement->category) == 'attendance' ? 'selected' : '' }}>Presensi & Disiplin</option>
                    <option value="worship" {{ old('category', $announcement->category) == 'worship' ? 'selected' : '' }}>Kegiatan Ibadah</option>
                    <option value="emergency" {{ old('category', $announcement->category) == 'emergency' ? 'selected' : '' }}>Penting / Darurat</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Target Sasaran <span class="text-rose-500">*</span></label>
                <select name="target_role" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border">
                    <option value="all" {{ old('target_role', $announcement->target_role) == 'all' ? 'selected' : '' }}>Semua Kalangan (Publik Madrasah)</option>
                    <option value="parents" {{ old('target_role', $announcement->target_role) == 'parents' ? 'selected' : '' }}>Khusus Orang Tua / Wali Murid</option>
                    <option value="students" {{ old('target_role', $announcement->target_role) == 'students' ? 'selected' : '' }}>Khusus Siswa</option>
                    <option value="teachers" {{ old('target_role', $announcement->target_role) == 'teachers' ? 'selected' : '' }}>Khusus Guru & Tenaga Pendidik</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Isi Pengumuman <span class="text-rose-500">*</span></label>
            <textarea name="content" rows="5" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border @error('content') border-rose-500 @enderror">{{ old('content', $announcement->content) }}</textarea>
            @error('content') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu Publikasi <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="published_at" required value="{{ old('published_at', $announcement->published_at->format('Y-m-d\TH:i')) }}" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu Kedaluwarsa (Opsional)</label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border">
            </div>
        </div>

        <div class="pt-2">
            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span>Sematkan pengumuman di bagian paling atas (Pin to top)</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('communication.announcements.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
