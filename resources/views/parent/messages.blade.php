@extends('layouts.app')

@section('title', 'Kotak Pesan Orang Tua')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kotak Pesan & Catatan Madrasah</h1>
            <p class="text-sm text-slate-500 mt-1">Komunikasi terarah dari wali kelas dan guru mengenai perkembangan putra-putri Anda.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    <!-- Filter Buttons -->
    <div class="flex items-center gap-2">
        <a href="{{ route('parent.messages', ['filter' => 'all']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ $filter === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            Semua Pesan
        </a>
        <a href="{{ route('parent.messages', ['filter' => 'unread']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ $filter === 'unread' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            Belum Dibaca ({{ $unreadCount }})
        </a>
    </div>

    <!-- Messages List -->
    <div class="space-y-3">
        @forelse($messages as $msg)
        <div class="bg-white p-5 rounded-3xl border {{ $msg->is_read ? 'border-slate-200' : 'border-emerald-300 ring-2 ring-emerald-500/10' }} shadow-xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $msg->category_badge }}">
                        {{ $msg->category_label }}
                    </span>
                    <span class="text-xs font-semibold text-slate-800">
                        Untuk: <strong class="text-emerald-800">{{ $msg->student->full_name }}</strong> (NIS: {{ $msg->student->nis }})
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">Dari: <strong>{{ $msg->sender->name }}</strong></span>
                    <span class="text-xs text-slate-400">&bull; {{ $msg->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <div>
                <h3 class="text-base font-bold text-slate-900">{{ $msg->title }}</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed whitespace-pre-line">{{ $msg->message }}</p>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <div>
                    @if($msg->is_read)
                        <span class="text-[11px] text-slate-400">Dibaca pada {{ $msg->read_at?->isoFormat('D MMM Y, H:i') }}</span>
                    @else
                        <span class="text-[11px] font-semibold text-emerald-600">Pesan Baru</span>
                    @endif
                </div>

                @if(!$msg->is_read)
                <form method="POST" action="{{ route('parent.messages.read', $msg) }}">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg border border-emerald-200 transition">
                        Tandai Telah Dibaca
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 text-sm">
            Tidak ada pesan pada filter ini.
        </div>
        @endforelse

        <div>
            {{ $messages->links() }}
        </div>
    </div>
</div>
@endsection
