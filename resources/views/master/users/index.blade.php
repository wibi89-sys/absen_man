@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Akun')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Manajemen Pengguna & Hak Akses</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun guru, staf, wali murid, dan siswa serta reset kata sandi jika diperlukan.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter & Pencarian -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('master.users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Nama / Username / Email</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik kata kunci..."
                       class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Peran (Role)</label>
                <select name="role" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Peran</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ $roleName === $r->name ? 'selected' : '' }}>{{ $r->display_name ?? $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Akun</label>
                <select name="status" class="w-full px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                    Cari Pengguna
                </button>
                @if($search || $roleName || $status !== null)
                    <a href="{{ route('master.users.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Pengguna -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">
                Daftar Pengguna Sistem (Total: {{ $users->total() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 min-w-[750px]">
                <thead class="bg-slate-100/70 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Pengguna</th>
                        <th class="py-3 px-4">Username & Email</th>
                        <th class="py-3 px-4">Peran (Role)</th>
                        <th class="py-3 px-4">Kontak</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/60 transition" x-data="{ openResetModal: false }">
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 truncate">{{ $u->name }}</div>
                                        <div class="text-[10px] text-slate-400">ID: #{{ $u->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-mono text-[11px] text-slate-800 font-semibold">{{ $u->username ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $u->email }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($u->roles as $role)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            {{ $role->display_name ?? $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-[10px] text-slate-400 italic">Tanpa Peran</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($u->phone)
                                    <span class="text-xs text-slate-700 font-mono">{{ $u->phone }}</span>
                                @else
                                    <span class="text-[10px] text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $u->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <!-- Tombol Reset Password -->
                                    <button type="button" @click="openResetModal = true" class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold border border-amber-200 transition">
                                        Reset Sandi
                                    </button>

                                    <!-- Tombol Toggle Status -->
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('master.users.toggle-status', $u) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun ini?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold transition border {{ $u->is_active ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200' }}">
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Modal Reset Sandi -->
                                <div x-show="openResetModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 text-left" style="display: none;">
                                    <div @click.away="openResetModal = false" class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl space-y-4">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                            <div>
                                                <h4 class="font-bold text-sm text-slate-900">Reset Kata Sandi Pengguna</h4>
                                                <p class="text-xs text-slate-500 mt-0.5">{{ $u->name }} ({{ $u->username ?? $u->email }})</p>
                                            </div>
                                            <button @click="openResetModal = false" class="text-slate-400 hover:text-slate-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>

                                        <form action="{{ route('master.users.reset-password', $u) }}" method="POST" class="space-y-3">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru *</label>
                                                <input type="password" name="password" required placeholder="Minimal 8 karakter"
                                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Kata Sandi *</label>
                                                <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru"
                                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                            </div>

                                            <div class="p-2.5 bg-slate-50 rounded-xl text-[11px] text-slate-600">
                                                Kata sandi baru akan langsung aktif dan dicatat ke log audit sistem.
                                            </div>

                                            <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                                                <button type="button" @click="openResetModal = false" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">Batal</button>
                                                <button type="submit" class="px-4 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-xs">Simpan Sandi Baru</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                Tidak ada data pengguna yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
