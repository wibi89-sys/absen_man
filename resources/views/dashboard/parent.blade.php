@extends('layouts.app')

@section('title', 'Portal Orang Tua / Wali')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-800 text-white shadow-md relative overflow-hidden">
        <div class="relative z-10">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-700/60 text-emerald-200 border border-emerald-500/30 mb-2">
                Portal Orang Tua & Wali Murid Terverifikasi
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Assalamu'alaikum, {{ $parent?->full_name ?? $user->name }}</h1>
            <p class="text-emerald-100 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                Pantau kehadiran harian, kepatuhan ibadah salat berjamaah, dan informasi resmi perkembangan pendidikan putra-putri Anda secara transparan dan aman.
            </p>
        </div>
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-700/20 rounded-full blur-2xl"></div>
    </div>

    <!-- Notice for unverified pending child links -->
    @if($unverifiedCount > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-amber-900 text-xs sm:text-sm flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <strong class="font-semibold block mb-0.5">Pemberitahuan Verifikasi Akun:</strong>
            <p class="text-amber-800 text-xs leading-relaxed">
                Terdapat <strong>{{ $unverifiedCount }}</strong> data tautan anak yang masih dalam proses verifikasi identitas resmi oleh Staf Tata Usaha. Data kehadiran dan ibadah siswa baru dapat diakses setelah tautan dinyatakan sah demi menjamin privasi anak.
            </p>
        </div>
    </div>
    @endif

    <!-- Verified Children Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Putra-Putri Anda</h2>
                <p class="text-xs text-slate-500">Akses ringkasan presensi sekolah dan ibadah yang telah diverifikasi pihak madrasah.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800">
                {{ $children->count() }} Siswa Terhubung Sah
            </span>
        </div>

        @if($children->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach($children as $child)
            @php
                $todayAttendance = $todayAttendances->get($child->id);
                $todayWorship = $todayWorships->get($child->id);
            @endphp
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs hover:border-emerald-300 transition p-6 space-y-5 flex flex-col justify-between">
                <!-- Child Info Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-extrabold flex items-center justify-center text-base shadow-sm">
                            {{ strtoupper(substr($child->full_name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base leading-tight">{{ $child->full_name }}</h3>
                            <div class="text-xs text-slate-500 font-mono mt-0.5">NIS: {{ $child->nis }}</div>
                            <div class="text-xs text-slate-600 font-medium mt-1">
                                Rombel: <strong class="text-emerald-800">{{ $child->currentClass?->name ?? 'Belum Ditentukan' }}</strong>
                                @if($child->currentClass?->homeroomTeacher)
                                    &bull; Wali Kelas: {{ $child->currentClass->homeroomTeacher->user->name }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                        Terverifikasi
                    </span>
                </div>

                <!-- Today's Snapshot Grid -->
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100">
                    <!-- School Attendance Snapshot -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Presensi Masuk Hari Ini</span>
                        <div class="mt-1.5 flex items-center gap-2">
                            @if($todayAttendance)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $todayAttendance->status_badge }}">
                                    {{ $todayAttendance->status_label }}
                                </span>
                                @if($todayAttendance->check_in_time)
                                    <span class="text-xs font-mono text-slate-500">{{ $todayAttendance->check_in_time }} WIB</span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                                    Belum Dicatat
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Worship Attendance Snapshot -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Salat Berjamaah Hari Ini</span>
                        <div class="mt-1.5 flex items-center gap-2">
                            @if($todayWorship)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $todayWorship->status_badge }}">
                                    {{ $todayWorship->status_label }}
                                </span>
                                <span class="text-[11px] text-slate-500 truncate">{{ $todayWorship->session?->type?->name }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                                    Belum Mulai Sesi
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Deep-dive Action Button -->
                <div class="pt-2">
                    <a href="{{ route('parent.children.show', $child) }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                        Lihat Rekap Lengkap Presensi & Ibadah
                        <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-3xl border border-slate-200 p-8 text-center max-w-md mx-auto">
            <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">Belum Ada Siswa Terhubung</h3>
            <p class="text-xs text-slate-500 mt-1">Akun Anda belum memiliki data siswa yang terverifikasi resmi oleh madrasah. Silakan hubungi Tata Usaha Madrasah.</p>
        </div>
        @endif
    </div>

    <!-- Secondary Widgets: Announcements & Direct Messages -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
        <!-- Announcements Widget -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">Pengumuman Madrasah</h3>
                </div>
                <a href="{{ route('parent.announcements') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">
                    Semua Pengumuman &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($announcements as $ann)
                <div class="py-3 first:pt-0 last:pb-0 space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $ann->category_badge }}">
                            {{ $ann->category_label }}
                        </span>
                        <span class="text-[11px] text-slate-400">{{ $ann->published_at->isoFormat('D MMM Y') }}</span>
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800">{{ $ann->title }}</h4>
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $ann->content }}</p>
                </div>
                @empty
                <div class="py-6 text-center text-slate-400 text-xs">
                    Belum ada pengumuman baru untuk orang tua.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Messages from Madrasah & Homeroom Teacher -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Pesan & Catatan Wali Kelas</h3>
                    </div>
                </div>
                @if($unreadMessages > 0)
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">
                    {{ $unreadMessages }} Baru
                </span>
                @endif
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentMessages as $msg)
                <div class="py-3 first:pt-0 last:pb-0 space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-semibold text-slate-500">
                            Untuk: <strong class="text-slate-700">{{ $msg->student->full_name }}</strong> &bull; Dari: {{ $msg->sender->name }}
                        </span>
                        <span class="text-[10px] text-slate-400">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800">{{ $msg->title }}</h4>
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $msg->message }}</p>
                </div>
                @empty
                <div class="py-6 text-center text-slate-400 text-xs">
                    Belum ada pesan atau catatan khusus dari wali kelas.
                </div>
                @endforelse
            </div>

            @if($recentMessages->isNotEmpty())
            <div class="pt-2 border-t border-slate-100 text-center">
                <a href="{{ route('parent.messages') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold inline-flex items-center">
                    Buka Seluruh Riwayat Pesan &rarr;
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
