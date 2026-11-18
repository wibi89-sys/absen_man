@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Pengaturan Madrasah & Profil</h2>
            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi identitas lembaga, logo resmi, kepala madrasah, dan preferensi tampilan.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs transition">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Dashboard
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-6 text-xs sm:text-sm font-semibold">
        <a href="{{ route('settings.index') }}" class="pb-3 text-emerald-700 border-b-2 border-emerald-600 flex items-center gap-1.5">
            Identitas Lembaga & Profil
        </a>
        @if(auth()->user()->hasAnyRole(['super_admin', 'admin']))
        <a href="{{ route('settings.database.index') }}" class="pb-3 text-slate-500 hover:text-slate-800 border-b-2 border-transparent transition flex items-center gap-1.5">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
            <span>Database & Cadangan Data</span>
        </a>
        @endif
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5"
          x-data="{
              logoPreview: '{{ $setting->logo_url }}',
              deleteLogo: false,
              handleLogoChange(e) {
                  const file = e.target.files[0];
                  if (file) {
                      this.deleteLogo = false;
                      this.logoPreview = URL.createObjectURL(file);
                  }
              }
          }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- Left Column: Branding & Logo -->
            <div class="space-y-5 lg:col-span-1">
                <!-- Logo & Visual Identity Card -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-4">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Logo & Identitas Visual
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Logo akan ditampilkan di login, navbar, dan kop cetak laporan.</p>
                    </div>

                    <!-- Logo Preview Box -->
                    <div class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-200 rounded-lg bg-slate-50/50">
                        <template x-if="logoPreview && !deleteLogo">
                            <div class="relative group">
                                <img :src="logoPreview" alt="Logo Madrasah" class="w-24 h-24 object-contain rounded-lg bg-white p-2 shadow-2xs border border-slate-200">
                                <button type="button" @click="deleteLogo = true; logoPreview = null; $refs.logoInput.value = ''"
                                        class="absolute -top-2 -right-2 bg-rose-600 hover:bg-rose-700 text-white rounded-full p-1 shadow-xs transition"
                                        title="Hapus Logo">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>

                        <template x-if="!logoPreview || deleteLogo">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-2xl shadow-xs mb-2">
                                    {{ $setting->initials }}
                                </div>
                                <span class="text-[11px] text-slate-500 font-medium">Inisial Otomatis Default</span>
                            </div>
                        </template>

                        <input type="hidden" name="delete_logo" :value="deleteLogo ? '1' : '0'">

                        <div class="mt-3.5 w-full">
                            <label class="block text-center cursor-pointer">
                                <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-medium shadow-2xs transition w-full">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Pilih File Logo Baru
                                </span>
                                <input type="file" name="logo" x-ref="logoInput" @change="handleLogoChange" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden">
                            </label>
                            <p class="text-[10px] text-slate-400 text-center mt-1.5">Format: PNG, JPG, WebP, SVG. Maks 2MB.</p>
                        </div>
                    </div>

                    <!-- Mode Pengujian / Akun Demo Setting -->
                    <div class="pt-3 border-t border-slate-100">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="enable_demo_mode" value="1" {{ old('enable_demo_mode', $setting->enable_demo_mode) ? 'checked' : '' }}
                                   class="mt-0.5 w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-semibold text-slate-800 block">Sediakan Mode Uji Coba di Login</span>
                                <span class="text-[11px] text-slate-500 leading-relaxed block mt-0.5">
                                    Jika aktif, drawer <em>"Akun Pengujian Demo"</em> tetap tersedia di halaman login untuk memudahkan uji coba peran.
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Teks Sambutan Kiosk -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-3">
                    <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Teks Sambutan Gerbang
                    </h3>
                    <div>
                        <label for="gate_welcome_text" class="block text-xs font-semibold text-slate-700 mb-1">Teks di Layar Presensi Kiosk</label>
                        <input type="text" id="gate_welcome_text" name="gate_welcome_text" value="{{ old('gate_welcome_text', $setting->gate_welcome_text) }}"
                               placeholder="Contoh: Selamat Datang di Madrasah..."
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                    </div>
                </div>
            </div>

            <!-- Right Column: Identitas, Kepala Sekolah & Kontak -->
            <div class="space-y-5 lg:col-span-2">
                
                <!-- Identitas Madrasah -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-3.5">
                    <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Identitas Lembaga Pendidikan
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="sm:col-span-2">
                            <label for="school_name" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Nama Madrasah / Sekolah <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="school_name" name="school_name" value="{{ old('school_name', $setting->school_name) }}" required
                                   placeholder="Contoh: MAN 1 KOTA BANDUNG"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 font-semibold text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="school_slogan" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Slogan / Sub-Judul Lembaga
                            </label>
                            <input type="text" id="school_slogan" name="school_slogan" value="{{ old('school_slogan', $setting->school_slogan) }}"
                                   placeholder="Contoh: Terdepan dalam Imtaq, Unggul dalam Iptek"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                        </div>

                        <div>
                            <label for="school_level" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Jenjang Pendidikan <span class="text-rose-500">*</span>
                            </label>
                            <select id="school_level" name="school_level" required
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none bg-white transition">
                                @foreach(['Madrasah Aliyah (MA)', 'Madrasah Tsanawiyah (MTs)', 'Madrasah Ibtidaiyah (MI)', 'SMA / SMK', 'SMP', 'SD', 'Pondok Pesantren'] as $lvl)
                                    <option value="{{ $lvl }}" {{ old('school_level', $setting->school_level) == $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="npsn" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                NPSN
                            </label>
                            <input type="text" id="npsn" name="npsn" value="{{ old('npsn', $setting->npsn) }}"
                                   placeholder="Nomor Pokok Sekolah Nasional"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="nsm" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                NSM (Nomor Statistik Madrasah)
                            </label>
                            <input type="text" id="nsm" name="nsm" value="{{ old('nsm', $setting->nsm) }}"
                                   placeholder="Nomor Statistik Madrasah (Kemenag)"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Kepala Madrasah -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-3.5">
                    <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Kepala Madrasah / Pimpinan Lembaga
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label for="principal_name" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="principal_name" name="principal_name" value="{{ old('principal_name', $setting->principal_name) }}" required
                                   placeholder="Contoh: Dr. H. Ahmad Fauzi, M.Pd."
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                        </div>

                        <div>
                            <label for="principal_nip" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                NIP Kepala Madrasah
                            </label>
                            <input type="text" id="principal_nip" name="principal_nip" value="{{ old('principal_nip', $setting->principal_nip) }}"
                                   placeholder="Contoh: 197508152002121003"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Kontak & Alamat -->
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4 sm:p-5 space-y-3.5">
                    <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Kontak & Alamat Lembaga
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="sm:col-span-2">
                            <label for="school_address" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Alamat Lengkap
                            </label>
                            <textarea id="school_address" name="school_address" rows="2"
                                      placeholder="Nama jalan, kelurahan, kecamatan, kabupaten/kota, kode pos..."
                                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">{{ old('school_address', $setting->school_address) }}</textarea>
                        </div>

                        <div>
                            <label for="school_phone" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Telepon / WhatsApp
                            </label>
                            <input type="text" id="school_phone" name="school_phone" value="{{ old('school_phone', $setting->school_phone) }}"
                                   placeholder="Contoh: 08123456789"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                        </div>

                        <div>
                            <label for="school_email" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Email Resmi
                            </label>
                            <input type="email" id="school_email" name="school_email" value="{{ old('school_email', $setting->school_email) }}"
                                   placeholder="Contoh: info@madrasah.sch.id"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="school_website" class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Website Madrasah
                            </label>
                            <input type="url" id="school_website" name="school_website" value="{{ old('school_website', $setting->school_website) }}"
                                   placeholder="https://absen.laksanatech.com"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="flex items-center justify-end gap-3 pt-1">
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs sm:text-sm shadow-xs transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Pengaturan
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
