@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Ubah Rombel</h2>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui penetapan wali kelas atau kapasitas rombel.</p>
        </div>
        <a href="{{ route('master.classes.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">&larr; Kembali</a>
    </div>

    <div class="p-6 sm:p-8 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form action="{{ route('master.classes.update', $class) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tahun Ajaran *</label>
                    <select name="academic_year_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" {{ old('academic_year_id', $class->academic_year_id) == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tingkat / Grade *</label>
                    <select name="grade_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        @foreach($grades as $g)
                            <option value="{{ $g->id }}" {{ old('grade_id', $class->grade_id) == $g->id ? 'selected' : '' }}>Kelas {{ $g->name }} (Tingkat {{ $g->level }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Rombel / Kelas *</label>
                <input type="text" name="name" value="{{ old('name', $class->name) }}" required
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Wali Kelas</label>
                    <select name="homeroom_teacher_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ old('homeroom_teacher_id', $class->homeroom_teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->formatted_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kapasitas Maksimal *</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $class->capacity) }}" min="1" max="50" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $class->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs text-slate-700 font-semibold">Rombel Aktif pada Tahun Ajaran Berjalan</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end space-x-3">
                <a href="{{ route('master.classes.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
                    Perbarui Rombel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
