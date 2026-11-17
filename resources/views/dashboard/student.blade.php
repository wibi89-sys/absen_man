@extends('layouts.app')

@section('title', 'Portal Siswa')

@section('content')
<div class="space-y-6" x-data="studentPortal()">
    <!-- Welcome Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-800 text-white shadow-md relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-700/60 text-emerald-200 border border-emerald-500/30 mb-2">
                    Portal Siswa Mandiri &bull; Presensi Multi-Metode
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Assalamu'alaikum, {{ $student?->full_name ?? $user->name }}</h1>
                <p class="text-emerald-100 text-xs sm:text-sm mt-1">
                    NIS: <strong class="text-white font-mono">{{ $student?->nis ?? '-' }}</strong> &bull;
                    Rombel: <strong class="text-white">{{ $student?->currentClass?->name ?? 'Belum Ditentukan' }}</strong> &bull;
                    Wali Kelas: <strong class="text-white">{{ $student?->currentClass?->homeroomTeacher?->user->name ?? '-' }}</strong>
                </p>
            </div>

            <!-- Quick Action: Show OSIS Card -->
            <button type="button" @click="showOsisModal = true" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-2xl text-xs font-bold text-white border border-white/20 transition flex items-center gap-2 self-start md:self-auto shadow-sm">
                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                <span>Kartu Pelajar OSIS Digital</span>
            </button>
        </div>
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-700/20 rounded-full blur-2xl"></div>
    </div>

    <!-- Today's Attendance Banner Status -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-bold shadow-sm"
                     :class="todayAttendance ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'">
                    <template x-if="todayAttendance">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="!todayAttendance">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block">Status Presensi Masuk Hari Ini</span>
                    <h3 class="text-lg font-bold text-slate-900 mt-0.5">
                        <template x-if="todayAttendance">
                            <span class="inline-flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold" :class="todayAttendance.status_badge" x-text="todayAttendance.status_label"></span>
                                <span class="text-sm font-mono text-slate-600">Pukul <strong x-text="todayAttendance.check_in_time"></strong> WIB</span>
                            </span>
                        </template>
                        <template x-if="!todayAttendance">
                            <span class="text-amber-700 font-semibold text-sm">Belum Melakukan Presensi Masuk Hari Ini</span>
                        </template>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        <template x-if="todayAttendance">
                            <span>Metode: <strong class="text-slate-700" x-text="todayAttendance.source_label"></strong> &bull; <span x-text="todayAttendance.notes"></span></span>
                        </template>
                        <template x-if="!todayAttendance">
                            <span>Silakan gunakan salah satu metode presensi di bawah untuk mencatat kehadiran Anda.</span>
                        </template>
                    </p>
                </div>
            </div>

            <!-- Date indicator -->
            <div class="text-right hidden sm:block">
                <span class="text-xs font-bold text-slate-800">{{ now()->isoFormat('dddd') }}</span>
                <div class="text-xs text-slate-500 font-mono">{{ now()->isoFormat('D MMMM Y') }}</div>
            </div>
        </div>
    </div>

    <!-- 3 Modern Attendance Methods Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Method 1: Geolocation GPS Button -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col justify-between space-y-4 hover:border-emerald-300 transition">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base">1. Presensi GPS Mandiri</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Absen langsung menggunakan lokasi GPS ponsel Anda saat sudah tiba di area madrasah (Radius: <strong>{{ $schoolLocation?->radius_meters ?? 100 }}m</strong>).
                </p>
            </div>

            <div class="space-y-2 pt-2">
                <button type="button" @click="submitGpsCheckIn" :disabled="isLocating || todayAttendance !== null"
                        class="w-full py-3 px-4 rounded-2xl bg-emerald-700 hover:bg-emerald-800 disabled:bg-slate-200 disabled:cursor-not-allowed text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                    <template x-if="isLocating">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </template>
                    <span x-text="todayAttendance ? 'Sudah Hadir Hari Ini' : (isLocating ? 'Mendeteksi Posisi GPS...' : 'Ambil Lokasi & Absen Sekarang')"></span>
                </button>
                <div x-show="gpsFeedback" class="p-3 rounded-xl text-xs font-medium" :class="gpsFeedbackSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'" x-text="gpsFeedback" style="display: none;"></div>
            </div>
        </div>

        <!-- Method 2: Scan QR Dinamis Gerbang / Layar TV -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col justify-between space-y-4 hover:border-emerald-300 transition">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base">2. Pindai QR Layar Gerbang</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Arahkan kamera ponsel Anda ke layar monitor TV / tablet yang dipasang di pos gerbang atau depan kelas madrasah.
                </p>
            </div>

            <div class="space-y-2 pt-2">
                <button type="button" @click="openDynamicScannerModal" :disabled="todayAttendance !== null"
                        class="w-full py-3 px-4 rounded-2xl bg-teal-700 hover:bg-teal-800 disabled:bg-slate-200 disabled:cursor-not-allowed text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                    <span>Buka Kamera & Scan QR Gerbang</span>
                </button>
            </div>
        </div>

        <!-- Method 3: Kartu OSIS / NFC Tap -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col justify-between space-y-4 hover:border-emerald-300 transition">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base">3. Kartu OSIS & Kartu NFC</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Tunjukkan QR kartu pelajar OSIS ke scanner pos piket, atau tap kartu fisik ber-chip NFC ke reader meja gerbang.
                </p>
            </div>

            <div class="space-y-2 pt-2">
                <button type="button" @click="showOsisModal = true"
                        class="w-full py-3 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Tampilkan Kartu OSIS Saya</span>
                </button>
            </div>
        </div>

    </div>

    <!-- Recent Attendance History Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
        <h3 class="text-sm font-bold text-slate-800">Riwayat Kehadiran Terakhir Anda</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Jam Masuk</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAttendances as $att)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $att->date->isoFormat('dddd, D MMMM Y') }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700">{{ $att->check_in_time ?? '-' }} WIB</td>
                        <td class="py-3 px-4 text-slate-600">{{ $att->source_label }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $att->status_badge }}">
                                {{ $att->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-slate-400">Belum ada riwayat kehadiran tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Kartu Pelajar OSIS Digital -->
    <div x-show="showOsisModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl border border-slate-200 print:shadow-none print:border-none" @click.away="showOsisModal = false">
            <!-- Header Kartu -->
            <div class="bg-gradient-to-r from-emerald-800 to-teal-700 p-5 text-white text-center relative">
                <button type="button" @click="showOsisModal = false" class="absolute top-4 right-4 text-emerald-200 hover:text-white text-lg print:hidden">&times;</button>
                <div class="w-10 h-10 rounded-xl bg-white text-emerald-800 flex items-center justify-center font-black mx-auto mb-2 shadow">
                    M1
                </div>
                <h4 class="text-xs font-bold tracking-wider uppercase">Madrasah Aliyah Negeri</h4>
                <p class="text-[10px] text-emerald-200 font-semibold tracking-widest uppercase">Kartu Tanda Pelajar & OSIS</p>
            </div>

            <!-- Body Kartu -->
            <div class="p-6 text-center space-y-4">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-extrabold flex items-center justify-center text-2xl mx-auto shadow-md">
                    {{ strtoupper(substr($student?->full_name ?? 'S', 0, 2)) }}
                </div>

                <div>
                    <h3 class="font-bold text-slate-900 text-base leading-tight">{{ $student?->full_name }}</h3>
                    <div class="text-xs font-mono text-slate-600 mt-0.5">NIS: {{ $student?->nis }} &bull; Kelas: {{ $student?->currentClass?->name ?? '-' }}</div>
                </div>

                <!-- Big OSIS QR Code (Statis untuk Fisik & Digital) -->
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col items-center relative overflow-hidden">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                        KARTU RESMI AKTIF
                    </span>

                    <canvas id="studentOsisQrCanvas" class="w-48 h-48 rounded-xl shadow-xs"></canvas>

                    <!-- Live Watermark Clock untuk pencegahan screenshot statis -->
                    <div class="text-[10px] font-mono text-slate-500 mt-3 flex items-center justify-between w-full border-t border-slate-200/60 pt-2">
                        <span>Otentikasi Waktu (Live):</span>
                        <span class="font-bold text-emerald-800" x-text="liveOsisClock"></span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Kode QR resmi siswa. Berlaku untuk kartu fisik (cetak PVC/laminasi) maupun kartu digital pada aplikasi.
                </p>

                <div class="grid grid-cols-2 gap-2 print:hidden">
                    <button type="button" @click="window.print()" class="w-full py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold rounded-xl text-xs transition border border-emerald-200 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak Kartu
                    </button>
                    <button type="button" @click="showOsisModal = false" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Scanner Kamera QR Layar Gerbang -->
    <div x-show="showScanModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200 p-6 space-y-4" @click.away="closeDynamicScannerModal">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Pindai QR Layar Gerbang</h3>
                <button type="button" @click="closeDynamicScannerModal" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <div class="rounded-2xl overflow-hidden bg-slate-950 aspect-square flex items-center justify-center relative">
                <div id="student-qr-reader" class="w-full h-full"></div>
            </div>

            <div x-show="scanFeedback" class="p-3 rounded-xl text-xs font-medium text-center" :class="scanFeedbackSuccess ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800'" x-text="scanFeedback" style="display: none;"></div>

            <p class="text-[11px] text-slate-400 text-center">
                Arahkan kamera ponsel Anda ke layar monitor QR di pos gerbang atau proyektor kelas.
            </p>
        </div>
    </div>

    <!-- Modal 3: Konfirmasi Sukses Presensi dengan Foto Siswa -->
    <div x-show="showSuccessModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl border border-slate-200 text-center p-6 space-y-4" @click.away="showSuccessModal = false">
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>

            <div>
                <h3 class="text-base font-extrabold text-slate-900">Presensi Berhasil Diverifikasi!</h3>
                <p class="text-xs text-slate-500 mt-0.5">Data kehadiran Anda telah tercatat di server madrasah.</p>
            </div>

            <!-- Foto Siswa -->
            <div class="space-y-2 py-1">
                @if($student?->photo)
                    <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}" class="w-24 h-24 rounded-3xl mx-auto object-cover border-4 border-emerald-500 shadow-md">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-extrabold flex items-center justify-center text-2xl mx-auto shadow-md">
                        {{ strtoupper(substr($student?->full_name ?? 'S', 0, 2)) }}
                    </div>
                @endif

                <div>
                    <h4 class="font-bold text-slate-900 text-sm leading-tight">{{ $student?->full_name }}</h4>
                    <p class="text-[11px] font-mono text-slate-500">NIS: {{ $student?->nis }} &bull; Kelas: {{ $student?->currentClass?->name ?? '-' }}</p>
                </div>
            </div>

            <!-- Detail Ringkasan Masuk -->
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-left space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Waktu Masuk:</span>
                    <span class="font-bold font-mono text-emerald-800" x-text="todayAttendance?.check_in_time + ' WIB'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Status Kehadiran:</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold" :class="todayAttendance?.status_badge" x-text="todayAttendance?.status_label"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Metode Verifikasi:</span>
                    <span class="font-semibold text-slate-800" x-text="todayAttendance?.source_label"></span>
                </div>
            </div>

            <p class="text-[11px] text-emerald-700 font-medium" x-text="successMessage"></p>

            <button type="button" @click="showSuccessModal = false" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition">
                Selesai & Tutup
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
function studentPortal() {
    return {
        showOsisModal: false,
        showScanModal: false,
        showSuccessModal: false,
        successMessage: '',
        isLocating: false,
        gpsFeedback: '',
        gpsFeedbackSuccess: false,
        scanFeedback: '',
        scanFeedbackSuccess: false,
        todayAttendance: {{ \Illuminate\Support\Js::from($todayAttendance ? ['status' => $todayAttendance->status, 'status_label' => $todayAttendance->status_label, 'status_badge' => $todayAttendance->status_badge, 'check_in_time' => $todayAttendance->check_in_time, 'source_label' => $todayAttendance->source_label, 'notes' => $todayAttendance->notes] : null) }},
        html5QrCode: null,
        liveOsisClock: '',

        init() {
            this.$watch('showOsisModal', (val) => {
                if (val) {
                    this.$nextTick(() => {
                        this.renderStaticOsisQr();
                    });
                }
            });

            // Live clock updater untuk verifikasi keaslian layar aplikasi (bukan screenshot mati)
            setInterval(() => {
                const now = new Date();
                this.liveOsisClock = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
            }, 1000);
            const now = new Date();
            this.liveOsisClock = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
        },

        renderStaticOsisQr() {
            const canvas = document.getElementById('studentOsisQrCanvas');
            if (!canvas) return;
            const token = '{{ $student?->effective_qr_token ?? $student?->nis }}';
            QRCode.toCanvas(canvas, token, {
                width: 190,
                margin: 1,
                color: { dark: '#064e3b', light: '#ffffff' }
            });
        },

        // 1. Presensi Mandiri via GPS
        submitGpsCheckIn() {
            if (this.isLocating) return;

            if (!('geolocation' in navigator)) {
                this.gpsFeedbackSuccess = false;
                this.gpsFeedback = 'Browser Anda tidak mendukung Geolocation GPS.';
                return;
            }

            this.isLocating = true;
            this.gpsFeedback = 'Sedang mendeteksi sinyal GPS ponsel Anda...';
            this.gpsFeedbackSuccess = true;

            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    const acc = pos.coords.accuracy;

                    try {
                        const res = await fetch('{{ route('attendance.self-gps') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                latitude: lat,
                                longitude: lon,
                                accuracy: acc
                            })
                        });

                        const data = await res.json();

                        if (data.success) {
                            this.gpsFeedbackSuccess = true;
                            this.gpsFeedback = data.message + ` (Jarak Anda: ${data.distance} meter dari madrasah).`;
                            this.todayAttendance = {
                                status: data.attendance.status,
                                status_label: data.status === 'present' ? 'Hadir' : 'Terlambat',
                                status_badge: data.status === 'present' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800',
                                check_in_time: data.attendance.check_in_time,
                                source_label: 'GPS Mandiri Siswa',
                                notes: data.attendance.notes
                            };
                            this.successMessage = data.message + (data.distance ? ` (Jarak: ${data.distance} meter dari madrasah).` : '');
                            this.showSuccessModal = true;
                        } else {
                            this.gpsFeedbackSuccess = false;
                            this.gpsFeedback = data.message;
                        }
                    } catch (err) {
                        this.gpsFeedbackSuccess = false;
                        this.gpsFeedback = 'Terjadi kesalahan jaringan saat mengirim presensi.';
                    } finally {
                        this.isLocating = false;
                    }
                },
                (err) => {
                    this.isLocating = false;
                    this.gpsFeedbackSuccess = false;
                    this.gpsFeedback = 'Gagal mengakses GPS: ' + err.message + '. Pastikan izin lokasi telah diizinkan pada browser.';
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        },

        // 2. Scan QR Dinamis Layar Gerbang
        openDynamicScannerModal() {
            this.showScanModal = true;
            this.scanFeedback = '';
            this.$nextTick(() => {
                this.html5QrCode = new Html5Qrcode("student-qr-reader");
                this.html5QrCode.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    async (decodedText) => {
                        this.scanFeedback = 'QR terdeteksi. Memvalidasi...';
                        this.html5QrCode.pause();

                        try {
                            const res = await fetch('{{ route('attendance.scan-dynamic-qr') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ token: decodedText })
                            });

                            const data = await res.json();

                            if (data.success) {
                                this.scanFeedbackSuccess = true;
                                this.scanFeedback = data.message;
                                this.todayAttendance = {
                                    status: data.attendance.status,
                                    status_label: data.status === 'present' ? 'Hadir' : 'Terlambat',
                                    status_badge: data.status === 'present' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800',
                                    check_in_time: data.attendance.check_in_time,
                                    source_label: 'Scan QR Gerbang/Kelas',
                                    notes: data.attendance.notes
                                };
                                this.successMessage = data.message;
                                this.closeDynamicScannerModal();
                                this.showSuccessModal = true;
                            } else {
                                this.scanFeedbackSuccess = false;
                                this.scanFeedback = data.message;
                                this.html5QrCode.resume();
                            }
                        } catch (e) {
                            this.scanFeedbackSuccess = false;
                            this.scanFeedback = 'Terjadi kesalahan koneksi server.';
                            this.html5QrCode.resume();
                        }
                    },
                    (err) => {}
                ).catch(err => {
                    this.scanFeedbackSuccess = false;
                    this.scanFeedback = "Gagal menyalakan kamera: " + err;
                });
            });
        },

        closeDynamicScannerModal() {
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.html5QrCode = null;
                    this.showScanModal = false;
                }).catch(() => {
                    this.showScanModal = false;
                });
            } else {
                this.showScanModal = false;
            }
        }
    };
}
</script>
@endsection
