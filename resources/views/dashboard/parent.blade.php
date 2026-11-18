@extends('layouts.app')

@section('title', 'Portal Orang Tua / Wali')

@section('content')
<div class="space-y-5">
    <!-- Welcome Header Card -->
    <div class="p-4 sm:p-5 rounded-xl bg-white border border-slate-200/80 border-l-4 border-l-emerald-600 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-semibold text-emerald-700 tracking-wide uppercase">Portal Orang Tua & Wali Terverifikasi</span>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">Assalamu'alaikum, {{ $parent?->full_name ?? $user->name }}</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Pantau kehadiran harian, kepatuhan ibadah salat, dan pengumuman resmi madrasah untuk putra-putri Anda.
            </p>
        </div>
        <div class="shrink-0">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                {{ $children->count() }} Siswa Terhubung
            </span>
        </div>
    </div>

    <!-- Notice for unverified pending child links -->
    @if($unverifiedCount > 0)
    <div class="bg-amber-50 border border-amber-200/80 rounded-xl p-3.5 text-amber-900 text-xs flex items-start gap-2.5">
        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <strong class="font-medium block mb-0.5">Pemberitahuan Verifikasi Akun:</strong>
            <p class="text-amber-800 leading-relaxed">
                Terdapat <strong>{{ $unverifiedCount }}</strong> data tautan siswa yang masih diverifikasi oleh Staf Tata Usaha demi keamanan privasi data anak.
            </p>
        </div>
    </div>
    @endif

    <!-- Verified Children Section -->
    <div class="space-y-3.5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm sm:text-base font-semibold text-slate-900">Putra-Putri Anda</h2>
                <p class="text-xs text-slate-500">Ringkasan presensi sekolah dan ibadah yang tercatat hari ini.</p>
            </div>
        </div>

        @if($children->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($children as $child)
            @php
                $todayAttendance = $todayAttendances->get($child->id);
                $todayWorship = $todayWorships->get($child->id);
            @endphp
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs hover:border-emerald-300/80 transition p-4 sm:p-5 space-y-4 flex flex-col justify-between">
                <!-- Child Info Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white font-bold flex items-center justify-center text-sm shrink-0">
                            {{ strtoupper(substr($child->full_name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 text-sm leading-tight truncate">{{ $child->full_name }}</h3>
                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">NIS: {{ $child->nis }}</div>
                            <div class="text-[11px] text-slate-600 mt-0.5 truncate">
                                Kelas: <strong class="text-emerald-700 font-medium">{{ $child->currentClass?->name ?? 'Belum Ditentukan' }}</strong>
                                @if($child->currentClass?->homeroomTeacher)
                                    &bull; Wali: {{ $child->currentClass->homeroomTeacher->user->name }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60 shrink-0">
                        Terverifikasi
                    </span>
                </div>

                <!-- Today's Snapshot Grid -->
                <div class="grid grid-cols-2 gap-2.5 pt-3 border-t border-slate-100">
                    <!-- School Attendance Snapshot -->
                    <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wider block">Presensi Hari Ini</span>
                        <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                            @if($todayAttendance)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $todayAttendance->status_badge }}">
                                    {{ $todayAttendance->status_label }}
                                </span>
                                @if($todayAttendance->check_in_time)
                                    <span class="text-[11px] font-mono text-slate-500">{{ $todayAttendance->check_in_time }} WIB</span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-200/70 text-slate-600">
                                    Belum Dicatat
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Worship Attendance Snapshot -->
                    <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wider block">Salat Hari Ini</span>
                        <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                            @if($todayWorship)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $todayWorship->status_badge }}">
                                    {{ $todayWorship->status_label }}
                                </span>
                                <span class="text-[10px] text-slate-500 truncate">{{ $todayWorship->session?->type?->name }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-200/70 text-slate-600">
                                    Belum Sesi
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Deep-dive Action Button -->
                <div class="pt-1">
                    <a href="{{ route('parent.children.show', $child) }}" class="w-full inline-flex items-center justify-center px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-medium rounded-lg transition shadow-2xs">
                        Lihat Rekap Lengkap Presensi & Ibadah
                        <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-xl border border-slate-200/80 p-6 text-center max-w-md mx-auto shadow-xs">
            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-2.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-900">Belum Ada Siswa Terhubung</h3>
            <p class="text-xs text-slate-500 mt-1">Akun Anda belum memiliki data siswa yang terverifikasi resmi oleh madrasah. Silakan hubungi Tata Usaha Madrasah.</p>
        </div>
        @endif
    </div>

    <!-- Secondary Widgets: Announcements & Direct Messages -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 pt-1">
        <!-- Announcements Widget -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Pengumuman Madrasah</h3>
                </div>
                <a href="{{ route('parent.announcements') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                    Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($announcements as $ann)
                <div class="py-2.5 first:pt-0 last:pb-0 space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $ann->category_badge }}">
                            {{ $ann->category_label }}
                        </span>
                        <span class="text-[10px] text-slate-400">{{ $ann->published_at->isoFormat('D MMM Y') }}</span>
                    </div>
                    <h4 class="text-xs font-semibold text-slate-800">{{ $ann->title }}</h4>
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $ann->content }}</p>
                </div>
                @empty
                <div class="py-4 text-center text-slate-400 text-xs">
                    Belum ada pengumuman baru untuk orang tua.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Messages from Madrasah & Homeroom Teacher -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-xs sm:text-sm">Pesan Wali Kelas</h3>
                    </div>
                </div>
                @if($unreadMessages > 0)
                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200/60">
                    {{ $unreadMessages }} Baru
                </span>
                @endif
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentMessages as $msg)
                <div class="py-2.5 first:pt-0 last:pb-0 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-slate-500">
                            Untuk: <strong class="text-slate-700 font-medium">{{ $msg->student->full_name }}</strong> &bull; Dari: {{ $msg->sender->name }}
                        </span>
                        <span class="text-[10px] text-slate-400">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <h4 class="text-xs font-semibold text-slate-800">{{ $msg->title }}</h4>
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $msg->message }}</p>
                </div>
                @empty
                <div class="py-4 text-center text-slate-400 text-xs">
                    Belum ada pesan khusus dari wali kelas.
                </div>
                @endforelse
            </div>

            @if($recentMessages->isNotEmpty())
            <div class="pt-2 border-t border-slate-100 text-center">
                <a href="{{ route('parent.messages') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center">
                    Buka Riwayat Pesan &rarr;
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
