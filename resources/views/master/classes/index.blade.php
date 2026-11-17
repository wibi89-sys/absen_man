@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Rombongan Belajar (Rombel)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola kelas, tingkat, penetapan wali kelas, dan kapasitas siswa.</p>
        </div>
        <a href="{{ route('master.classes.create') }}" 
           class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Rombel Baru
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Rombel</th>
                        <th class="py-3 px-4">Tingkat</th>
                        <th class="py-3 px-4">Tahun Ajaran</th>
                        <th class="py-3 px-4">Wali Kelas</th>
                        <th class="py-3 px-4">Jumlah Siswa / Kapasitas</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classes as $c)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $c->name }}
                        </td>
                        <td class="py-3 px-4">
                            Kelas {{ $c->grade?->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-500">
                            {{ $c->academicYear?->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-800">
                            {{ $c->homeroomTeacher?->formatted_name ?? 'Belum ditentukan' }}
                        </td>
                        <td class="py-3 px-4 font-mono font-medium">
                            <span class="text-emerald-700 font-bold">{{ $c->students_count }}</span> / {{ $c->capacity }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $c->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="{{ route('master.classes.edit', $c) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold">Ubah</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Belum ada rombongan belajar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($classes->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $classes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
