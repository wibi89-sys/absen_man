@extends('layouts.app')

@section('title', 'Laporan Perkembangan: ' . $student->full_name)

@section('content')
<div class="space-y-6">
    <!-- Student Header Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-700 to-teal-600 text-white font-extrabold flex items-center justify-center text-xl shadow-md">
                {{ strtoupper(substr($student->full_name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $student->full_name }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Anak Terverifikasi Sah
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-mono mt-0.5">NIS: {{ $student->nis }} &bull; NISN: {{ $student->nisn ?? '-' }}</p>
                <div class="text-xs text-slate-700 font-medium mt-1">
                    Kelas: <strong class="text-emerald-800">{{ $student->currentClass?->name ?? 'Belum Ada Kelas' }}</strong>
                    @if($student->currentClass?->homeroomTeacher)
                        &bull; Wali Kelas: <strong>{{ $student->currentClass->homeroomTeacher->user->name }}</strong>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-slate-200" x-data="{ currentTab: '{{ $tab }}' }">
        <nav class="flex space-x-6">
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'overview']) }}" class="py-3 px-1 text-sm font-semibold border-b-2 transition flex items-center gap-2 {{ $tab === 'overview' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Presensi Sekolah</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'worship']) }}" class="py-3 px-1 text-sm font-semibold border-b-2 transition flex items-center gap-2 {{ $tab === 'worship' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Monitoring Ibadah</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'messages']) }}" class="py-3 px-1 text-sm font-semibold border-b-2 transition flex items-center gap-2 {{ $tab === 'messages' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <span>Catatan & Pesan Khusus</span>
            </a>
        </nav>
    </div>

    <!-- Tab 1: Presensi Sekolah -->
    @if($tab === 'overview')
    <div class="space-y-6">
        <!-- Metrics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-slate-500 uppercase block">Tingkat Kehadiran</span>
                <span class="text-2xl font-bold {{ $attendanceRate >= 85 ? 'text-emerald-700' : 'text-amber-700' }} mt-1 block">{{ $attendanceRate }}%</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-emerald-700 uppercase block">Hadir Tepat Waktu</span>
                <span class="text-2xl font-bold text-emerald-800 mt-1 block">{{ $attendanceSummary['present'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-orange-700 uppercase block">Terlambat</span>
                <span class="text-2xl font-bold text-orange-800 mt-1 block">{{ $attendanceSummary['late'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-blue-700 uppercase block">Sakit</span>
                <span class="text-2xl font-bold text-blue-800 mt-1 block">{{ $attendanceSummary['sick'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-purple-700 uppercase block">Izin</span>
                <span class="text-2xl font-bold text-purple-800 mt-1 block">{{ $attendanceSummary['excused'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-rose-700 uppercase block">Tidak Hadir (Alfa)</span>
                <span class="text-2xl font-bold text-rose-800 mt-1 block">{{ $attendanceSummary['absent'] }}</span>
            </div>
        </div>

        <!-- Attendance History Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Riwayat Kehadiran Sekolah (30 Hari Terakhir)</h3>
                <span class="text-xs text-slate-500">{{ $dailyAttendances->count() }} hari tercatat</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Status Kehadiran</th>
                            <th class="py-3 px-4">Jam Masuk</th>
                            <th class="py-3 px-4">Jam Pulang</th>
                            <th class="py-3 px-4">Keterlambatan</th>
                            <th class="py-3 px-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($dailyAttendances as $att)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $att->date->isoFormat('dddd, D MMMM Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $att->status_badge }}">
                                    {{ $att->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ $att->check_in_time ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ $att->check_out_time ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono">
                                @if($att->minutes_late > 0)
                                    <span class="text-rose-600 font-semibold">{{ $att->minutes_late }} menit</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $att->notes ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada rekaman presensi dalam rentang tanggal ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 2: Monitoring Ibadah -->
    @if($tab === 'worship')
    <div class="space-y-6">
        <!-- Privacy Notice -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-emerald-950 text-xs sm:text-sm flex gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <div>
                <strong class="font-semibold block mb-0.5">Penetapan Nilai Karakter Ibadah:</strong>
                <p class="text-emerald-800 text-xs leading-relaxed">
                    Data berikut menyajikan rekap partisipasi salat berjamaah yang telah diverifikasi final oleh Tim Petugas Keagamaan. Seluruh izin khusus/dispensasi sah (termasuk kondisi kesehatan/syar'i) diberi label netral <strong>"Dispensasi"</strong> tanpa mengurangi nilai adab siswa.
                </p>
            </div>
        </div>

        <!-- Worship Metrics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-slate-500 uppercase block">Kepatuhan Ibadah</span>
                <span class="text-2xl font-bold {{ $worshipRate >= 80 ? 'text-emerald-700' : 'text-amber-700' }} mt-1 block">{{ $worshipRate }}%</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-emerald-700 uppercase block">Hadir Berjamaah</span>
                <span class="text-2xl font-bold text-emerald-800 mt-1 block">{{ $worshipSummary['present'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-orange-700 uppercase block">Masbuk</span>
                <span class="text-2xl font-bold text-orange-800 mt-1 block">{{ $worshipSummary['late'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-purple-700 uppercase block">Dispensasi Resmi</span>
                <span class="text-2xl font-bold text-purple-800 mt-1 block">{{ $worshipSummary['exempt'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 text-center shadow-xs">
                <span class="text-[11px] font-semibold text-rose-700 uppercase block">Tidak Hadir</span>
                <span class="text-2xl font-bold text-rose-800 mt-1 block">{{ $worshipSummary['absent'] }}</span>
            </div>
        </div>

        <!-- Worship History Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Riwayat Pelaksanaan Salat Berjamaah</h3>
                <span class="text-xs text-slate-500">{{ $worshipParticipants->count() }} sesi tercatat</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Kegiatan Salat</th>
                            <th class="py-3 px-4">Gelombang & Lokasi</th>
                            <th class="py-3 px-4">Bukti Presensi</th>
                            <th class="py-3 px-4">Status Terverifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($worshipParticipants as $wp)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $wp->session?->date->isoFormat('dddd, D MMMM Y') }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $wp->session?->type?->name }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                Gelombang {{ $wp->session?->wave_number }} &bull; {{ $wp->session?->location?->name ?? 'Masjid' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($wp->scanRecord)
                                    <span class="text-emerald-700 font-medium">Scan {{ $wp->scanRecord->scanned_at->format('H:i') }} WIB</span>
                                @else
                                    <span class="text-slate-400 italic">Konfirmasi Petugas</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $wp->status_badge }}">
                                    {{ $wp->status_label }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Belum ada sesi ibadah yang terdaftar pada rentang tanggal ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab 3: Messages -->
    @if($tab === 'messages')
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Pesan & Catatan Perkembangan Siswa</h3>
            <span class="text-xs text-slate-500">Dari Wali Kelas & Guru Madrasah</span>
        </div>

        <div class="space-y-3">
            @forelse($messages as $msg)
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $msg->category_badge }}">
                            {{ $msg->category_label }}
                        </span>
                        <span class="text-xs font-semibold text-slate-700">Dari: {{ $msg->sender->name }}</span>
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $msg->created_at->diffForHumans() }}</span>
                </div>
                <h4 class="text-sm font-bold text-slate-900">{{ $msg->title }}</h4>
                <p class="text-xs text-slate-600 leading-relaxed">{{ $msg->message }}</p>
            </div>
            @empty
            <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                Belum ada catatan atau pesan khusus untuk {{ $student->full_name }}.
            </div>
            @endforelse

            <div>
                {{ $messages->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
