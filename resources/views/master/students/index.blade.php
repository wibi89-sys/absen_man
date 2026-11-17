@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Kelola Data Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar siswa terdaftar, status rombel aktif, dan biodata induk.</p>
        </div>
        <a href="{{ route('master.students.create') }}" 
           class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Siswa Baru
        </a>
    </div>

    <!-- Filter Card -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('master.students.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari berdasarkan NIS, NISN, atau Nama Siswa..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <select name="class_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Rombel / Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                    Filter
                </button>
                <a href="{{ route('master.students.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">NIS / NISN</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Rombel</th>
                        <th class="py-3 px-4">JK</th>
                        <th class="py-3 px-4">Tgl Masuk</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono">
                            <div class="font-bold text-slate-900">{{ $student->nis }}</div>
                            <div class="text-[10px] text-slate-400">{{ $student->nisn ?? 'NISN -' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-900">{{ $student->full_name }}</div>
                            @if($student->nickname)
                                <div class="text-[10px] text-slate-400">Panggilan: {{ $student->nickname }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 font-semibold text-[11px] border border-emerald-100">
                                {{ $student->currentClass?->name ?? 'Belum ada rombel' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            {{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </td>
                        <td class="py-3 px-4 text-slate-500">
                            {{ $student->enrollment_date?->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $student->status == 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="{{ route('master.students.edit', $student) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold">Ubah</a>
                            <form action="{{ route('master.students.destroy', $student) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengarsipkan data siswa ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold">Arsipkan</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Tidak ditemukan data siswa yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
