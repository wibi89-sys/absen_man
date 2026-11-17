@extends('layouts.app')

@section('title', 'Dispensasi Ibadah & Halangan Syar\'i')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dispensasi Ibadah Khusus</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                    Kerahasiaan Tinggi
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Pencatatan dispensasi syar'i (halangan siswi) dan kondisi medis. Data ini diisolasi dan tidak dipublikasikan ke rekap umum atau akun orang tua siswa lain.</p>
        </div>
        <a href="{{ route('worship.exemptions.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl shadow-xs transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Dispensasi Baru
        </a>
    </div>

    <!-- Confidentiality Privacy Policy Alert -->
    <div class="bg-purple-50/70 border border-purple-200 rounded-2xl p-4 text-purple-950 text-sm flex gap-3">
        <svg class="w-5 h-5 text-purple-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <div>
            <strong class="font-semibold block mb-0.5">Prinsip Etika & Kerahasiaan Sesuai Master Spec:</strong>
            <p class="text-xs text-purple-800 leading-relaxed">
                Kondisi biologis siswa putri (haid/nifas) maupun riwayat medis tidak boleh dijadikan alat perangkingan moral, tidak boleh dicantumkan pada rekap kelas umum, dan hanya dapat dikelola oleh Petugas Keagamaan, Konselor Guru BK, atau Administrator. Pada rekap publik, status siswa hanya berlabel <strong>"Dispensasi"</strong>.
            </p>
        </div>
    </div>

    <!-- Macro Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Dispensasi Aktif Hari Ini</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ number_format($activeCount) }}</div>
            <span class="text-xs text-slate-400 mt-1 block">Otomatis masuk rekap dispensasi</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider block">Halangan Syar'i (Siswi)</span>
            <div class="text-2xl font-bold text-purple-700 mt-2">{{ number_format($shariaCount) }}</div>
            <span class="text-xs text-purple-500 mt-1 block">Berlaku selama periode terdaftar</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider block">Sakit / Medis</span>
            <div class="text-2xl font-bold text-blue-700 mt-2">{{ number_format($sickCount) }}</div>
            <span class="text-xs text-blue-500 mt-1 block">Dispensasi resmi kesehatan</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('worship.exemptions.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status Periode</label>
                <select name="status" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Sedang Aktif Hari Ini</option>
                    <option value="past" {{ $status == 'past' ? 'selected' : '' }}>Riwayat Selesai</option>
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Riwayat</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Dispensasi</label>
                <select name="category" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
                    <option value="">Semua Kategori</option>
                    <option value="sharia_halangan" {{ $category == 'sharia_halangan' ? 'selected' : '' }}>Halangan Syar'i</option>
                    <option value="sick" {{ $category == 'sick' ? 'selected' : '' }}>Sakit</option>
                    <option value="emergency" {{ $category == 'emergency' ? 'selected' : '' }}>Kondisi Darurat</option>
                    <option value="dispensation" {{ $category == 'dispensation' ? 'selected' : '' }}>Dispensasi Resmi</option>
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
                <a href="{{ route('worship.exemptions.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-3 rounded-lg transition">
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
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Rombel</th>
                        <th class="py-3 px-4">Kategori Alasan</th>
                        <th class="py-3 px-4">Rentang Tanggal</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Dicatat Oleh</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($exemptions as $ex)
                    @php
                        $isActive = now()->between($ex->start_date, $ex->end_date);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $ex->student->full_name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">NIS: {{ $ex->student->nis }}</div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700">
                            {{ $ex->student->currentClass?->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ex->reason_category === 'sharia_halangan' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $ex->category_label }}
                            </span>
                            @if($ex->notes)
                                <span class="text-[11px] text-slate-500 block mt-1 italic max-w-xs truncate">{{ $ex->notes }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-medium text-slate-800">{{ $ex->start_date->format('d M Y') }}</span>
                            <span class="text-slate-400">s/d</span>
                            <span class="font-medium text-slate-800">{{ $ex->end_date->format('d M Y') }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @if($isActive)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                    Aktif Hari Ini
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                    Kedaluwarsa
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $ex->approver?->name ?? 'Petugas' }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <form method="POST" action="{{ route('worship.exemptions.destroy', $ex) }}" onsubmit="return confirm('Hapus catatan dispensasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition border border-rose-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Belum ada catatan dispensasi pada kriteria ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $exemptions->links() }}
        </div>
    </div>
</div>
@endsection
