@extends('layouts.app')

@section('title', 'Scanner Presensi Ibadah')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Scanner Presensi Ibadah</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $session->type?->name }} &bull; Gelombang {{ $session->wave_number }} &bull; {{ $session->location?->name ?? 'Masjid Utama' }}
            </p>
        </div>
        <a href="{{ route('worship.sessions.show', $session) }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <!-- Scanner Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden" x-data="{
        mode: 'camera', // 'camera' or 'manual'
        manualNis: '',
        isSubmitting: false,
        lastResult: null,

        playBeep(success) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = success ? 'sine' : 'square';
                osc.frequency.value = success ? 880 : 220; // 880Hz high beep, 220Hz low error buzz
                gain.gain.setValueAtTime(0.1, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + (success ? 0.15 : 0.3));
            } catch (e) {
                console.log(e);
            }
        },

        submitScan(token, identifier, source) {
            if (this.isSubmitting) return;
            this.isSubmitting = true;

            fetch('{{ route('worship.sessions.process-scan', $session) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    token: token,
                    identifier: identifier,
                    source: source
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                this.isSubmitting = false;
                if (status === 200 && body.success) {
                    this.playBeep(true);
                    this.lastResult = {
                        success: true,
                        message: body.message,
                        data: body.data
                    };
                } else {
                    this.playBeep(false);
                    this.lastResult = {
                        success: false,
                        message: body.message || 'Presensi gagal diproses.'
                    };
                }
            })
            .catch(err => {
                this.isSubmitting = false;
                this.playBeep(false);
                this.lastResult = {
                    success: false,
                    message: 'Terjadi kesalahan jaringan atau koneksi terputus.'
                };
            });
        }
    }">
        <!-- Tab Selector -->
        <div class="flex border-b border-slate-200 bg-slate-50/50">
            <button @click="mode = 'camera'" :class="mode === 'camera' ? 'border-b-2 border-emerald-600 text-emerald-700 font-bold bg-white' : 'text-slate-600 hover:text-slate-900'" class="flex-1 py-3 text-center text-xs font-semibold transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Kamera QR Mandiri (Siswa)</span>
            </button>
            <button @click="mode = 'manual'" :class="mode === 'manual' ? 'border-b-2 border-emerald-600 text-emerald-700 font-bold bg-white' : 'text-slate-600 hover:text-slate-900'" class="flex-1 py-3 text-center text-xs font-semibold transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Input NIS / Barcode (Fallback Petugas)</span>
            </button>
        </div>

        <div class="p-6">
            <!-- Mode 1: Camera Scanner -->
            <div x-show="mode === 'camera'" class="space-y-4">
                <p class="text-xs text-slate-500 text-center">
                    Arahkan kamera ke layar proyektor yang menampilkan QR Dinamis aktif.
                </p>

                <!-- Camera viewport -->
                <div id="reader" class="w-full max-w-sm mx-auto overflow-hidden rounded-2xl border-2 border-slate-200"></div>
            </div>

            <!-- Mode 2: Manual NIS or Barcode Officer Entry -->
            <div x-show="mode === 'manual'" class="space-y-4 max-w-sm mx-auto py-2">
                <div class="text-center">
                    <h3 class="text-sm font-bold text-slate-800">Input Manual Siswa Tanpa Perangkat</h3>
                    <p class="text-xs text-slate-500 mt-1">Masukkan Nomor Induk Siswa (NIS) untuk mencatat kehadiran siswa secara manual oleh petugas.</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Induk Siswa (NIS)</label>
                        <input type="text" x-model="manualNis" placeholder="Contoh: 20261001" class="w-full text-base font-mono border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border text-center font-bold tracking-wider" @keydown.enter="
                            if (manualNis) {
                                // For officer fallback, fetch active token
                                fetch('{{ route('worship.sessions.token-json', $session) }}')
                                    .then(r => r.json())
                                    .then(data => {
                                        submitScan(data.token, manualNis, 'manual_officer');
                                        manualNis = '';
                                    });
                            }
                        ">
                    </div>

                    <button type="button" @click="
                        if (manualNis) {
                            fetch('{{ route('worship.sessions.token-json', $session) }}')
                                .then(r => r.json())
                                .then(data => {
                                    submitScan(data.token, manualNis, 'manual_officer');
                                    manualNis = '';
                                });
                        }
                    " class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        Verifikasi Kehadiran Siswa
                    </button>
                </div>
            </div>

            <!-- Real-time Scan Result Toast/Card -->
            <div x-show="lastResult !== null" class="mt-6 transition-all duration-300" x-cloak>
                <!-- Success State -->
                <div x-show="lastResult && lastResult.success" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-emerald-900" x-text="lastResult ? lastResult.message : ''"></h4>
                        <template x-if="lastResult && lastResult.data">
                            <div class="mt-1 text-xs text-emerald-800 space-y-0.5 font-medium">
                                <div>Siswa: <strong x-text="lastResult.data.student_name"></strong> (<span x-text="lastResult.data.nis"></span>)</div>
                                <div>Rombel: <span x-text="lastResult.data.class"></span> &bull; Waktu: <span x-text="lastResult.data.scanned_at"></span> WIB</div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Error State -->
                <div x-show="lastResult && !lastResult.success" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-rose-900">Presensi Ditolak</h4>
                        <p class="text-xs text-rose-700 mt-0.5" x-text="lastResult ? lastResult.message : ''"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.Html5QrcodeScanner !== 'undefined') {
            const scanner = new window.Html5QrcodeScanner("reader", {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true
            }, false);

            scanner.render(function (decodedText) {
                // Pass decoded dynamic token and current user's student identifier (or user ID)
                const currentStudentId = "{{ auth()->user()->student?->id ?? auth()->id() }}";
                const alpineEl = document.querySelector('[x-data]');
                if (alpineEl && alpineEl._x_dataStack) {
                    alpineEl._x_dataStack[0].submitScan(decodedText, currentStudentId, 'qr_student_camera');
                }
            }, function (error) {
                // Ignore ongoing scanning frames
            });
        }
    });
</script>
@endsection
