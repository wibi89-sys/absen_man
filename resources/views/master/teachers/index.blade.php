@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Kelola Data Guru & Tendik</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar tenaga pendidik, NIP, gelar akademik, dan status keaktifan.</p>
        </div>
        <a href="{{ route('master.teachers.create') }}" 
           class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Guru Baru
        </a>
    </div>

    <!-- Filter Card -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('master.teachers.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari berdasarkan NIP atau Nama Guru..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                    Cari
                </button>
                <a href="{{ route('master.teachers.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
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
                        <th class="py-3 px-4">NIP</th>
                        <th class="py-3 px-4">Nama & Gelar</th>
                        <th class="py-3 px-4">No. Telepon</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-medium text-slate-900">
                            {{ $teacher->nip ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-900">{{ $teacher->formatted_name }}</div>
                            @if($teacher->user)
                                <div class="text-[10px] text-slate-400">Akun: {{ $teacher->user->username ?? $teacher->user->email }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-500">
                            {{ $teacher->phone ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $teacher->status == 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($teacher->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="{{ route('master.teachers.edit', $teacher) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold">Ubah</a>
                            <form action="{{ route('master.teachers.destroy', $teacher) }}" method="POST" class="inline" onsubmit="return confirm('Nonaktifkan data guru ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold">Nonaktifkan</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400">Belum ada data guru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachers->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $teachers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
