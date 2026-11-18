@extends('layouts.app')

@section('title', 'Kiosk Pos Gerbang - Scanner Presensi Masuk')

@section('content')
<div class="space-y-5" x-data="gateKiosk()">
    <!-- Top Header & Live Counter -->
    <div class="bg-slate-900 rounded-xl p-4 sm:p-5 text-white border border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-1.5">
                {{ $schoolSetting->school_name }} &bull; Pos Gerbang Kiosk
            </span>
            <h1 class="text-base sm:text-lg font-bold tracking-tight">Kiosk Scanner Presensi Masuk Siswa</h1>
            <p class="text-slate-300 text-xs mt-0.5">
                {{ $schoolSetting->gate_welcome_text ?? 'Mendukung QR Kartu OSIS, Barcode Reader, dan Tap Kartu Pelajar NFC/RFID.' }}
            </p>
        </div>

        <div class="grid grid-cols-3 gap-2 w-full md:w-auto md:flex md:items-center md:gap-3 shrink-0">
            <div class="bg-slate-800/80 rounded-lg p-2 sm:px-3.5 sm:py-2 border border-slate-700/60 text-center">
                <span class="text-[10px] uppercase tracking-wider text-slate-400 block truncate">Total Masuk</span>
                <span class="text-lg sm:text-xl font-bold text-white" x-text="stats.total_in">{{ $stats['total_in'] }}</span>
            </div>
            <div class="bg-slate-800/80 rounded-lg p-2 sm:px-3.5 sm:py-2 border border-slate-700/60 text-center">
                <span class="text-[10px] uppercase tracking-wider text-emerald-400 block truncate">Tepat Waktu</span>
                <span class="text-lg sm:text-xl font-bold text-emerald-400" x-text="stats.present">{{ $stats['present'] }}</span>
            </div>
            <div class="bg-slate-800/80 rounded-lg p-2 sm:px-3.5 sm:py-2 border border-slate-700/60 text-center">
                <span class="text-[10px] uppercase tracking-wider text-amber-400 block truncate">Terlambat</span>
                <span class="text-lg sm:text-xl font-bold text-amber-400" x-text="stats.late">{{ $stats['late'] }}</span>
            </div>
        </div>
    </div>

    <!-- Main Scanner Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left Column: Input Scanner (Tabs: Kamera QR, Barcode Gun, NFC) -->
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-4">
            <!-- Mode Selector Tabs -->
            <div class="grid grid-cols-1 sm:grid-cols-3 p-1 bg-slate-100/90 rounded-lg gap-1">
                <button type="button" @click="setMode('barcode')" 
                        :class="mode === 'barcode' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="w-full py-2 px-2 text-xs rounded-md transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span class="truncate">Barcode Scanner</span>
                </button>
                <button type="button" @click="setMode('camera')" 
                        :class="mode === 'camera' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="w-full py-2 px-2 text-xs rounded-md transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="truncate">Kamera QR OSIS</span>
                </button>
                <button type="button" @click="setMode('nfc')" 
                        :class="mode === 'nfc' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="w-full py-2 px-2 text-xs rounded-md transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="truncate">Tap Kartu NFC/RFID</span>
                </button>
            </div>

            <!-- Tab 1: Barcode Reader (Standby Input Auto-Focus) -->
            <div x-show="mode === 'barcode'" class="space-y-3">
                <div class="p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-lg text-xs text-emerald-900">
                    <strong class="font-medium block mb-0.5">Mode Barcode Gun Aktif:</strong>
                    Arahkan scanner kartu ke barcode NIS siswa. Kolom input otomatis terisi dan memproses presensi secara instan.
                </div>

                <form @submit.prevent="submitBarcode">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Induk Siswa (NIS) / Kode Barcode:</label>
                    <div class="relative">
                        <input type="text" x-ref="barcodeInput" x-model="barcodeCode"
                               placeholder="Klik di sini & scan barcode kartu..."
                               class="w-full px-3.5 py-2.5 pl-10 rounded-lg border border-slate-300 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 text-slate-900 font-mono text-sm font-semibold transition bg-white shadow-2xs">
                        <svg class="w-4 h-4 text-emerald-600 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <button type="submit" class="mt-2.5 w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                        Proses Masuk (Enter)
                    </button>
                </form>
            </div>

            <!-- Tab 2: Kamera Scanner QR Kartu OSIS -->
            <div x-show="mode === 'camera'" class="space-y-3" style="display: none;">
                <div class="rounded-lg overflow-hidden bg-slate-900 aspect-video flex items-center justify-center relative shadow-inner">
                    <div id="gate-qr-reader" class="w-full h-full"></div>
                    <div x-show="!cameraStarted" class="text-center p-6 text-white space-y-2.5">
                        <svg class="w-10 h-10 text-slate-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                        <p class="text-xs text-slate-400">Kamera pos gerbang belum diaktifkan.</p>
                        <button type="button" @click="startCamera" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition shadow-xs">
                            Aktifkan Kamera Gerbang
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Reader NFC / RFID -->
            <div x-show="mode === 'nfc'" class="space-y-3" style="display: none;">
                <div class="p-5 rounded-lg bg-purple-50/80 border border-purple-200/80 text-center space-y-2.5">
                    <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center mx-auto shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Tempelkan Kartu NFC / RFID Pelajar</h3>
                    <p class="text-xs text-slate-600 max-w-sm mx-auto">
                        Tap kartu di punggung ponsel Android atau gunakan USB RFID Reader yang terpasang di komputer pos.
                    </p>
                    <div class="pt-1">
                        <button type="button" @click="startNfcReading" class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-medium rounded-lg shadow-xs transition">
                            Aktifkan Sensor Web NFC
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submitNfcManual">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">UID Kartu NFC / RFID (Input Reader USB):</label>
                    <input type="text" x-ref="nfcInput" x-model="nfcUid" placeholder="Tap kartu ke USB reader..."
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition">
                </form>
            </div>

        </div>

        <!-- Right Column: Live Student Feedback Popup Card -->
        <div class="lg:col-span-5 space-y-4">
            <!-- Active Result Card -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 text-center space-y-3.5">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Status Pemindaian Terakhir</span>

                <template x-if="lastStudent">
                    <div class="space-y-3">
                        <template x-if="lastStudent.photo">
                            <img :src="'/storage/' + lastStudent.photo" :alt="lastStudent.name" class="w-20 h-20 rounded-xl mx-auto object-cover border-2 border-emerald-500 shadow-xs">
                        </template>
                        <template x-if="!lastStudent.photo">
                            <div class="w-16 h-16 rounded-xl mx-auto flex items-center justify-center text-xl font-bold text-white shadow-xs"
                                 :class="lastStudent.status === 'present' ? 'bg-emerald-600' : 'bg-amber-600'">
                                <span x-text="lastStudent.name.substring(0, 2).toUpperCase()"></span>
                            </div>
                        </template>

                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-medium"
                                  :class="lastStudent.status_badge" x-text="lastStudent.status_label"></span>
                            <h3 class="text-base font-bold text-slate-900 mt-1.5" x-text="lastStudent.name"></h3>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">NIS: <span x-text="lastStudent.nis"></span> &bull; Kelas: <span class="font-medium text-slate-800" x-text="lastStudent.class"></span></p>
                            <div class="text-xs font-medium text-slate-700 mt-1.5">
                                Waktu Masuk: <span class="text-emerald-800 font-mono font-semibold" x-text="lastStudent.check_in_time"></span> WIB
                            </div>
                        </div>

                        <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200/80 text-xs text-slate-600" x-text="lastMessage"></div>
                    </div>
                </template>

                <template x-if="!lastStudent && !errorMessage">
                    <div class="py-8 text-slate-400 space-y-1.5">
                        <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <p class="text-xs">Menunggu pemindaian kartu di gerbang...</p>
                    </div>
                </template>

                <template x-if="errorMessage">
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-xs text-center space-y-1.5">
                        <svg class="w-6 h-6 text-rose-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <strong class="font-medium block" x-text="errorMessage"></strong>
                    </div>
                </template>
            </div>

            <!-- Quick Link to Screen & Sessions -->
            <div class="flex items-center gap-3">
                <a href="{{ route('attendance.dynamic-qr-display') }}" target="_blank" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-medium text-center transition flex items-center justify-center gap-2 shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Buka Layar QR Dinamis (TV / Monitor)</span>
                </a>
            </div>
        </div>

    </div>

    <!-- Recent Gate Check-Ins Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-800">10 Siswa Terakhir Masuk</h3>
            <span class="text-[11px] text-slate-500 font-mono">{{ now()->isoFormat('D MMMM Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-700 uppercase text-[10px] font-semibold tracking-wider">
                    <tr>
                        <th class="py-2.5 px-3">Nama Siswa / NIS</th>
                        <th class="py-2.5 px-3">Kelas</th>
                        <th class="py-2.5 px-3">Jam Masuk</th>
                        <th class="py-2.5 px-3">Metode</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todayAttendances as $att)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $att->student->full_name }}
                            <div class="text-[10px] font-mono text-slate-400">NIS: {{ $att->student->nis }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-medium">{{ $att->student->currentClass?->name ?? '-' }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $att->check_in_time }} WIB</td>
                        <td class="py-3 px-4">
                            <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                                {{ $att->source_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $att->status_badge }}">
                                {{ $att->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400">Belum ada siswa yang masuk melalui pos gerbang hari ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
function gateKiosk() {
    return {
        mode: 'barcode',
        barcodeCode: '',
        nfcUid: '',
        cameraStarted: false,
        html5QrCode: null,
        lastStudent: null,
        lastMessage: '',
        errorMessage: '',
        stats: {
            total_in: {{ $stats['total_in'] }},
            present: {{ $stats['present'] }},
            late: {{ $stats['late'] }}
        },

        init() {
            this.$nextTick(() => {
                if (this.$refs.barcodeInput) {
                    this.$refs.barcodeInput.focus();
                }
            });
        },

        setMode(m) {
            this.mode = m;
            this.errorMessage = '';
            if (m === 'barcode') {
                this.$nextTick(() => this.$refs.barcodeInput.focus());
                if (this.cameraStarted) this.stopCamera();
            } else if (m === 'camera') {
                this.startCamera();
            } else if (m === 'nfc') {
                this.$nextTick(() => this.$refs.nfcInput.focus());
                if (this.cameraStarted) this.stopCamera();
            }
        },

        playAudio(isSuccess) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = isSuccess ? 'sine' : 'sawtooth';
                osc.frequency.value = isSuccess ? 880 : 220;
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + (isSuccess ? 0.2 : 0.4));
            } catch (e) {
                console.log(e);
            }
        },

        async sendScan(identifier, method) {
            this.errorMessage = '';
            try {
                const res = await fetch('{{ route('attendance.gate-scanner.process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ identifier, method })
                });

                const data = await res.json();

                if (data.success) {
                    this.playAudio(true);
                    this.lastStudent = data.student_data;
                    this.lastMessage = data.message;
                    if (!data.is_duplicate) {
                        this.stats.total_in++;
                        if (data.status === 'present') this.stats.present++;
                        if (data.status === 'late') this.stats.late++;
                    }

                    // Suara Sambutan Otomatis (Text-to-Speech Web Speech API)
                    if ('speechSynthesis' in window && data.student_data?.name) {
                        try {
                            window.speechSynthesis.cancel();
                            const statusWord = data.status === 'present' ? 'Hadir' : 'Terlambat';
                            const msg = new SpeechSynthesisUtterance(data.student_data.name + ', ' + statusWord);
                            msg.lang = 'id-ID';
                            msg.rate = 1.05;
                            window.speechSynthesis.speak(msg);
                        } catch (e) {}
                    }
                } else {
                    this.playAudio(false);
                    this.lastStudent = null;
                    this.errorMessage = data.message || 'Gagal memproses presensi.';
                }
            } catch (err) {
                this.playAudio(false);
                this.errorMessage = 'Terjadi kesalahan koneksi server.';
            }
        },

        submitBarcode() {
            if (!this.barcodeCode) return;
            const code = this.barcodeCode;
            this.barcodeCode = '';
            this.sendScan(code, 'barcode_nis');
        },

        submitNfcManual() {
            if (!this.nfcUid) return;
            const uid = this.nfcUid;
            this.nfcUid = '';
            this.sendScan(uid, 'nfc_uid');
        },

        startCamera() {
            if (this.cameraStarted) return;
            this.html5QrCode = new Html5Qrcode("gate-qr-reader");
            this.html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    this.sendScan(decodedText, 'qr_card');
                },
                (err) => {}
            ).then(() => {
                this.cameraStarted = true;
            }).catch(err => {
                this.errorMessage = "Tidak dapat mengakses kamera: " + err;
            });
        },

        stopCamera() {
            if (this.html5QrCode && this.cameraStarted) {
                this.html5QrCode.stop().then(() => {
                    this.cameraStarted = false;
                });
            }
        },

        async startNfcReading() {
            if ('NDEFReader' in window) {
                try {
                    const ndef = new NDEFReader();
                    await ndef.scan();
                    alert("Sensor NFC aktif! Silakan tempelkan kartu pelajar ke punggung smartphone.");
                    ndef.onreading = event => {
                        const serialNumber = event.serialNumber;
                        this.sendScan(serialNumber, 'nfc_uid');
                    };
                } catch (error) {
                    alert("Gagal mengaktifkan NFC: " + error.message);
                }
            } else {
                alert("Browser Anda belum mendukung Web NFC API. Gunakan Google Chrome di Android atau gunakan USB RFID reader pada laptop.");
            }
        }
    };
}
</script>
@endsection
