@extends('layouts.app')

@section('title', 'Layanan Bimbingan Konseling (BK)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Bimbingan Konseling (BK)</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                    Sangat Rahasia
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Layanan konseling pribadi, sosial, kesulitan belajar, dan pendampingan karakter siswa madrasah.</p>
        </div>
        <a href="{{ route('counseling.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl shadow-xs transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Jadwalkan Sesi Konseling
        </a>
    </div>

    <!-- Confidentiality Alert -->
    <div class="bg-rose-50/70 border border-rose-200 rounded-2xl p-4 text-rose-950 text-xs sm:text-sm flex gap-3">
        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <div>
            <strong class="font-semibold block mb-0.5">Kode Etik Kerahasiaan Bimbingan Konseling:</strong>
            <p class="text-xs text-rose-800 leading-relaxed">
                Seluruh rekaman kasus, latar belakang masalah, dan rekomendasi bimbingan hanya dapat diakses oleh Guru BK dan pimpinan berwenang. Catatan ini diisolasi dari laporan umum, buku nilai, maupun portal orang tua.
            </p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Sesi Tercatat</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider block">Sedang Berjalan</span>
            <div class="text-2xl font-bold text-blue-700 mt-2">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider block">Perlu Tindak Lanjut</span>
            <div class="text-2xl font-bold text-amber-700 mt-2">{{ $stats['follow_up_needed'] }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">Selesai / Tuntas</span>
            <div class="text-2xl font-bold text-emerald-700 mt-2">{{ $stats['completed'] }}</div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Tanggal Sesi</th>
                        <th class="py-3 px-4">Siswa Konseli</th>
                        <th class="py-3 px-4">Kategori Bimbingan</th>
                        <th class="py-3 px-4">Guru BK Konselor</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tindak Lanjut</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($sessions as $s)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $s->session_date->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $s->student->full_name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $s->student->nis }} &bull; {{ $s->student->currentClass?->name ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700">
                            {{ $s->category_label }}
                        </td>
                        <td class="py-3 px-4 text-emerald-800 font-medium">
                            {{ $s->counselor->user->name }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $s->status_badge }}">
                                {{ $s->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $s->follow_up_date ? $s->follow_up_date->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('counseling.show', $s) }}" class="inline-flex items-center px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition shadow-xs">
                                Buka Berkas
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Belum ada sesi konseling yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $sessions->links() }}
        </div>
    </div>
</div>
@endsection
