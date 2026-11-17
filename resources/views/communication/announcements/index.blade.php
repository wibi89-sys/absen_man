@extends('layouts.app')

@section('title', 'Kelola Pengumuman Madrasah')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kelola Pengumuman Madrasah</h1>
            <p class="text-sm text-slate-500 mt-1">Publikasi pengumuman terarah untuk Orang Tua, Siswa, atau Tenaga Pendidik.</p>
        </div>
        <a href="{{ route('communication.announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Pengumuman Baru
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Judul & Isi Pengumuman</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Target Sasaran</th>
                        <th class="py-3 px-4">Tanggal Publikasi</th>
                        <th class="py-3 px-4">Dibuat Oleh</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($announcements as $ann)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 max-w-md">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">{{ $ann->title }}</span>
                                @if($ann->is_pinned)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">PIN</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 line-clamp-2 mt-0.5">{{ $ann->content }}</p>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $ann->category_badge }}">
                                {{ $ann->category_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700 capitalize">
                            {{ $ann->target_role === 'all' ? 'Semua Pengguna' : ($ann->target_role === 'parents' ? 'Orang Tua' : ucfirst($ann->target_role)) }}
                            @if($ann->schoolClass)
                                <span class="text-[10px] text-slate-400 block font-normal">(Kelas {{ $ann->schoolClass->name }})</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $ann->published_at->format('d M Y, H:i') }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $ann->creator->name }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('communication.announcements.edit', $ann) }}" class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition border border-slate-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('communication.announcements.destroy', $ann) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition border border-rose-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            Belum ada pengumuman yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection
