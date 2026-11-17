@extends('layouts.app')

@section('title', 'Buku Catatan Kedisiplinan & Prestasi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kedisiplinan & Prestasi Siswa</h1>
            <p class="text-sm text-slate-500 mt-1">Pencatatan kasus pelanggaran, penghargaan prestasi, dan tindak lanjut pembinaan siswa.</p>
        </div>
        <a href="{{ route('discipline.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Kejadian Baru
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider block">Total Pelanggaran Disiplin</span>
            <div class="text-2xl font-bold text-rose-700 mt-2">{{ $stats['total_violations'] }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Tercatat di sistem</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">Prestasi & Apresiasi</span>
            <div class="text-2xl font-bold text-emerald-700 mt-2">{{ $stats['total_achievements'] }}</div>
            <span class="text-xs text-emerald-600 mt-1 block">Poin penghargaan karakter</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider block">Kasus Perlu Penanganan</span>
            <div class="text-2xl font-bold text-amber-700 mt-2">{{ $stats['pending_resolution'] }}</div>
            <span class="text-xs text-amber-500 mt-1 block">Status belum resolved</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('discipline.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Catatan</label>
                <select name="type" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
                    <option value="">Semua (Pelanggaran & Prestasi)</option>
                    <option value="violation" {{ $type === 'violation' ? 'selected' : '' }}>Pelanggaran</option>
                    <option value="achievement" {{ $type === 'achievement' ? 'selected' : '' }}>Prestasi</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Penanganan</label>
                <select name="status" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
                    <option value="">Semua Status</option>
                    <option value="reported" {{ $status === 'reported' ? 'selected' : '' }}>Dilaporkan</option>
                    <option value="processed" {{ $status === 'processed' ? 'selected' : '' }}>Dalam Penanganan</option>
                    <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Terselesaikan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Siswa</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama siswa atau NIS..." class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                    Filter
                </button>
                <a href="{{ route('discipline.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-3 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Kategori & Poin</th>
                        <th class="py-3 px-4">Deskripsi Kejadian</th>
                        <th class="py-3 px-4">Status & Tindak Lanjut</th>
                        <th class="py-3 px-4">Pelapor</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($records as $rec)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $rec->date->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $rec->student->full_name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">NIS: {{ $rec->student->nis }} &bull; {{ $rec->student->currentClass?->name ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $rec->category->type_badge }}">
                                {{ $rec->category->name }}
                            </span>
                            <span class="text-[11px] font-mono font-bold block mt-0.5 {{ $rec->category->type === 'achievement' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $rec->points_awarded > 0 ? '+' : '' }}{{ $rec->points_awarded }} Poin
                            </span>
                        </td>
                        <td class="py-3 px-4 max-w-xs text-slate-700 leading-relaxed">
                            {{ $rec->description }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $rec->status_badge }}">
                                {{ $rec->status_label }}
                            </span>
                            @if($rec->action_taken)
                                <span class="text-[11px] text-slate-500 block mt-1 italic">{{ $rec->action_taken }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $rec->reporter->name }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <form method="POST" action="{{ route('discipline.update-status', $rec) }}" class="inline-flex items-center gap-1">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="text-[11px] border-slate-300 rounded px-1.5 py-1 bg-white">
                                    <option value="reported" {{ $rec->status === 'reported' ? 'selected' : '' }}>Dilaporkan</option>
                                    <option value="processed" {{ $rec->status === 'processed' ? 'selected' : '' }}>Diproses</option>
                                    <option value="resolved" {{ $rec->status === 'resolved' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Belum ada catatan kedisiplinan atau prestasi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
