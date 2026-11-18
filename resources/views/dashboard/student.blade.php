@extends('layouts.app')

@section('title', 'Portal Siswa')

@section('content')
<div class="space-y-5" x-data="studentPortal()">
    <!-- Welcome Header Card -->
    <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 border-l-4 border-l-emerald-600 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-semibold text-emerald-700 tracking-wide uppercase">
                Portal Siswa Mandiri &bull; Presensi Multi-Metode
            </span>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">Assalamu'alaikum, {{ $student?->full_name ?? $user->name }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                NIS: <strong class="text-slate-800 font-mono">{{ $student?->nis ?? '-' }}</strong> &bull;
                Kelas: <strong class="text-slate-800">{{ $student?->currentClass?->name ?? 'Belum Ditentukan' }}</strong> &bull;
                Wali: <strong class="text-slate-800">{{ $student?->currentClass?->homeroomTeacher?->user->name ?? '-' }}</strong>
            </p>
        </div>

        <!-- Quick Action: Show OSIS Card -->
        <button type="button" @click="showOsisModal = true" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs font-medium text-white transition flex items-center gap-2 self-start md:self-auto shadow-2xs">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
            <span>Kartu Pelajar OSIS Digital</span>
        </button>
    </div>

    <!-- Today's Attendance Banner Status -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                     :class="todayAttendance ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-500 border border-slate-200/60'">
                    <template x-if="todayAttendance">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="!todayAttendance">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
                <div>
                    <span class="text-[10px] font-medium uppercase tracking-wider text-slate-400 block">Status Presensi Masuk Hari Ini</span>
                    <h3 class="text-sm font-semibold text-slate-900 mt-0.5">
                        <template x-if="todayAttendance">
                            <span class="inline-flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-medium" :class="todayAttendance.status_badge" x-text="todayAttendance.status_label"></span>
                                <span class="text-xs font-mono text-slate-600">Pukul <strong x-text="todayAttendance.check_in_time"></strong> WIB</span>
                            </span>
                        </template>
                        <template x-if="!todayAttendance">
                            <span class="text-amber-700 font-medium text-xs sm:text-sm">Belum Melakukan Presensi Masuk Hari Ini</span>
                        </template>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <template x-if="todayAttendance">
                            <span>Metode: <strong class="text-slate-700 font-medium" x-text="todayAttendance.source_label"></strong> &bull; <span x-text="todayAttendance.notes"></span></span>
                        </template>
                        <template x-if="!todayAttendance">
                            <span>Silakan gunakan salah satu metode di bawah untuk mencatat kehadiran.</span>
                        </template>
                    </p>
                </div>
            </div>

            <!-- Date indicator -->
            <div class="text-right hidden sm:block shrink-0">
                <span class="text-xs font-semibold text-slate-800">{{ now()->isoFormat('dddd') }}</span>
                <div class="text-[11px] text-slate-500 font-mono">{{ now()->isoFormat('D MMMM Y') }}</div>
            </div>
        </div>
    </div>

    <!-- 3 Modern Attendance Methods Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Method 1: Geolocation GPS Button -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 flex flex-col justify-between space-y-3 hover:border-emerald-300/80 transition">
            <div class="space-y-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">1. Presensi GPS Mandiri</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Absen langsung menggunakan lokasi GPS ponsel saat tiba di area madrasah (Radius: <strong>{{ $schoolLocation?->radius_meters ?? 100 }}m</strong>).
                </p>
            </div>

            <div class="space-y-2 pt-1">
                <button type="button" @click="submitGpsCheckIn" :disabled="isLocating || todayAttendance !== null"
                        class="w-full py-2 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-200 disabled:cursor-not-allowed text-white font-medium text-xs shadow-2xs transition flex items-center justify-center gap-1.5">
                    <template x-if="isLocating">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </template>
                    <span x-text="todayAttendance ? 'Sudah Hadir Hari Ini' : (isLocating ? 'Mendeteksi GPS...' : 'Ambil Lokasi & Absen')"></span>
                </button>
                <div x-show="gpsFeedback" class="p-2.5 rounded-lg text-xs font-medium" :class="gpsFeedbackSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'" x-text="gpsFeedback" style="display: none;"></div>
            </div>
        </div>

        <!-- Method 2: Scan QR Dinamis Gerbang / Layar TV -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 flex flex-col justify-between space-y-3 hover:border-emerald-300/80 transition">
            <div class="space-y-2.5">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">2. Pindai QR Layar Gerbang</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Arahkan kamera ponsel Anda ke layar monitor TV / tablet yang dipasang di pos gerbang atau depan kelas madrasah.
                </p>
            </div>

            <div class="space-y-2 pt-1">
                <button type="button" @click="openDynamicScannerModal" :disabled="todayAttendance !== null"
                        class="w-full py-2 px-3 rounded-lg bg-teal-600 hover:bg-teal-700 disabled:bg-slate-200 disabled:cursor-not-allowed text-white font-medium text-xs shadow-2xs transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                    <span>Buka Kamera & Scan QR</span>
                </button>
            </div>
        </div>

        <!-- Method 3: Kartu OSIS / NFC Tap -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 flex flex-col justify-between space-y-3 hover:border-emerald-300/80 transition">
            <div class="space-y-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                </div>
                <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">3. Kartu OSIS & Kartu NFC</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Tunjukkan QR kartu pelajar ke scanner pos piket, atau tap kartu fisik ber-chip NFC ke reader meja gerbang.
                </p>
            </div>

            <div class="space-y-2 pt-1">
                <button type="button" @click="showOsisModal = true"
                        class="w-full py-2 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs shadow-2xs transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Tampilkan Kartu OSIS</span>
                </button>
            </div>
        </div>

    </div>

    <!-- Recent Attendance History Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-3">
        <h3 class="text-xs sm:text-sm font-semibold text-slate-900">Riwayat Kehadiran Terakhir Anda</h3>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase text-[10px] font-semibold tracking-wider">
                    <tr>
                        <th class="py-2 px-3">Tanggal</th>
                        <th class="py-2 px-3">Jam Masuk</th>
                        <th class="py-2 px-3">Metode</th>
                        <th class="py-2 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAttendances as $att)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2 px-3 font-medium text-slate-900">{{ $att->date->isoFormat('dddd, D MMMM Y') }}</td>
                        <td class="py-2 px-3 font-mono text-slate-700">{{ $att->check_in_time ?? '-' }} WIB</td>
                        <td class="py-2 px-3 text-slate-600">{{ $att->source_label }}</td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $att->status_badge }}">
                                {{ $att->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-400">Belum ada riwayat kehadiran tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Kartu Pelajar OSIS Digital -->
    <div x-show="showOsisModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-xl max-w-sm w-full overflow-hidden shadow-xl border border-slate-200 print:shadow-none print:border-none" @click.away="showOsisModal = false">
            <!-- Header Kartu -->
            <div class="bg-slate-900 p-4 text-white text-center relative border-b border-slate-800">
                <button type="button" @click="showOsisModal = false" class="absolute top-3.5 right-3.5 text-slate-400 hover:text-white text-lg print:hidden">&times;</button>
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs mx-auto mb-1.5 shadow-xs">
                    M1
                </div>
                <h4 class="text-xs font-semibold tracking-wider uppercase">Madrasah Aliyah Negeri</h4>
                <p class="text-[10px] text-emerald-400 font-medium tracking-wide uppercase">Kartu Tanda Pelajar & OSIS</p>
            </div>

            <!-- Body Kartu -->
            <div class="p-5 text-center space-y-3.5">
                <div class="w-14 h-14 rounded-lg bg-emerald-600 text-white font-bold flex items-center justify-center text-xl mx-auto shadow-xs">
                    {{ strtoupper(substr($student?->full_name ?? 'S', 0, 2)) }}
                </div>

                <div>
                    <h3 class="font-semibold text-slate-900 text-sm leading-tight">{{ $student?->full_name }}</h3>
                    <div class="text-[11px] font-mono text-slate-600 mt-0.5">NIS: {{ $student?->nis }} &bull; Kelas: {{ $student?->currentClass?->name ?? '-' }}</div>
                </div>

                <!-- Big OSIS QR Code (Statis untuk Fisik & Digital) -->
                <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-lg flex flex-col items-center relative overflow-hidden">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60 mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                        KARTU RESMI AKTIF
                    </span>

                    <canvas id="studentOsisQrCanvas" class="w-44 h-44 rounded-md shadow-2xs"></canvas>

                    <!-- Live Watermark Clock untuk pencegahan screenshot statis -->
                    <div class="text-[10px] font-mono text-slate-500 mt-2.5 flex items-center justify-between w-full border-t border-slate-200 pt-1.5">
                        <span>Otentikasi Waktu:</span>
                        <span class="font-semibold text-emerald-700" x-text="liveOsisClock"></span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Kode QR resmi siswa. Berlaku untuk kartu fisik (cetak PVC) maupun kartu digital pada aplikasi.
                </p>

                <div class="grid grid-cols-2 gap-2 print:hidden">
                    <button type="button" @click="window.print()" class="w-full py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-medium rounded-lg text-xs transition border border-emerald-200 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak Kartu
                    </button>
                    <button type="button" @click="showOsisModal = false" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-xs transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Scanner Kamera QR Layar Gerbang -->
    <div x-show="showScanModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-xl max-w-md w-full overflow-hidden shadow-xl border border-slate-200 p-5 space-y-3.5" @click.away="closeDynamicScannerModal">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Pindai QR Layar Gerbang</h3>
                <button type="button" @click="closeDynamicScannerModal" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <div class="rounded-lg overflow-hidden bg-slate-950 aspect-square flex items-center justify-center relative">
                <div id="student-qr-reader" class="w-full h-full"></div>
            </div>

            <div x-show="scanFeedback" class="p-2.5 rounded-lg text-xs font-medium text-center" :class="scanFeedbackSuccess ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800'" x-text="scanFeedback" style="display: none;"></div>

            <p class="text-[11px] text-slate-500 text-center">
                Arahkan kamera ponsel Anda ke layar monitor QR di pos gerbang atau proyektor kelas.
            </p>
        </div>
    </div>

    <!-- Modal 3: Konfirmasi Sukses Presensi dengan Foto Siswa -->
    <div x-show="showSuccessModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-xl max-w-sm w-full overflow-hidden shadow-xl border border-slate-200 text-center p-5 space-y-3.5" @click.away="showSuccessModal = false">
            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>

            <div>
                <h3 class="text-sm font-bold text-slate-900">Presensi Berhasil Diverifikasi!</h3>
                <p class="text-xs text-slate-500 mt-0.5">Data kehadiran Anda telah tercatat di server.</p>
            </div>

            <!-- Foto Siswa -->
            <div class="space-y-1.5 py-1">
                @if($student?->photo)
                    <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}" class="w-20 h-20 rounded-xl mx-auto object-cover border-2 border-emerald-500 shadow-sm">
                @else
                    <div class="w-16 h-16 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center text-xl mx-auto shadow-sm">
                        {{ strtoupper(substr($student?->full_name ?? 'S', 0, 2)) }}
                    </div>
                @endif

                <div>
                    <h4 class="font-semibold text-slate-900 text-xs sm:text-sm leading-tight">{{ $student?->full_name }}</h4>
                    <p class="text-[11px] font-mono text-slate-500">NIS: {{ $student?->nis }} &bull; Kelas: {{ $student?->currentClass?->name ?? '-' }}</p>
                </div>
            </div>

            <!-- Detail Ringkasan Masuk -->
            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-lg text-left space-y-1.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Waktu Masuk:</span>
                    <span class="font-medium font-mono text-emerald-800" x-text="todayAttendance?.check_in_time + ' WIB'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Status Kehadiran:</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium" :class="todayAttendance?.status_badge" x-text="todayAttendance?.status_label"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Metode Verifikasi:</span>
                    <span class="font-medium text-slate-800" x-text="todayAttendance?.source_label"></span>
                </div>
            </div>

            <p class="text-[11px] text-emerald-700 font-medium" x-text="successMessage"></p>

            <button type="button" @click="showSuccessModal = false" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs shadow-xs transition">
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
