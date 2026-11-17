@extends('layouts.app')

@section('title', 'Detail Sesi Ibadah')

@section('content')
<div class="space-y-6">
    <!-- Session Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $session->status_badge }}">
                        {{ $session->status_label }}
                    </span>
                    <span class="text-xs font-medium text-slate-500">
                        Gelombang {{ $session->wave_number }} &bull; {{ $session->location?->name ?? 'Lokasi Sekolah' }}
                    </span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $session->type?->name }}</h1>
                <p class="text-xs text-slate-500">
                    Pelaksanaan: <strong class="text-slate-800">{{ $session->date->isoFormat('dddd, D MMMM Y') }}</strong> &bull;
                    Dibuat oleh: <span class="text-slate-700 font-medium">{{ $session->creator?->name }}</span>
                    @if($session->verified_by)
                        &bull; Diverifikasi final oleh: <strong class="text-emerald-700">{{ $session->verifier?->name }}</strong> ({{ $session->verified_at?->format('H:i') }})
                    @endif
                </p>
                @if($session->notes)
                    <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 max-w-2xl">
                        <em>Catatan:</em> {{ $session->notes }}
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('worship.sessions.display-qr', $session) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Buka Layar QR Proyektor
                </a>

                <a href="{{ route('worship.sessions.scan', $session) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Scanner Kamera
                </a>

                @if($session->status !== 'verified' && auth()->user()->hasAnyRole(['super_admin', 'admin', 'petugas_keagamaan']))
                <form method="POST" action="{{ route('worship.sessions.finalize', $session) }}" onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi sesi ini? Seluruh peserta yang belum scan/konfirmasi akan otomatis ditandai Tidak Hadir.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Finalisasi Sesi
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Metrics Chips -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-7 gap-3 mt-6 pt-6 border-t border-slate-100">
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                <span class="text-[11px] font-semibold text-slate-500 uppercase block">Total Peserta</span>
                <span class="text-xl font-bold text-slate-900 mt-1 block">{{ $stats['total'] }}</span>
            </div>
            <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100 text-center">
                <span class="text-[11px] font-semibold text-emerald-700 uppercase block">Hadir</span>
                <span class="text-xl font-bold text-emerald-800 mt-1 block">{{ $stats['present'] }}</span>
            </div>
            <div class="bg-orange-50 p-3 rounded-xl border border-orange-100 text-center">
                <span class="text-[11px] font-semibold text-orange-700 uppercase block">Masbuk</span>
                <span class="text-xl font-bold text-orange-800 mt-1 block">{{ $stats['late'] }}</span>
            </div>
            <div class="bg-amber-50 p-3 rounded-xl border border-amber-100 text-center">
                <span class="text-[11px] font-semibold text-amber-700 uppercase block">Belum Scan</span>
                <span class="text-xl font-bold text-amber-800 mt-1 block">{{ $stats['pending'] }}</span>
            </div>
            <div class="bg-purple-50 p-3 rounded-xl border border-purple-100 text-center">
                <span class="text-[11px] font-semibold text-purple-700 uppercase block">Dispensasi</span>
                <span class="text-xl font-bold text-purple-800 mt-1 block">{{ $stats['exempt'] }}</span>
            </div>
            <div class="bg-blue-50 p-3 rounded-xl border border-blue-100 text-center">
                <span class="text-[11px] font-semibold text-blue-700 uppercase block">Izin</span>
                <span class="text-xl font-bold text-blue-800 mt-1 block">{{ $stats['excused'] }}</span>
            </div>
            <div class="bg-rose-50 p-3 rounded-xl border border-rose-100 text-center">
                <span class="text-[11px] font-semibold text-rose-700 uppercase block">Tidak Hadir</span>
                <span class="text-xl font-bold text-rose-800 mt-1 block">{{ $stats['absent'] }}</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('worship.sessions.show', $session) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter Rombel</label>
                <select name="class_id" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
                    <option value="">Semua Rombel Terjadwal</option>
                    @foreach($sessionClasses as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter Status</label>
                <select name="status" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
                    <option value="">Semua Status Peserta</option>
                    <option value="pending_verification" {{ $status == 'pending_verification' ? 'selected' : '' }}>Menunggu Verifikasi / Belum Scan</option>
                    <option value="present" {{ $status == 'present' ? 'selected' : '' }}>Hadir Berjamaah</option>
                    <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Masbuk / Terlambat</option>
                    <option value="exempt" {{ $status == 'exempt' ? 'selected' : '' }}>Dispensasi</option>
                    <option value="excused" {{ $status == 'excused' ? 'selected' : '' }}>Izin</option>
                    <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Nama / NIS</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau NIS..." class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 border">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                    Terapkan
                </button>
                <a href="{{ route('worship.sessions.show', $session) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium py-2 px-3 rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Participants Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden" x-data="{
        selectedParticipants: [],
        selectAll: false,
        toggleAll() {
            if (this.selectAll) {
                this.selectedParticipants = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
            } else {
                this.selectedParticipants = [];
            }
        }
    }">
        <!-- Bulk Action Header -->
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-bold text-slate-800">Daftar Peserta Terkunci (Denominator)</h3>
                <span class="text-xs text-slate-500">({{ $participants->total() }} siswa ditemukan)</span>
            </div>

            <!-- Bulk verification helper -->
            <form method="POST" action="{{ route('worship.sessions.verify', $session) }}" class="flex items-center gap-2" x-show="selectedParticipants.length > 0">
                @csrf
                <span class="text-xs text-slate-600 font-medium" x-text="`${selectedParticipants.length} siswa dipilih:`"></span>
                <template x-for="id in selectedParticipants" :key="id">
                    <input type="hidden" :name="`bulk_statuses[${id}]`" id="bulk-input" :value="$el.closest('form').querySelector('select[name=bulk_status]').value">
                </template>
                <select name="bulk_status" class="text-xs border-slate-300 rounded-lg px-2.5 py-1.5 border bg-white" @change="
                    let val = $el.value;
                    document.querySelectorAll('input[id=bulk-input]').forEach(el => el.value = val);
                ">
                    <option value="present">Tandai Hadir</option>
                    <option value="late">Tandai Masbuk</option>
                    <option value="excused">Tandai Izin</option>
                    <option value="exempt">Tandai Dispensasi</option>
                    <option value="absent">Tandai Tidak Hadir</option>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-xs">
                    Simpan Perubahan Terpilih
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4 w-10 text-center">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        </th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Rombel</th>
                        <th class="py-3 px-4">Bukti Scan / Sumber</th>
                        <th class="py-3 px-4">Status Verifikasi</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4 text-right">Aksi Konfirmasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($participants as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center">
                            <input type="checkbox" value="{{ $p->id }}" x-model="selectedParticipants" class="row-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $p->student->full_name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">NIS: {{ $p->student->nis }}</div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700">
                            {{ $p->schoolClass?->name }}
                        </td>
                        <td class="py-3 px-4">
                            @if($p->scanRecord)
                                <div class="flex items-center text-emerald-700 font-medium">
                                    <svg class="w-3.5 h-3.5 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ $p->scanRecord->scanned_at->format('H:i:s') }}
                                </div>
                                <span class="text-[10px] text-slate-400 block">{{ $p->scanRecord->source }}</span>
                            @else
                                <span class="text-slate-400 italic">Belum scan</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $p->status_badge }}">
                                {{ $p->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 max-w-xs truncate text-slate-600">
                            {{-- Strict privacy function hides female health notes from general users --}}
                            {{ $p->getSafeNotesForUser(auth()->user()) }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1" x-data="{ openModal: false }">
                                <form method="POST" action="{{ route('worship.sessions.verify', $session) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="hidden" name="participant_id" value="{{ $p->id }}">
                                    @if($p->status !== 'present')
                                    <button type="submit" name="status" value="present" title="Verifikasi Hadir" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    @endif
                                    @if($p->status !== 'late')
                                    <button type="submit" name="status" value="late" title="Tandai Masbuk" class="p-1.5 text-orange-600 hover:bg-orange-50 rounded-lg transition border border-orange-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    @endif
                                    @if($p->status !== 'exempt')
                                    <button type="submit" name="status" value="exempt" title="Tandai Dispensasi" class="p-1.5 text-purple-600 hover:bg-purple-50 rounded-lg transition border border-purple-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Tidak ada peserta yang cocok dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $participants->links() }}
        </div>
    </div>
</div>
@endsection
