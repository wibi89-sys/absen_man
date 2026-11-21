<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Layar Presensi Gerbang QR Dinamis | {{ $schoolSetting->school_name }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ $schoolSetting->favicon_url }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ $schoolSetting->favicon_url }}">

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
                    <span>{{ $schoolSetting->school_name }}</span>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-emerald-950 text-emerald-400 border border-emerald-800/80">
                        QR Dinamis Gerbang
                    </span>
                </h1>
                <p class="text-xs text-slate-400">
                    {{ $schoolSetting->gate_welcome_text ?? 'Pindai menggunakan aplikasi siswa di ponsel' }} &bull; {{ now()->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-2.5">
            <!-- Digital Clock -->
            <div class="px-3 py-1.5 bg-slate-800 rounded-lg border border-slate-700/80 text-emerald-400 font-mono font-semibold text-xs tracking-wider hidden sm:block" id="digitalClock">
                00:00:00 WIB
            </div>

            <button id="fullscreenBtn" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium rounded-lg border border-slate-700 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Layar Penuh</span>
            </button>
            <a href="{{ route('attendance.gate-scanner') }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium rounded-lg transition shadow-xs">
                Pos Kiosk
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
                            QR Dinamis Berputar
                        </span>
                        <span id="countdownText" class="font-mono text-slate-800 font-semibold">25 detik</span>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div id="progressBar" class="bg-emerald-600 h-full w-full transition-all duration-300 ease-linear rounded-full"></div>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 text-center mt-3 leading-relaxed">
                    Arahkan kamera aplikasi ponsel siswa ke kode QR di atas.<br>
                    Kode otomatis diperbarui berkala untuk mencegah kecurangan.
                </p>
            </div>
        </div>

        <!-- Right: Real-time Stats & Guidelines -->
        <div class="w-full lg:w-96 flex flex-col gap-4">
            <!-- Counter Box -->
            <div class="bg-slate-900 p-5 rounded-xl border border-slate-800 shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400 block mb-1">
                        Siswa Masuk Hari Ini
                    </span>
                    <div class="flex items-baseline space-x-2">
                        <span id="totalCheckedInCount" class="text-3xl sm:text-4xl font-bold text-white tracking-tight">{{ $totalIn }}</span>
                        <span class="text-xs font-medium text-slate-300">siswa hadir</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">
                        Presensi masuk madrasah aktif &bull; Sesi: {{ $session->title }}
                    </p>
                </div>
            </div>

            <!-- Attendance Info Instructions Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3.5 shadow-lg">
                <h3 class="text-xs sm:text-sm font-semibold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Petunjuk Presensi Gerbang</span>
                </h3>

                <ul class="text-xs text-slate-300 space-y-2">
                    <li class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-emerald-950 text-emerald-400 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5 border border-emerald-800">1</span>
                        <span>Buka menu <strong>Scan QR Gerbang</strong> pada ponsel siswa.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-emerald-950 text-emerald-400 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5 border border-emerald-800">2</span>
                        <span>Arahkan kamera ke layar ini hingga bunyi <em>beep</em> berhasil.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-emerald-950 text-emerald-400 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5 border border-emerald-800">3</span>
                        <span>Bagi siswa tanpa ponsel, silakan tunjukkan <strong>Kartu OSIS</strong> ke meja pos piket di samping gerbang.</span>
                    </li>
                </ul>
            </div>
        </div>
    </main>

    <!-- Footer Status Bar -->
    <footer class="bg-slate-900/60 border-t border-slate-800/60 px-6 py-3 flex items-center justify-between text-xs text-slate-400">
        <div class="flex items-center space-x-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Server FastCGI Online &bull; Latensi Sync: &lt;50ms</span>
        </div>
        <div>
            MADRASAH ONE &bull; Sistem Presensi Terpadu
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script>
        let currentToken = '';
        let expiresAtEpoch = 0;
        let totalTtl = 25;
        let countdownTimer = null;

        // Digital Clock Updater
        function updateDigitalClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('digitalClock');
            if (el) el.textContent = `${h}:${m}:${s} WIB`;
        }
        setInterval(updateDigitalClock, 1000);
        updateDigitalClock();

        // Fetch Token from Server
        async function fetchNewToken() {
            try {
                const res = await fetch('{{ route('attendance.dynamic-qr-token') }}', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                currentToken = data.token;
                expiresAtEpoch = data.expires_at_epoch;
                totalTtl = data.ttl || 25;

                // Update counter
                if (data.total_checked_in !== undefined) {
                    document.getElementById('totalCheckedInCount').textContent = data.total_checked_in;
                }

                renderQr(currentToken);
                startCountdown();
            } catch (err) {
                console.error("Gagal memperbarui QR Token:", err);
                setTimeout(fetchNewToken, 5000);
            }
        }

        // Render QR Code onto Canvas
        function renderQr(text) {
            const canvas = document.getElementById('qrCanvas');
            QRCode.toCanvas(canvas, text, {
                width: 320,
                margin: 1,
                color: {
                    dark: '#0f172a',
                    light: '#ffffff'
                }
            }, function (error) {
                if (error) console.error(error);
            });
        }

        // Progress Bar & Countdown logic
        function startCountdown() {
            if (countdownTimer) clearInterval(countdownTimer);

            const progressBar = document.getElementById('progressBar');
            const countdownText = document.getElementById('countdownText');

            countdownTimer = setInterval(() => {
                const nowEpoch = Math.floor(Date.now() / 1000);
                const remaining = Math.max(0, expiresAtEpoch - nowEpoch);
                const percent = (remaining / totalTtl) * 100;

                progressBar.style.width = percent + '%';
                countdownText.textContent = remaining + ' detik';

                if (remaining <= 0) {
                    clearInterval(countdownTimer);
                    fetchNewToken();
                }
            }, 500);
        }

        // Fullscreen Toggle
        document.getElementById('fullscreenBtn')?.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => alert(err.message));
            } else {
                document.exitFullscreen();
            }
        });

        // Initialize on load
        fetchNewToken();
    </script>
</body>
</html>
