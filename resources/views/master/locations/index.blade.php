@extends('layouts.app')

@section('title', 'Lokasi Madrasah & Radius Geofence')

@section('content')
<div class="space-y-6" x-data="{
    lat: '',
    lon: '',
    detecting: false,
    showEditModal: false,
    detectingEdit: false,
    editForm: {
        id: null,
        name: '',
        location_type: 'madrasah',
        latitude: '',
        longitude: '',
        radius_meters: 100,
        capacity: '',
        is_active: true
    },

    detectLocation() {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung Geolocation.');
            return;
        }
        this.detecting = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.lat = pos.coords.latitude.toFixed(8);
                this.lon = pos.coords.longitude.toFixed(8);
                this.detecting = false;
            },
            (err) => {
                alert('Gagal mendeteksi lokasi: ' + err.message);
                this.detecting = false;
            },
            { enableHighAccuracy: true }
        );
    },

    openEditModal(item) {
        this.editForm = {
            id: item.id,
            name: item.name || '',
            location_type: item.location_type || 'madrasah',
            latitude: item.latitude !== null ? item.latitude : '',
            longitude: item.longitude !== null ? item.longitude : '',
            radius_meters: item.radius_meters || 100,
            capacity: item.capacity || '',
            is_active: Boolean(item.is_active)
        };
        this.showEditModal = true;
    },

    detectEditLocation() {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung Geolocation.');
            return;
        }
        this.detectingEdit = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.editForm.latitude = pos.coords.latitude.toFixed(8);
                this.editForm.longitude = pos.coords.longitude.toFixed(8);
                this.detectingEdit = false;
            },
            (err) => {
                alert('Gagal mendeteksi lokasi: ' + err.message);
                this.detectingEdit = false;
            },
            { enableHighAccuracy: true }
        );
    }
}">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Lokasi Madrasah, Ibadah & Radius Geofence</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola titik koordinat GPS dan batas radius toleransi presensi mandiri siswa & ibadah.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Tambah Lokasi Baru -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs h-fit space-y-4">
            <h3 class="font-bold text-slate-900 text-sm">Tambah Lokasi Baru</h3>
            <form action="{{ route('master.locations.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Lokasi / Gedung *</label>
                    <input type="text" name="name" placeholder="Contoh: Kampus Utama Madrasah" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jenis Lokasi *</label>
                    <select name="location_type" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="madrasah">Area Utama Madrasah</option>
                        <option value="masjid">Masjid Utama</option>
                        <option value="musholla">Musholla</option>
                        <option value="classroom">Ruang Kelas</option>
                        <option value="field">Lapangan Upacara / Olahraga</option>
                        <option value="counseling_room">Ruang Bimbingan Konseling (BK)</option>
                    </select>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-700 uppercase">Koordinat GPS Geofencing</span>
                        <button type="button" @click="detectLocation" class="text-[10px] text-emerald-700 hover:text-emerald-800 font-bold underline">
                            <span x-show="!detecting">Deteksi GPS Saya</span>
                            <span x-show="detecting" style="display:none;">Mendeteksi...</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] text-slate-500">Latitude</label>
                            <input type="text" name="latitude" x-model="lat" placeholder="-6.2088000"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-500">Longitude</label>
                            <input type="text" name="longitude" x-model="lon" placeholder="106.8456000"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] text-slate-500">Radius Toleransi Presensi (Meter)</label>
                        <input type="number" name="radius_meters" value="100" min="10" max="5000"
                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kapasitas Orang</label>
                    <input type="number" name="capacity" placeholder="Contoh: 500" min="1"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs text-slate-700 font-semibold">Lokasi Aktif Digunakan</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition mt-2">
                    Simpan Lokasi Baru
                </button>
            </form>
        </div>

        <!-- Daftar Lokasi Madrasah (Tabel Lengkap + Tombol Aksi CRUD) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col justify-between">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Nama Lokasi</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Koordinat GPS</th>
                            <th class="py-3 px-4">Radius</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($locations as $loc)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $loc->name }}
                                @if($loc->capacity)
                                    <span class="block text-[10px] text-slate-400 font-normal">Kapasitas: {{ $loc->capacity }} orang</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 uppercase text-[10px] font-semibold text-slate-500">
                                {{ str_replace('_', ' ', $loc->location_type) }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-600">
                                @if($loc->latitude && $loc->longitude)
                                    <a href="https://maps.google.com/?q={{ $loc->latitude }},{{ $loc->longitude }}" target="_blank" class="text-emerald-700 hover:underline inline-flex items-center gap-1">
                                        <span>{{ round($loc->latitude, 5) }}, {{ round($loc->longitude, 5) }}</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <span class="text-slate-400">Belum diatur</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-emerald-800">
                                {{ $loc->radius_meters ? $loc->radius_meters . ' m' : '100 m' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $loc->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center space-x-1 whitespace-nowrap">
                                <button type="button" @click="openEditModal({{ json_encode($loc) }})"
                                        class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-lg border border-emerald-200 transition">
                                    Edit
                                </button>
                                <form action="{{ route('master.locations.destroy', $loc) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi \'{{ addslashes($loc->name) }}\'?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold rounded-lg border border-rose-200 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Belum ada lokasi madrasah.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($locations->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $locations->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Edit Lokasi -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 p-6 space-y-4" @click.away="showEditModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm">Edit Lokasi Madrasah & Radius</h3>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form :action="'{{ url('master/locations') }}/' + editForm.id" method="POST" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Nama Lokasi / Gedung *</label>
                    <input type="text" name="name" x-model="editForm.name" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Jenis Lokasi *</label>
                    <select name="location_type" x-model="editForm.location_type" required
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="madrasah">Area Utama Madrasah</option>
                        <option value="masjid">Masjid Utama</option>
                        <option value="musholla">Musholla</option>
                        <option value="classroom">Ruang Kelas</option>
                        <option value="field">Lapangan Upacara / Olahraga</option>
                        <option value="counseling_room">Ruang Bimbingan Konseling (BK)</option>
                    </select>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-700 uppercase">Koordinat GPS Geofencing</span>
                        <button type="button" @click="detectEditLocation" class="text-[10px] text-emerald-700 hover:text-emerald-800 font-bold underline">
                            <span x-show="!detectingEdit">Deteksi GPS Saya Saat Ini</span>
                            <span x-show="detectingEdit" style="display:none;">Mendeteksi...</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] text-slate-500">Latitude</label>
                            <input type="text" name="latitude" x-model="editForm.latitude" placeholder="-6.2088000"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-500">Longitude</label>
                            <input type="text" name="longitude" x-model="editForm.longitude" placeholder="106.8456000"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] text-slate-500">Radius Toleransi Presensi (Meter)</label>
                        <input type="number" name="radius_meters" x-model="editForm.radius_meters" min="10" max="5000"
                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-mono">
                        <p class="text-[10px] text-slate-400 mt-1">Siswa di luar jarak radius ini akan ditolak otomatis saat presensi mandiri GPS.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kapasitas Orang</label>
                    <input type="number" name="capacity" x-model="editForm.capacity" min="1"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="editForm.is_active" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs text-slate-700 font-semibold">Lokasi Aktif Digunakan</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
