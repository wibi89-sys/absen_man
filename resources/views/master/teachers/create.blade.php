@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Tambah Guru Baru</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftarkan tenaga pendidik baru ke dalam pangkalan data.</p>
        </div>
        <a href="{{ route('master.teachers.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">&larr; Kembali</a>
    </div>

    <div class="p-6 sm:p-8 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form action="{{ route('master.teachers.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nomor Induk Pegawai (NIP)</label>
                <input type="text" name="nip" value="{{ old('nip') }}"
                       placeholder="Contoh: 198501012010011001"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Gelar Depan</label>
                    <input type="text" name="title_prefix" value="{{ old('title_prefix') }}"
                           placeholder="Drs., Ust., Dr."
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap *</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                           placeholder="Nama lengkap..."
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Gelar Belakang</label>
                    <input type="text" name="title_suffix" value="{{ old('title_suffix') }}"
                           placeholder="M.Pd., S.Ag."
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nomor Telepon / WA</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           placeholder="0812xxxxxxx"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status Keaktifan *</label>
                    <select name="status" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktif Mengajar</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end space-x-3">
                <a href="{{ route('master.teachers.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
                    Simpan Guru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
