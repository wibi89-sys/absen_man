@extends('layouts.app')

@section('title', 'Kirim Pesan ke Orang Tua Siswa')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kirim Catatan ke Orang Tua</h1>
            <p class="text-sm text-slate-500 mt-1">Komunikasi resmi perkembangan akademik, kehadiran, atau adab siswa langsung ke akun orang tua terverifikasi.</p>
        </div>
        <a href="{{ url()->previous() }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    @if($student)
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
        <div>
            <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">Siswa Tujuan</span>
            <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $student->full_name }} (NIS: {{ $student->nis }})</div>
            <span class="text-xs text-slate-500">Kelas: {{ $student->currentClass?->name ?? '-' }}</span>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white border border-emerald-200 text-emerald-800">
            {{ $student->verifiedParents->count() }} Orang Tua Terhubung Sah
        </span>
    </div>
    @endif

    <form method="POST" action="{{ route('communication.messages.store') }}" class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-5">
        @csrf

        @if($student)
            <input type="hidden" name="student_id" value="{{ $student->id }}">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Orang Tua Terverifikasi <span class="text-rose-500">*</span></label>
                <select name="parent_id" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border">
                    @forelse($student->verifiedParents as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ ucfirst($p->relationship_type) }})</option>
                    @empty
                        <option value="" disabled>Belum ada orang tua yang terverifikasi untuk siswa ini</option>
                    @endforelse
                </select>
            </div>
        @else
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">ID Siswa <span class="text-rose-500">*</span></label>
                <input type="number" name="student_id" required placeholder="Masukkan ID Siswa..." class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">ID Orang Tua <span class="text-rose-500">*</span></label>
                <input type="number" name="parent_id" required placeholder="Masukkan ID Orang Tua..." class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 border">
            </div>
        @endif

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori Pesan <span class="text-rose-500">*</span></label>
            <select name="category" required class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border">
                <option value="general">Komunikasi Umum</option>
                <option value="academic">Perkembangan Akademik</option>
                <option value="attendance">Atensi Presensi / Ketidakhadiran</option>
                <option value="worship">Pembiasaan Ibadah</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Subjek / Judul Pesan <span class="text-rose-500">*</span></label>
            <input type="text" name="title" required placeholder="Contoh: Apresiasi Peningkatan Disiplin Masuk Kelas" class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Isi Pesan <span class="text-rose-500">*</span></label>
            <textarea name="message" rows="4" required placeholder="Tuliskan catatan atau pesan konstruktif untuk orang tua..." class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 border"></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Kirim Pesan ke Orang Tua
            </button>
        </div>
    </form>
</div>
@endsection
