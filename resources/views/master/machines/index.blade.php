@extends('layouts.app')

@section('title', 'Manajemen Mesin Absensi Biometrik LAN')

@section('content')
<div class="space-y-6" x-data="{ showAddModal: false, testingId: null, testResult: null }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Mesin Absensi Biometrik LAN</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Integrasi mesin absensi sidik jari & RFID (contoh: Solution X100C / ZKTeco) via jaringan kabel LAN atau protokol ADMS.
            </p>
        </div>
        <button type="button" @click="showAddModal = true" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Daftarkan Mesin LAN Baru</span>
        </button>
    </div>

    <!-- Info Guide Banner -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 text-white shadow-sm space-y-3">
        <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs uppercase tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Panduan Koneksi Mesin Solution X100C / ZKTeco</span>
        </div>
        <p class="text-xs text-slate-300 leading-relaxed">
            Mesin absensi Solution X100C yang terhubung ke switch/router madrasah dapat mengirim log kehadiran siswa secara otomatis ke aplikasi MADRASAH ONE melalui protokol <strong>ADMS Cloud Server</strong>:
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-white/5 border border-white/10 font-mono">
                <span class="text-slate-400 block text-[10px]">URL Server ADMS Mesin:</span>
                <span class="text-emerald-300 font-bold">http://[IP-KOMPUTER-SERVER]:80/api/machine/cdata</span>
            </div>
            <div class="p-3 rounded-xl bg-white/5 border border-white/10 font-mono">
                <span class="text-slate-400 block text-[10px]">Port Default & PIN Siswa:</span>
                <span class="text-emerald-300 font-bold">Port 4370 &bull; PIN Mesin = NIS Siswa</span>
            </div>
        </div>
    </div>

    <!-- Registered Machines Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Daftar Mesin Absensi Terdaftar</h3>
            <span class="text-xs text-slate-500">{{ $machines->count() }} perangkat</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-600 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="py-3 px-4">Nama Perangkat</th>
                        <th class="py-3 px-4">IP Address : Port</th>
                        <th class="py-3 px-4">Lokasi</th>
                        <th class="py-3 px-4">Protokol</th>
                        <th class="py-3 px-4">Status Terakhir</th>
                        <th class="py-3 px-4 text-center">Aksi Pengujian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($machines as $m)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $m->name }}
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-emerald-800">
                            {{ $m->ip_address }}:{{ $m->port }}
                        </td>
                        <td class="py-3 px-4 text-slate-700">{{ $m->location_name ?? 'Gerbang / Pos Depan' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ strtoupper($m->protocol) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($m->last_status === 'online')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Online
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Belum Sync
                                </span>
                            @endif
                            @if($m->last_sync_at)
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $m->last_sync_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button type="button" @click="testPing({{ $m->id }})" :disabled="testingId === {{ $m->id }}"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-300 transition">
                                <span x-show="testingId !== {{ $m->id }}">Tes Koneksi Ping</span>
                                <span x-show="testingId === {{ $m->id }}" style="display: none;">Mengetes...</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400">
                            Belum ada mesin absensi LAN yang didaftarkan. Klik tombol di atas untuk mendaftarkan mesin Solution X100C.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Mesin -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200" @click.away="showAddModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base">Daftarkan Mesin Absensi LAN Baru</h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('master.machines.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Perangkat</label>
                    <input type="text" name="name" required placeholder="Contoh: Solution X100C Gerbang Depan"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">IP Address di Jaringan LAN</label>
                        <input type="text" name="ip_address" required placeholder="192.168.1.201"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 font-mono text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Port Komunikasi</label>
                        <input type="number" name="port" required value="4370"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 font-mono text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Protokol</label>
                        <select name="protocol" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs">
                            <option value="adms_http">ADMS Web Push (HTTP)</option>
                            <option value="socket_udp">Socket UDP (Port 4370)</option>
                            <option value="socket_tcp">Socket TCP</option>
                            <option value="rest_api">REST Webhook API</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Pemasangan</label>
                        <input type="text" name="location_name" placeholder="Pos Satpam Gerbang Depan"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold">
                        Simpan Mesin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function testPing(id) {
    const el = Alpine.$data(document.querySelector('[x-data]'));
    el.testingId = id;

    fetch(`/master/machines/${id}/test-connection`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        window.location.reload();
    })
    .catch(err => {
        alert("Gagal melakukan pengetesan: " + err);
    })
    .finally(() => {
        el.testingId = null;
    });
}
</script>
@endsection
