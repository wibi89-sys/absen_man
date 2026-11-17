@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Ubah Data Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi identitas atau rombongan belajar siswa.</p>
        </div>
        <a href="{{ route('master.students.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">&larr; Kembali</a>
    </div>

    <div class="p-6 sm:p-8 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form action="{{ route('master.students.update', $student) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nomor Induk Siswa (NIS) *</label>
                    <input type="text" name="nis" value="{{ old('nis', $student->nis) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">NISN (Nasional)</label>
                    <input type="text" name="nisn" value="{{ old('nisn', $student->nisn) }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap Siswa *</label>
                <input type="text" name="full_name" value="{{ old('full_name', $student->full_name) }}" required
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Panggilan</label>
                    <input type="text" name="nickname" value="{{ old('nickname', $student->nickname) }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jenis Kelamin *</label>
                    <select name="gender" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="L" {{ old('gender', $student->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $student->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Rombel / Kelas Aktif</label>
                    <select name="current_class_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Tanpa Rombel --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('current_class_id', $student->current_class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tanggal Masuk *</label>
                    <input type="date" name="enrollment_date" value="{{ old('enrollment_date', $student->enrollment_date?->format('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status Siswa *</label>
                <select name="status" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Lulus</option>
                    <option value="moved" {{ old('status', $student->status) == 'moved' ? 'selected' : '' }}>Pindah Sekolah</option>
                    <option value="dropped_out" {{ old('status', $student->status) == 'dropped_out' ? 'selected' : '' }}>Keluar (DO)</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end space-x-3">
                <a href="{{ route('master.students.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
                    Perbarui Siswa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
