@extends('layouts.app')

@section('title', 'Sesi Monitoring Ibadah')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-base sm:text-lg font-bold tracking-tight text-slate-900">Monitoring Ibadah Terpadu</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pengelolaan jadwal gelombang salat (Zuhur & Asar), scan QR dinamis, dan verifikasi kehadiran.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'petugas_keagamaan']))
            <a href="{{ route('worship.sessions.create') }}" class="inline-flex items-center px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg shadow-xs transition">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Sesi Ibadah
            </a>
            @endif
        </div>
    </div>

    <!-- Macro Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wider block">Peserta Terkunci</span>
            <div class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 mt-1">{{ number_format($totalParticipants) }}</div>
            <span class="text-[11px] text-slate-400 mt-0.5 block truncate">Denominator rombel aktif</span>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-emerald-700 uppercase tracking-wider block">Hadir Berjamaah</span>
            <div class="text-xl sm:text-2xl font-bold tracking-tight text-emerald-700 mt-1">{{ number_format($totalPresent) }}</div>
            <span class="text-[11px] text-emerald-600 mt-0.5 block truncate">Scan terkonfirmasi</span>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-purple-700 uppercase tracking-wider block">Dispensasi Syar'i / Izin</span>
            <div class="text-xl sm:text-2xl font-bold tracking-tight text-purple-700 mt-1">{{ number_format($totalExempt) }}</div>
            <span class="text-[11px] text-purple-500 mt-0.5 block truncate">Privasi terlindungi</span>
        </div>
        <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-medium text-amber-700 uppercase tracking-wider block">Menunggu Verifikasi</span>
            <div class="text-xl sm:text-2xl font-bold tracking-tight text-amber-700 mt-1">{{ number_format($totalPending) }}</div>
            <span class="text-[11px] text-amber-500 mt-0.5 block truncate">Perlu konfirmasi petugas</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('worship.sessions.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Sesi</label>
                <input type="date" name="date" value="{{ $date }}" class="w-full text-xs border-slate-300 rounded-lg px-2.5 py-1.5 focus:ring-emerald-500/20 focus:border-emerald-600 border transition">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Jenis Ibadah</label>
                <select name="worship_type_id" class="w-full text-xs border-slate-300 rounded-lg px-2.5 py-1.5 focus:ring-emerald-500/20 focus:border-emerald-600 border transition">
                    <option value="">Semua Ibadah</option>
                    @foreach($worshipTypes as $type)
                        <option value="{{ $type->id }}" {{ $typeId == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status Sesi</label>
                <select name="status" class="w-full text-xs border-slate-300 rounded-lg px-2.5 py-1.5 focus:ring-emerald-500/20 focus:border-emerald-600 border transition">
                    <option value="">Semua Status</option>
                    <option value="scheduled" {{ $status == 'scheduled' ? 'selected' : '' }}>Dijadwalkan</option>
                    <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>Sedang Berlangsung</option>
                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="verified" {{ $status == 'verified' ? 'selected' : '' }}>Terverifikasi Final</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-xs font-medium py-1.5 px-3 rounded-lg transition">
                    Filter
                </button>
                <a href="{{ route('worship.sessions.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium py-1.5 px-3 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Sesi List -->
    <div class="space-y-3">
        @forelse($sessions as $session)
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 hover:border-emerald-300/80 transition">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium {{ $session->status_badge }}">
                            {{ $session->status_label }}
                        </span>
                        <span class="text-[11px] font-medium text-slate-500">
                            Gelombang {{ $session->wave_number }} &bull; {{ $session->location?->name ?? 'Lokasi Ditentukan' }}
                        </span>
                    </div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">{{ $session->type?->name }}</h2>
                    <p class="text-xs text-slate-500">
                        Tanggal: <strong class="text-slate-700 font-medium">{{ $session->date->isoFormat('dddd, D MMMM Y') }}</strong> &bull;
                        Dibuat: <span class="text-slate-700">{{ $session->creator?->name ?? 'Sistem' }}</span>
                        @if($session->verified_by)
                            &bull; Diverifikasi: <span class="text-emerald-700 font-medium">{{ $session->verifier?->name }}</span>
                        @endif
                    </p>
                </div>

                <!-- Stats in card -->
                <div class="flex items-center gap-4 py-1.5 px-3 bg-slate-50 rounded-lg border border-slate-100">
                    <div class="text-center">
                        <span class="text-[10px] text-slate-500 block">Peserta</span>
                        <span class="text-xs sm:text-sm font-bold text-slate-800">{{ $session->participants_count }}</span>
                    </div>
                    <div class="w-px h-6 bg-slate-200"></div>
                    <div class="text-center">
                        <span class="text-[10px] text-emerald-600 block">Scan</span>
                        <span class="text-xs sm:text-sm font-bold text-emerald-700">{{ $session->attendances_count }}</span>
                    </div>
                    <div class="w-px h-6 bg-slate-200"></div>
                    <div class="text-center">
                        <span class="text-[10px] text-slate-500 block">Tingkat Kehadiran</span>
                        <span class="text-xs sm:text-sm font-bold text-slate-900">
                            {{ $session->participants_count > 0 ? round(($session->attendances_count / $session->participants_count) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                    <a href="{{ route('worship.sessions.display-qr', $session) }}" target="_blank" class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-medium rounded-lg transition border border-indigo-200/60">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Layar QR
                    </a>

                    <a href="{{ route('worship.sessions.scan', $session) }}" class="inline-flex items-center px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-medium rounded-lg transition border border-emerald-200/60">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Scanner
                    </a>

                    <a href="{{ route('worship.sessions.show', $session) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-medium rounded-lg transition shadow-2xs">
                        Detail & Verifikasi
                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-slate-200/80 p-8 text-center shadow-xs">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-900">Belum Ada Sesi Ibadah</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Tidak ditemukan sesi ibadah untuk kriteria filter ini.</p>
            @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'petugas_keagamaan']))
            <div class="mt-4">
                <a href="{{ route('worship.sessions.create') }}" class="inline-flex items-center px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition shadow-xs">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Sesi Ibadah
                </a>
            </div>
            @endif
        </div>
        @endforelse

        <div class="mt-4">
            {{ $sessions->links() }}
        </div>
    </div>
</div>
@endsection
