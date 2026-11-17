@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Tahun Ajaran & Semester</h2>
            <p class="text-xs text-slate-500 mt-0.5">Atur kalender tahun akademik dan semester yang sedang aktif.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Tambah -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs h-fit">
            <h3 class="font-bold text-slate-900 text-sm mb-3">Tambah Tahun Ajaran</h3>
            <form action="{{ route('master.academic-years.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Tahun Ajaran *</label>
                    <input type="text" name="name" placeholder="Contoh: 2027/2028" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tanggal Mulai *</label>
                    <input type="date" name="start_date" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tanggal Selesai *</label>
                    <input type="date" name="end_date" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs text-slate-700 font-semibold">Jadikan Tahun Aktif</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition mt-2">
                    Simpan Tahun Ajaran
                </button>
            </form>
        </div>

        <!-- Daftar Tahun Ajaran -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Tahun Ajaran</th>
                            <th class="py-3 px-4">Periode</th>
                            <th class="py-3 px-4">Semester</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($years as $y)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $y->name }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $y->start_date?->format('d M Y') }} s/d {{ $y->end_date?->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex gap-1.5">
                                    @foreach($y->semesters as $sem)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $sem->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                            Sem. {{ $sem->semester == '1' ? 'Ganjil' : 'Genap' }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($y->is_active)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aktif Berjalan
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                        Arsip
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if(!$y->is_active)
                                <form action="{{ route('master.academic-years.set-active', $y) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                        Aktifkan
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-slate-400 font-medium">Sedang Digunakan</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">Belum ada tahun ajaran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
