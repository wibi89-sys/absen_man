@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    restoreModalOpen: false,
    restoreFilename: '',
    uploadModalOpen: false,
    resetModalOpen: false,
    resetMode: 'transactions',
    confirmText: '',
    adminPassword: '',
    newAdminPassword: '',
    openRestore(filename) {
        this.restoreFilename = filename;
        this.restoreModalOpen = true;
    }
}">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Pemeliharaan & Cadangan Database</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola pencadangan berkas SQL, pemulihan data (restore), dan reset basis data produksi.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('settings.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition">
                &larr; Identitas Madrasah
            </a>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition shadow-2xs">
                Dashboard
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-6 text-xs sm:text-sm font-semibold">
        <a href="{{ route('settings.index') }}" class="pb-3 text-slate-500 hover:text-slate-800 border-b-2 border-transparent transition">
            Identitas Lembaga & Profil
        </a>
        <a href="{{ route('settings.database.index') }}" class="pb-3 text-emerald-700 border-b-2 border-emerald-600 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
            <span>Database & Cadangan Data</span>
        </a>
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs flex items-center gap-2.5 shadow-2xs">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs flex items-center gap-2.5 shadow-2xs">
        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs space-y-1 shadow-2xs">
        @foreach($errors->all() as $err)
            <div class="flex items-start gap-1.5">
                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $err }}</span>
            </div>
        @endforeach
    </div>
    @endif

    <!-- System & Database Overview Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 sm:p-4 shadow-xs">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Basis Data</span>
            <div class="text-sm sm:text-base font-bold text-slate-900 mt-1 truncate" title="{{ $connectionInfo['database'] }}">
                {{ $connectionInfo['database'] }}
            </div>
            <span class="text-[11px] text-slate-500 font-mono mt-0.5 block truncate">{{ $connectionInfo['driver'] }} &bull; {{ $connectionInfo['host'] }}</span>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 sm:p-4 shadow-xs">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Ukuran Database</span>
            <div class="text-base sm:text-xl font-bold text-emerald-700 mt-1 font-mono">
                {{ $dbSize['human'] }}
            </div>
            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ $dbSize['tables_count'] }} Tabel Terstruktur</span>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 sm:p-4 shadow-xs">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Lingkungan (Env)</span>
            <div class="flex items-center gap-1.5 mt-1">
                @if($connectionInfo['app_env'] === 'production')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        PRODUCTION
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        {{ strtoupper($connectionInfo['app_env']) }}
                    </span>
                @endif
            </div>
            <span class="text-[11px] text-slate-500 mt-0.5 block truncate">
                Debug: {{ $connectionInfo['app_debug'] ? 'Aktif' : 'Nonaktif (Aman)' }}
            </span>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 sm:p-4 shadow-xs">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Total Arsip Backup</span>
            <div class="text-base sm:text-xl font-bold text-slate-900 mt-1 font-mono">
                {{ count($backups) }} Berkas
            </div>
            <span class="text-[11px] text-slate-500 mt-0.5 block">Tersimpan di Server</span>
        </div>
    </div>

    <!-- Section 1: Buat Cadangan Database Baru -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Pencadangan Database Instan (.SQL)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Membuat salinan lengkap seluruh tabel dan data sistem. Aman digunakan di shared hosting cPanel tanpa ketergantungan binari mysqldump.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="uploadModalOpen = true"
                        class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Unggah File Restore</span>
                </button>
            </div>
        </div>

        <form action="{{ route('settings.database.backup') }}" method="POST" class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-2.5">
            @csrf
            <input type="text" name="description" placeholder="Catatan opsional (misal: Backup Rutin Bulanan, Sebelum Ujian...)"
                   class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition shadow-2xs">
            <button type="submit" 
                    class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm transition flex items-center justify-center gap-1.5 shadow-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span>Cadangkan Sekarang</span>
            </button>
        </form>
    </div>

    <!-- Section 2: Daftar Arsip Cadangan (Backup History) -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-3.5 sm:p-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm">Riwayat Berkas Cadangan</h3>
                <p class="text-[11px] text-slate-500">Berkas SQL tersimpan di direktori privat server: storage/app/backups</p>
            </div>
            <span class="text-xs font-mono font-semibold text-slate-500 bg-white px-2.5 py-1 rounded-md border border-slate-200">
                {{ count($backups) }} Berkas
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-100/70 text-slate-700 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-3">Nama Berkas SQL</th>
                        <th class="py-2.5 px-3">Tipe</th>
                        <th class="py-2.5 px-3 text-center">Ukuran</th>
                        <th class="py-2.5 px-3">Waktu Pembuatan</th>
                        <th class="py-2.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($backups as $idx => $b)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3 font-mono font-semibold text-slate-900">
                            {{ $b['filename'] }}
                        </td>
                        <td class="py-2.5 px-3">
                            @if($b['is_auto'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                    Auto-Rollback
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    Manual
                                </span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono font-semibold text-slate-700">
                            {{ $b['human_size'] }}
                        </td>
                        <td class="py-2.5 px-3 text-slate-500">
                            {{ $b['created_at'] }}
                        </td>
                        <td class="py-2.5 px-3 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('settings.database.download', ['file' => $b['filename']]) }}" 
                                   class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition shadow-2xs"
                                   title="Unduh ke komputer lokal">
                                    Unduh
                                </a>
                                <button type="button" @click="openRestore('{{ $b['filename'] }}')"
                                        class="px-2.5 py-1 rounded-md bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-semibold text-[11px] transition shadow-2xs"
                                        title="Kembalikan database ke kondisi berkas ini">
                                    Restore
                                </button>
                                <form action="{{ route('settings.database.delete') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip cadangan ini?')">
                                    @csrf
                                    <input type="hidden" name="filename" value="{{ $b['filename'] }}">
                                    <button type="submit" class="px-2 py-1 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-[11px] transition" title="Hapus berkas cadangan">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            Belum ada berkas cadangan database tersimpan. Klik tombol <strong>Cadangkan Sekarang</strong> di atas untuk membuat cadangan pertama.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Zona Bahaya & Reset Database (Clean Slate / Reset Transaksi) -->
    <div class="bg-white rounded-xl border border-rose-200/90 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 bg-rose-50/60 border-b border-rose-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-rose-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Zona Tindakan Kritis & Reset Database
                </h3>
                <p class="text-xs text-rose-700 mt-0.5">
                    Kosongkan database kembali bersih untuk persiapan peluncuran produksi atau pembersihan berkala data uji coba.
                </p>
            </div>

            <button type="button" @click="resetModalOpen = true"
                    class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm transition flex items-center justify-center gap-1.5 shadow-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Buka Menu Reset Database</span>
            </button>
        </div>

        <div class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-600">
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1.5">
                <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Opsi 1: Kosongkan Transaksi Saja (Rekomendasi Uji Coba Selesai)
                </span>
                <p class="text-slate-500 leading-relaxed">
                    Hanya menghapus riwayat kehadiran gerbang, rekaman salat berjamaah, pesan pengumuman, dan log audit. 
                    <strong>Data master guru, akun pimpinan, rombel, dan siswa tetap utuh.</strong>
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-rose-200 bg-rose-50/30 space-y-1.5">
                <span class="font-bold text-rose-900 text-xs flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-600"></span> Opsi 2: Reset Total Bersih Pabrik (Clean Slate Produksi)
                </span>
                <p class="text-rose-700/80 leading-relaxed">
                    Menghapus seluruh tabel dan data dummy, lalu menyiapkan 1 akun Super Administrator baru yang bersih tanpa data dummy sama sekali. Siap untuk diisi data riil madrasah.
                </p>
            </div>
        </div>
    </div>

    <!-- MODAL 1: KONFIRMASI RESTORE FILE -->
    <div x-show="restoreModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200" @click.away="restoreModalOpen = false">
            <div class="flex items-center gap-3 text-amber-600 mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center border border-amber-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Konfirmasi Pemulihan Database</h4>
                    <p class="text-[11px] text-slate-500">Tindakan ini akan menimpa data yang sedang berjalan</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                Anda akan merestore database dari berkas: <br>
                <code class="font-mono font-bold text-amber-900 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 block mt-1 break-all" x-text="restoreFilename"></code>
            </p>

            <form action="{{ route('settings.database.restore') }}" method="POST" class="space-y-3.5">
                @csrf
                <input type="hidden" name="filename" :value="restoreFilename">
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Masukkan Kata Sandi Akun Anda untuk Konfirmasi:
                    </label>
                    <input type="password" name="password" required placeholder="Kata sandi akun yang sedang aktif"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 focus:outline-none">
                </div>

                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-[11px] text-slate-500">
                    Sistem otomatis membuat arsip cadangan pengaman darurat sebelum restore dieksekusi.
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="restoreModalOpen = false" class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition shadow-xs">
                        Lanjutkan Restore
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: UPLOAD FILE SQL UNTUK RESTORE -->
    <div x-show="uploadModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200" @click.away="uploadModalOpen = false">
            <h4 class="font-bold text-slate-900 text-sm mb-1">Unggah Berkas SQL Cadangan</h4>
            <p class="text-xs text-slate-500 mb-4">Pilih file .sql dari perangkat lokal Anda untuk dipulihkan ke server.</p>

            <form action="{{ route('settings.database.upload-restore') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Berkas SQL (.sql)</label>
                    <input type="file" name="sql_file" accept=".sql" required
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi Akun Anda</label>
                    <input type="password" name="password" required placeholder="Kata sandi akun yang sedang aktif"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="uploadModalOpen = false" class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs">
                        Unggah & Pulihkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: KONFIRMASI RESET DATABASE DENGAN PROTEKSI TINGGI -->
    <div x-show="resetModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-rose-300" @click.away="resetModalOpen = false">
            <div class="flex items-center gap-3 text-rose-600 mb-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center border border-rose-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-rose-900 text-base">Konfirmasi Reset Basis Data</h4>
                    <p class="text-[11px] text-rose-700">Tindakan permanen dengan proteksi keamanan ganda</p>
                </div>
            </div>

            <form action="{{ route('settings.database.reset') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Lingkup Reset:</label>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2.5 p-3 rounded-lg border cursor-pointer transition select-none"
                               :class="resetMode === 'transactions' ? 'bg-amber-50/60 border-amber-300 text-amber-900' : 'bg-white border-slate-200 text-slate-700'">
                            <input type="radio" name="mode" value="transactions" x-model="resetMode" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="font-bold text-xs block">Kosongkan Riwayat Transaksi Saja (Rekomendasi)</span>
                                <span class="text-[11px] text-slate-500 block">Menghapus presensi gerbang, salat berjamaah, dan log. Akun guru, rombel, dan siswa tetap ada.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 p-3 rounded-lg border cursor-pointer transition select-none"
                               :class="resetMode === 'fresh' ? 'bg-rose-50/60 border-rose-300 text-rose-900' : 'bg-white border-slate-200 text-slate-700'">
                            <input type="radio" name="mode" value="fresh" x-model="resetMode" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div>
                                <span class="font-bold text-xs block">Reset Total Bersih Pabrik (Clean Slate Produksi)</span>
                                <span class="text-[11px] text-slate-500 block">Menghapus SELURUH data dummy dan menyiapkan 1 akun Super Administrator baru untuk produksi.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div x-show="resetMode === 'fresh'" x-cloak class="p-3 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                    <label class="block text-xs font-semibold text-slate-700">Password Baru untuk Akun Super Administrator (Opsional)</label>
                    <input type="text" name="new_admin_password" placeholder="Default: AdminMadrasah2026!"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none">
                    <p class="text-[10px] text-slate-500">Akun baru yang dibuat: username: <code>superadmin</code> / email: <code>admin@madrasah.sch.id</code></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-rose-900 mb-1">
                        Ketik kata <span class="font-mono bg-rose-100 px-1.5 py-0.5 rounded text-rose-800 font-bold">RESET</span> untuk mengonfirmasi:
                    </label>
                    <input type="text" name="confirm_text" x-model="confirmText" required placeholder="Ketik RESET huruf besar"
                           class="w-full px-3 py-2 rounded-lg border border-rose-300 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Akun Anda Saat Ini:</label>
                    <input type="password" name="password" required placeholder="Masukkan kata sandi login Anda"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 focus:outline-none">
                </div>

                <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Sistem secara otomatis membuat cadangan darurat sebelum proses reset dimulai.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="resetModalOpen = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                        Batalkan
                    </button>
                    <button type="submit" 
                            :disabled="confirmText !== 'RESET'"
                            class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs transition shadow-xs">
                        Jalankan Reset Database
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
