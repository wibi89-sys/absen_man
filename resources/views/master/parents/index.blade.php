@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Verifikasi Relasi Orang Tua & Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Penetapan keabsahan hubungan keluarga untuk akses Portal Orang Tua.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('master.parents.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari berdasarkan Nama Orang Tua, NIK, atau Nomor Telepon..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                    Cari
                </button>
                <a href="{{ route('master.parents.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Orang Tua / Wali</th>
                        <th class="py-3 px-4">Hubungan</th>
                        <th class="py-3 px-4">Kontak / Telepon</th>
                        <th class="py-3 px-4">Anak Ditautkan & Status Verifikasi</th>
                        <th class="py-3 px-4 text-right">Tautkan Siswa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($parents as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $p->full_name }}</div>
                            <div class="text-[10px] text-slate-400">NIK: {{ $p->nik ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 uppercase text-[11px] font-semibold text-slate-700">
                            {{ $p->relationship_type }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $p->phone ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($p->students->isNotEmpty())
                                <div class="space-y-1.5">
                                    @foreach($p->students as $st)
                                    <div class="flex items-center space-x-2">
                                        <span class="font-semibold text-slate-800">{{ $st->full_name }} ({{ $st->nis }})</span>
                                        @if($st->pivot->is_verified)
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Sah Terverifikasi
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800">
                                                Belum Sah
                                            </span>
                                        @endif
                                        <form action="{{ route('master.parents.toggle-verify', [$p, $st]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-[10px] text-blue-600 hover:underline">
                                                {{ $st->pivot->is_verified ? 'Batalkan' : 'Verifikasi' }}
                                            </button>
                                        </form>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 italic">Belum ada anak ditautkan</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right" x-data="{ openModal: false }">
                            <button @click="openModal = true" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs transition">
                                + Tautkan Anak
                            </button>

                            <!-- Simple Modal Form -->
                            <div x-show="openModal" class="fixed inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4" style="display: none;">
                                <div @click.away="openModal = false" class="bg-white rounded-2xl p-6 max-w-sm w-full text-left shadow-xl space-y-4">
                                    <h4 class="font-bold text-sm text-slate-900">Tautkan Anak ke {{ $p->full_name }}</h4>
                                    <form action="{{ route('master.parents.link-student', $p) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Siswa</label>
                                            <select name="student_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                                @foreach($unlinkedStudents as $us)
                                                    <option value="{{ $us->id }}">{{ $us->full_name }} ({{ $us->nis }}) - {{ $us->currentClass?->name ?? 'Tanpa Rombel' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex justify-end space-x-2 pt-2">
                                            <button type="button" @click="openModal = false" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">Batal</button>
                                            <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Tautkan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada data orang tua / wali.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($parents->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $parents->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
