@extends('layouts.app')

@section('title', 'Kegiatan Siswa & Ekstrakurikuler')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kegiatan Siswa & Ekstrakurikuler</h1>
            <p class="text-sm text-slate-500 mt-1">Pengelolaan organisasi siswa, ekskul, presensi kegiatan, dan penentuan kelayakan salat Asar.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Tambah Kegiatan -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
            <h3 class="text-base font-bold text-slate-900">Tambah Kegiatan Baru</h3>
            <form method="POST" action="{{ route('activities.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kegiatan / Organisasi <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Pramuka, OSIM, PMR, Paskibra" class="w-full text-sm border-slate-300 rounded-xl px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full text-sm border-slate-300 rounded-xl px-3 py-2 border">
                        <option value="ekstrakurikuler">Ekstrakurikuler</option>
                        <option value="organisasi">Organisasi Siswa (OSIM/MPK)</option>
                        <option value="keagamaan">Kegiatan Keagamaan / Rohis</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Guru Pembina</label>
                    <select name="supervisor_id" class="w-full text-sm border-slate-300 rounded-xl px-3 py-2 border">
                        <option value="">-- Pilih Pembina (Opsional) --</option>
                        @foreach($teachers as $tc)
                            <option value="{{ $tc->id }}">{{ $tc->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hari Rutin</label>
                        <select name="day_of_week" class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">
                            <option value="">Fleksibel</option>
                            <option value="1">Senin</option>
                            <option value="2">Selasa</option>
                            <option value="3">Rabu</option>
                            <option value="4">Kamis</option>
                            <option value="5">Jumat</option>
                            <option value="6">Sabtu</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Latihan</label>
                        <input type="text" name="location" placeholder="Lapangan, Lab, dll" class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 border">
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                    Simpan Kegiatan
                </button>
            </form>
        </div>

        <!-- Daftar Kegiatan Cards -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($activities as $act)
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col justify-between space-y-4 hover:border-emerald-300 transition">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 capitalize">
                            {{ $act->category }}
                        </span>
                        <span class="text-xs text-slate-500 font-semibold">
                            {{ $act->active_members_count }} Anggota
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900">{{ $act->name }}</h3>
                    <p class="text-xs text-slate-500">
                        Pembina: <strong class="text-slate-700">{{ $act->supervisor?->user?->name ?? 'Belum Ditugaskan' }}</strong>
                    </p>
                    <p class="text-xs text-slate-500">
                        Jadwal: <span class="font-medium text-slate-800">{{ $act->day_name }}</span> &bull; {{ $act->location ?? 'Area Madrasah' }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('activities.show', $act) }}" class="inline-flex items-center text-xs font-bold text-emerald-700 hover:text-emerald-800">
                        Presensi & Anggota &rarr;
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 text-sm">
                Belum ada kegiatan ekstrakurikuler yang dibuat.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
