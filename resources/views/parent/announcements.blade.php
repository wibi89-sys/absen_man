@extends('layouts.app')

@section('title', 'Papan Pengumuman Madrasah')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Papan Pengumuman Madrasah</h1>
            <p class="text-sm text-slate-500 mt-1">Informasi resmi seputar kegiatan akademik, jadwal libur, dan agenda madrasah.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali ke Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($announcements as $ann)
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col justify-between space-y-4 hover:border-emerald-300 transition">
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $ann->category_badge }}">
                        {{ $ann->category_label }}
                    </span>
                    @if($ann->is_pinned)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">
                        Dipin
                    </span>
                    @endif
                </div>
                <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $ann->title }}</h3>
                <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ $ann->content }}</p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span>{{ $ann->published_at->isoFormat('D MMMM Y') }}</span>
                <span>Oleh: {{ $ann->creator->name }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 text-sm">
            Belum ada pengumuman madrasah saat ini.
        </div>
        @endforelse
    </div>

    <div>
        {{ $announcements->links() }}
    </div>
</div>
@endsection
