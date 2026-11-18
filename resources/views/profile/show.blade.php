@extends('layouts.app')

@section('title', 'Profil & Keamanan Akun')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Profil & Keamanan Akun</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola informasi pribadi, kontak, dan perbarui kata sandi Anda secara berkala.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Kembali ke Dashboard
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

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Mohon perbaiki kesalahan berikut:</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Overview Card -->
    <div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-900 via-teal-900 to-slate-900 text-white shadow-md relative overflow-hidden">
        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
            <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center font-extrabold text-2xl text-emerald-300 shadow-inner">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="space-y-1.5 flex-1 min-w-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <h3 class="text-xl font-bold text-white">{{ $user->name }}</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        {{ $user->is_active ? 'Akun Aktif' : 'Non-Aktif' }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 text-xs text-emerald-200">
                    <span class="font-mono bg-white/10 px-2 py-0.5 rounded-md">{{ $user->username ?? $user->email }}</span>
                    <span>&bull;</span>
                    <span>{{ $user->email }}</span>
                    @if($user->phone)
                        <span>&bull;</span>
                        <span>{{ $user->phone }}</span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 pt-1">
                    @forelse($user->roles as $role)
                        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-emerald-800/80 text-white border border-emerald-700">
                            {{ $role->display_name ?? $role->name }}
                        </span>
                    @empty
                        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-slate-800 text-slate-300">
                            Pengguna Umum
                        </span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Forms Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Left: Edit Personal Details -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Informasi Pribadi</h3>
                    <p class="text-[11px] text-slate-500">Perbarui nama tampilan dan nomor kontak aktif Anda.</p>
                </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Username (ID Pengguna)</label>
                    <input type="text" value="{{ $user->username ?? '-' }}" disabled
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-xs cursor-not-allowed">
                    <span class="text-[10px] text-slate-400 mt-1 block">Username dikelola secara terpusat oleh Administrator Tata Usaha.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-xs cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / HP Aktif</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-1 block">Digunakan untuk keperluan notifikasi presensi atau pengumuman madrasah.</span>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Change Password -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-5" x-data="{ showPass: false }">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Ubah Kata Sandi</h3>
                    <p class="text-[11px] text-slate-500">Ganti kata sandi bawaan demi keamanan akun Anda di server hosting.</p>
                </div>
            </div>

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Saat Ini *</label>
                    <input :type="showPass ? 'text' : 'password'" name="current_password" required placeholder="Masukkan kata sandi lama Anda"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru *</label>
                    <input :type="showPass ? 'text' : 'password'" name="password" required placeholder="Minimal 8 karakter"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Kata Sandi Baru *</label>
                    <input :type="showPass ? 'text' : 'password'" name="password_confirmation" required placeholder="Ketik ulang kata sandi baru"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="inline-flex items-center text-slate-600 cursor-pointer">
                        <input type="checkbox" x-model="showPass" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mr-2">
                        <span>Tampilkan Karakter Sandi</span>
                    </label>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800 space-y-1">
                    <div class="font-bold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Tips Keamanan Kata Sandi:</span>
                    </div>
                    <p>Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol. Jangan gunakan kata sandi yang mudah ditebak seperti tanggal lahir atau nomor telepon.</p>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition shadow-sm">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security & Activity Logs -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Riwayat Masuk & Keamanan</h3>
                    <p class="text-[11px] text-slate-500">Catatan 5 aktivitas login terakhir pada akun Anda.</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 min-w-[500px]">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">Waktu</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Alamat IP</th>
                        <th class="py-2.5 px-3">Perangkat / Browser</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($loginHistory as $item)
                        <tr>
                            <td class="py-2.5 px-3 text-slate-900">{{ $item->created_at?->isoFormat('D MMM Y, HH:mm') ?? '-' }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->status === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $item->status === 'success' ? 'Berhasil Masuk' : 'Gagal' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-[11px] text-slate-600">{{ $item->ip_address ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-[11px] text-slate-500 truncate max-w-xs" title="{{ $item->user_agent }}">
                                {{ Str::limit($item->user_agent ?? 'Unknown Browser', 45) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-400 italic">Belum ada riwayat login tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
