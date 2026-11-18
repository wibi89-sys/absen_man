@extends('layouts.app')

@section('title', 'Dispensasi Ibadah & Halangan Syar\'i')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-base sm:text-lg font-bold tracking-tight text-slate-900">Dispensasi Ibadah Khusus</h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-purple-50 text-purple-700 border border-purple-200/60">
                    Kerahasiaan Tinggi
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Pencatatan dispensasi syar'i (halangan siswi) dan kondisi medis. Data ini diisolasi dan tidak dipublikasikan ke rekap umum.</p>
        </div>
        <a href="{{ route('worship.exemptions.create') }}" class="inline-flex items-center px-3.5 py-1.5 bg-purple-700 hover:bg-purple-800 text-white text-xs font-medium rounded-lg shadow-xs transition shrink-0">
            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Dispensasi Baru
        </a>
    </div>

    <!-- Confidentiality Privacy Policy Alert -->
    <div class="bg-purple-50/70 border border-purple-200/80 rounded-xl p-3.5 text-purple-950 text-xs flex gap-2.5">
        <svg class="w-4 h-4 text-purple-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <div>
            <strong class="font-medium block mb-0.5">Prinsip Etika & Kerahasiaan Sesuai Master Spec:</strong>
            <p class="text-purple-800 leading-relaxed">
                Kondisi biologis siswi (haid/nifas) maupun riwayat medis tidak boleh dijadikan alat perangkingan moral dan hanya dapat dikelola oleh Petugas Keagamaan, Guru BK, atau Administrator. Pada rekap publik, status siswa hanya berlabel <strong>"Dispensasi"</strong>.
            </p>
        </div>
    </div>

    <!-- Macro Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wider block">Total Dispensasi Aktif</span>
            <div class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 mt-1">{{ number_format($activeCount) }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block truncate">Otomatis masuk rekap dispensasi</span>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-purple-700 uppercase tracking-wider block">Halangan Syar'i (Siswi)</span>
            <div class="text-xl sm:text-2xl font-bold tracking-tight text-purple-700 mt-1">{{ number_format($shariaCount) }}</div>
            <span class="text-[11px] text-purple-500 mt-0.5 block truncate">Berlaku selama periode terdaftar</span>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-blue-700 uppercase tracking-wider block">Sakit / Medis</span>
            <div class="text-xl sm:text-2xl font-bold tracking-tight text-blue-700 mt-1">{{ number_format($sickCount) }}</div>
            <span class="text-[11px] text-blue-500 mt-0.5 block truncate">Dispensasi resmi kesehatan</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('worship.exemptions.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status Periode</label>
                <select name="status" class="w-full text-xs border-slate-300 rounded-lg px-2.5 py-1.5 border focus:ring-emerald-500/20 focus:border-emerald-600 transition">
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Sedang Aktif Hari Ini</option>
                    <option value="past" {{ $status == 'past' ? 'selected' : '' }}>Riwayat Selesai</option>
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Riwayat</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Kategori Dispensasi</label>
                <select name="category" class="w-full text-xs border-slate-300 rounded-lg px-2.5 py-1.5 border focus:ring-emerald-500/20 focus:border-emerald-600 transition">
                    <option value="">Semua Kategori</option>
                    <option value="sharia_halangan" {{ $category == 'sharia_halangan' ? 'selected' : '' }}>Halangan Syar'i</option>
                    <option value="sick" {{ $category == 'sick' ? 'selected' : '' }}>Sakit</option>
                    <option value="emergency" {{ $category == 'emergency' ? 'selected' : '' }}>Kondisi Darurat</option>
                    <option value="dispensation" {{ $category == 'dispensation' ? 'selected' : '' }}>Dispensasi Resmi</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Cari Siswa</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama siswa atau NIS..." class="w-full text-xs border-slate-300 rounded-lg px-2.5 py-1.5 border focus:ring-emerald-500/20 focus:border-emerald-600 transition">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-xs font-medium py-1.5 px-3 rounded-lg transition">
                    Filter
                </button>
                <a href="{{ route('worship.exemptions.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium py-1.5 px-3 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs text-slate-600">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-semibold text-slate-700 uppercase tracking-wider">
                        <th class="py-2.5 px-3">Nama Siswa</th>
                        <th class="py-2.5 px-3">Rombel</th>
                        <th class="py-2.5 px-3">Kategori Alasan</th>
                        <th class="py-2.5 px-3">Rentang Tanggal</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Dicatat Oleh</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($exemptions as $ex)
                    @php
                        $isActive = now()->between($ex->start_date, $ex->end_date);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 px-3">
                            <div class="font-semibold text-slate-900 text-xs sm:text-sm">{{ $ex->student->full_name }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">NIS: {{ $ex->student->nis }}</div>
                        </td>
                        <td class="py-2.5 px-3 font-medium text-slate-700">
                            {{ $ex->student->currentClass?->name ?? '-' }}
                        </td>
                        <td class="py-2.5 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium {{ $ex->reason_category === 'sharia_halangan' ? 'bg-purple-50 text-purple-700 border border-purple-200/60' : 'bg-blue-50 text-blue-700 border border-blue-200/60' }}">
                                {{ $ex->category_label }}
                            </span>
                            @if($ex->notes)
                                <span class="text-[10px] text-slate-500 block mt-0.5 italic max-w-xs truncate">{{ $ex->notes }}</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-800">{{ $ex->start_date->format('d M Y') }}</span>
                            <span class="text-slate-400">s/d</span>
                            <span class="font-medium text-slate-800">{{ $ex->end_date->format('d M Y') }}</span>
                        </td>
                        <td class="py-2.5 px-3">
                            @if($isActive)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    Aktif Hari Ini
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-600">
                                    Kedaluwarsa
                                </span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-slate-600">
                            {{ $ex->approver?->name ?? 'Petugas' }}
                        </td>
                        <td class="py-2.5 px-3 text-right">
                            <form method="POST" action="{{ route('worship.exemptions.destroy', $ex) }}" onsubmit="return confirm('Hapus catatan dispensasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-rose-600 hover:bg-rose-50 rounded-md transition border border-rose-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

        <div class="p-3 border-t border-slate-100">
            {{ $exemptions->links() }}
        </div>
    </div>
</div>
@endsection
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
