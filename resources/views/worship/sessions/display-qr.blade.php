<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $session->type?->name }} - Layar QR Proyektor | {{ $schoolSetting->school_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-white min-h-screen flex flex-col font-sans antialiased overflow-x-hidden selection:bg-emerald-500 selection:text-white">

    <!-- Top Navigation / Presenter Controls -->
    <header class="bg-slate-900 border-b border-slate-800 px-4 sm:px-6 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            @if($schoolSetting->logo_url)
                <img src="{{ $schoolSetting->logo_url }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg bg-white p-1">
            @else
                <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center font-bold text-white shadow-xs">
                    {{ $schoolSetting->initials }}
                </div>
            @endif
            <div>
                <h1 class="text-sm sm:text-base font-bold tracking-tight text-white flex items-center gap-2">
                    <span>{{ $session->type?->name }}</span>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-emerald-950 text-emerald-400 border border-emerald-800/80">
                        Gelombang {{ $session->wave_number }}
                    </span>
                </h1>
                <p class="text-xs text-slate-400">
                    {{ $schoolSetting->school_name }} &bull; {{ $session->location?->name ?? 'Masjid Utama' }} &bull; {{ $session->date->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-2.5">
            <button id="fullscreenBtn" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium rounded-lg border border-slate-700 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Layar Penuh</span>
            </button>
            <a href="{{ route('worship.sessions.show', $session) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium rounded-lg transition shadow-xs">
                Kembali ke Sesi
            </a>
        </div>
    </header>

    <!-- Main Live Screen -->
    <main class="flex-1 flex flex-col lg:flex-row items-center justify-center p-4 sm:p-6 gap-6 max-w-7xl mx-auto w-full">
        <!-- Left: Huge Dynamic QR Code Display -->
        <div class="flex-1 flex flex-col items-center justify-center">
            <div class="bg-white p-5 sm:p-6 rounded-xl shadow-xl border border-slate-800 flex flex-col items-center max-w-md w-full">
                <!-- QR Canvas -->
                <div class="relative bg-white rounded-lg p-2 flex items-center justify-center">
                    <canvas id="qrCanvas" class="w-64 h-64 sm:w-72 sm:h-72 rounded-md"></canvas>
                </div>

                <!-- Expiry Progress Bar -->
                <div class="w-full mt-3.5 space-y-1.5">
                    <div class="flex items-center justify-between text-xs font-medium text-slate-600">
                        <span class="flex items-center gap-1.5 text-emerald-700 font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            QR Dinamis Aktif
                        </span>
                        <span id="countdownText" class="font-mono text-slate-800 font-semibold">30 detik</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div id="countdownBar" class="bg-emerald-500 h-2 rounded-full transition-all duration-1000 ease-linear" style="width: 100%;"></div>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <p class="text-[11px] text-slate-500 font-medium">Arahkan kamera siswa pada kode QR di atas untuk presensi.</p>
                    <span class="text-[10px] text-slate-400 font-mono tracking-tight block mt-0.5">Kode berganti berkala untuk mencegah screenshot.</span>
                </div>
            </div>
        </div>

        <!-- Right: Live Attendance Statistics & Instructions -->
        <div class="w-full lg:w-96 flex flex-col gap-4">
            <!-- Live Counter Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400">Kehadiran Terkini</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-950 text-emerald-300 font-medium border border-emerald-800/80">
                        Live Auto-Sync
                    </span>
                </div>

                <div>
                    <div class="flex items-baseline gap-2">
                        <span id="presentCount" class="text-3xl sm:text-4xl font-bold text-white tracking-tight">0</span>
                        <span class="text-xs text-slate-400">/ <span id="totalCount">{{ $session->participants()->count() }}</span> Siswa</span>
                    </div>
                    <span class="text-xs text-slate-400 mt-1 block">Telah melakukan scan masuk atau verifikasi</span>
                </div>

                <!-- Rate bar -->
                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs text-slate-300 font-medium">
                        <span>Tingkat Partisipasi</span>
                        <span id="rateText" class="text-emerald-400 font-mono">0%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                        <div id="rateBar" class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: 0%;"></div>
                    </div>
                </div>
            </div>

            <!-- Presenter Guidance Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 text-xs text-slate-300 space-y-3 shadow-lg">
                <h4 class="font-semibold text-xs sm:text-sm text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Panduan Petugas Keagamaan
                </h4>
                <ul class="space-y-2 text-slate-400 text-xs">
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 shrink-0"></span>
                        <span>Tampilkan halaman ini pada proyektor masjid sebelum azan berkumandang.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 shrink-0"></span>
                        <span>Siswa melakukan scan melalui menu <strong>Scanner Kamera</strong> pada akun masing-masing.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 shrink-0"></span>
                        <span>Jika siswa tidak membawa perangkat, gunakan <strong>Scanner Petugas</strong> untuk input NIS manual.</span>
                    </li>
                </ul>
            </div>
        </div>
    </main>

    <!-- Client-side Dynamic Token Sync & QR Rendering Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('qrCanvas');
            const countdownText = document.getElementById('countdownText');
            const countdownBar = document.getElementById('countdownBar');
            const presentCountEl = document.getElementById('presentCount');
            const totalCountEl = document.getElementById('totalCount');
            const rateTextEl = document.getElementById('rateText');
            const rateBarEl = document.getElementById('rateBar');
            const fullscreenBtn = document.getElementById('fullscreenBtn');

            const tokenUrl = "{{ route('worship.sessions.token-json', $session) }}";
            let currentToken = null;
            let remainingSeconds = 30;
            let totalTtl = 30;

            function renderQR(text) {
                if (window.QRCode && typeof window.QRCode.toCanvas === 'function') {
                    window.QRCode.toCanvas(canvas, text, {
                        width: 320,
                        margin: 2,
                        color: {
                            dark: '#0f172a',
                            light: '#ffffff'
                        }
                    }, function (error) {
                        if (error) console.error(error);
                    });
                }
            }

            function fetchToken() {
                fetch(tokenUrl)
                    .then(res => res.json())
                    .then(data => {
                        if (data.token !== currentToken) {
                            currentToken = data.token;
                            renderQR(data.token);
                        }

                        remainingSeconds = Math.max(1, data.remaining_seconds);
                        totalTtl = Math.max(totalTtl, remainingSeconds);

                        // Update stats
                        presentCountEl.textContent = data.present_count;
                        totalCountEl.textContent = data.total_count;
                        rateTextEl.textContent = data.attendance_rate + '%';
                        rateBarEl.style.width = Math.min(100, data.attendance_rate) + '%';
                    })
                    .catch(err => console.error("Error syncing token:", err));
            }

            // Initial fetch
            fetchToken();

            // Interval: Tick countdown every second
            setInterval(function () {
                if (remainingSeconds > 0) {
                    remainingSeconds--;
                    countdownText.textContent = remainingSeconds + ' detik';
                    const pct = Math.max(0, Math.min(100, (remainingSeconds / totalTtl) * 100));
                    countdownBar.style.width = pct + '%';
                }

                // If token expires or is <= 2s, refresh
                if (remainingSeconds <= 2) {
                    fetchToken();
                }
            }, 1000);

            // Periodic data sync every 5 seconds
            setInterval(fetchToken, 5000);

            // Fullscreen toggle
            fullscreenBtn.addEventListener('click', function () {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => console.log(err));
                } else {
                    document.exitFullscreen().catch(err => console.log(err));
                }
            });
        });
    </script>
</body>
</html>
