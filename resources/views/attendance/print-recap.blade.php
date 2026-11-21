<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Rekapitulasi Presensi - {{ $schoolSetting->school_name }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ $schoolSetting->favicon_url }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; color: #000; background: #fff; }
            .print-table th, .print-table td { border: 1px solid #000 !important; }
            @page {
                size: A4 portrait;
                margin: 1.5cm 1.2cm 1.5cm 1.2cm;
            }
        }
        .print-table th, .print-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen font-serif antialiased p-4 sm:p-8">

    <!-- Top Floating Toolbar (Hidden on Print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-lg border border-slate-200 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('attendance.recap', ['start_date' => $startDate, 'end_date' => $endDate, 'class_id' => $classId]) }}" 
               class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Kembali
            </a>
            <span class="text-xs text-slate-500">Pratinjau Cetak Lembar Rekapitulasi Resmi</span>
        </div>
        <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Official Document Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-none sm:rounded-2xl shadow-sm sm:shadow-md border border-slate-200 print:border-none print:shadow-none print:p-0">
        
        <!-- Kop Surat Resmi Madrasah -->
        <div class="border-b-4 border-double border-slate-900 pb-4 mb-6">
            <div class="flex items-center justify-center gap-6">
                @if($schoolSetting->logo_url)
                    <img src="{{ $schoolSetting->logo_url }}" alt="Logo" class="w-20 h-20 object-contain shrink-0">
                @else
                    <div class="w-20 h-20 rounded-xl bg-slate-100 border border-slate-300 flex items-center justify-center font-bold text-xl text-slate-700 shrink-0">
                        {{ $schoolSetting->initials }}
                    </div>
                @endif
                <div class="text-center flex-1">
                    <div class="text-xs uppercase tracking-widest font-semibold text-slate-700">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
                    <h1 class="text-xl sm:text-2xl font-black uppercase text-slate-900 tracking-tight">{{ $schoolSetting->school_name }}</h1>
                    @if($schoolSetting->npsn || $schoolSetting->nsm)
                        <div class="text-xs font-semibold text-slate-600 mt-0.5">
                            @if($schoolSetting->npsn) <span>NPSN: {{ $schoolSetting->npsn }}</span> @endif
                            @if($schoolSetting->npsn && $schoolSetting->nsm) <span> | </span> @endif
                            @if($schoolSetting->nsm) <span>NSM: {{ $schoolSetting->nsm }}</span> @endif
                        </div>
                    @endif
                    <p class="text-[11px] text-slate-600 mt-1">
                        {{ $schoolSetting->school_address ?? 'Alamat Madrasah Belum Diatur' }}
                        @if($schoolSetting->school_phone) &bull; Telp: {{ $schoolSetting->school_phone }} @endif
                        @if($schoolSetting->school_email) &bull; Email: {{ $schoolSetting->school_email }} @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Document Heading -->
        <div class="text-center my-6">
            <h2 class="text-base sm:text-lg font-bold uppercase tracking-wider underline text-slate-900">
                LAPORAN REKAPITULASI PRESENSI KEHADIRAN SISWA
            </h2>
            <div class="text-xs text-slate-700 mt-1 space-y-0.5">
                <div>Periode: <strong>{{ Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</strong></div>
                @if($selectedClass)
                    <div>Rombongan Belajar: <strong>{{ $selectedClass->name }}</strong> (Tingkat: {{ $selectedClass->grade?->name ?? '-' }})</div>
                    <div>Wali Kelas: <strong>{{ $selectedClass->homeroomTeacher?->full_name ?? '-' }}</strong></div>
                @else
                    <div>Lingkup: <strong>Seluruh Rombongan Belajar / Kelas Aktif</strong></div>
                @endif
            </div>
        </div>

        <!-- Content Table -->
        @if($selectedClass)
            <!-- Tabel Rincian Siswa Per Rombel -->
            <table class="w-full text-left text-xs print-table border-collapse mb-8">
                <thead class="bg-slate-100 font-bold uppercase text-[10px] text-slate-800">
                    <tr>
                        <th class="py-2 text-center w-8">No</th>
                        <th class="py-2 text-center w-24">NIS</th>
                        <th class="py-2">Nama Siswa</th>
                        <th class="py-2 text-center w-12">L/P</th>
                        <th class="py-2 text-center w-12">Hadir</th>
                        <th class="py-2 text-center w-12">Tlt</th>
                        <th class="py-2 text-center w-12">Sakit</th>
                        <th class="py-2 text-center w-12">Izin</th>
                        <th class="py-2 text-center w-12">Alpa</th>
                        <th class="py-2 text-center w-16">Total Sesi</th>
                        <th class="py-2 text-center w-14">% Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($studentRecaps as $idx => $st)
                        <tr>
                            <td class="text-center font-mono text-[11px]">{{ $idx + 1 }}</td>
                            <td class="text-center font-mono text-[11px]">{{ $st['student']->nis }}</td>
                            <td class="font-semibold">{{ $st['student']->full_name }}</td>
                            <td class="text-center">{{ $st['student']->gender ?? '-' }}</td>
                            <td class="text-center font-mono">{{ $st['present'] }}</td>
                            <td class="text-center font-mono">{{ $st['late'] }}</td>
                            <td class="text-center font-mono">{{ $st['sick'] }}</td>
                            <td class="text-center font-mono">{{ $st['excused'] }}</td>
                            <td class="text-center font-mono font-bold {{ $st['absent'] > 0 ? 'text-rose-700' : '' }}">{{ $st['absent'] }}</td>
                            <td class="text-center font-mono font-semibold">{{ $st['total'] }}</td>
                            <td class="text-center font-mono font-bold">{{ $st['rate'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 italic text-slate-500">Tidak ada data siswa pada rombel ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <!-- Tabel Rekapitulasi Rombongan Belajar -->
            <table class="w-full text-left text-xs print-table border-collapse mb-8">
                <thead class="bg-slate-100 font-bold uppercase text-[10px] text-slate-800">
                    <tr>
                        <th class="py-2 text-center w-8">No</th>
                        <th class="py-2">Rombel / Kelas</th>
                        <th class="py-2">Wali Kelas</th>
                        <th class="py-2 text-center w-16">Siswa</th>
                        <th class="py-2 text-center w-14">Hadir</th>
                        <th class="py-2 text-center w-14">Tlt</th>
                        <th class="py-2 text-center w-14">Sakit</th>
                        <th class="py-2 text-center w-14">Izin</th>
                        <th class="py-2 text-center w-14">Alpa</th>
                        <th class="py-2 text-center w-16">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($classRecaps as $idx => $row)
                        <tr>
                            <td class="text-center font-mono text-[11px]">{{ $idx + 1 }}</td>
                            <td class="font-bold">{{ $row['class']->name }}</td>
                            <td>{{ $row['class']->homeroomTeacher?->full_name ?? '-' }}</td>
                            <td class="text-center font-mono">{{ $row['total_students'] }}</td>
                            <td class="text-center font-mono">{{ $row['present'] }}</td>
                            <td class="text-center font-mono">{{ $row['late'] }}</td>
                            <td class="text-center font-mono">{{ $row['sick'] }}</td>
                            <td class="text-center font-mono">{{ $row['excused'] }}</td>
                            <td class="text-center font-mono font-bold {{ $row['absent'] > 0 ? 'text-rose-700' : '' }}">{{ $row['absent'] }}</td>
                            <td class="text-center font-mono font-bold">{{ $row['rate'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 italic text-slate-500">Tidak ada data rekap presensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <!-- Signatures Section -->
        <div class="grid grid-cols-2 gap-8 text-xs text-slate-800 pt-6">
            <div class="text-center">
                <div>&nbsp;</div>
                <div class="font-semibold">{{ $selectedClass ? 'Wali Kelas,' : 'Koordinator Guru Piket,' }}</div>
                <div class="h-20"></div>
                <div class="font-bold underline">
                    {{ $selectedClass ? ($selectedClass->homeroomTeacher?->full_name ?? '( ...................................................... )') : '( ...................................................... )' }}
                </div>
                <div class="text-[11px] text-slate-600">
                    NIP: {{ $selectedClass?->homeroomTeacher?->nip ?? '............................................' }}
                </div>
            </div>

            <div class="text-center">
                <div>{{ now()->isoFormat('D MMMM Y') }}</div>
                <div class="font-semibold">Mengetahui,<br>Kepala Madrasah</div>
                <div class="h-16"></div>
                <div class="font-bold underline">{{ $schoolSetting->principal_name }}</div>
                <div class="text-[11px] text-slate-600">NIP: {{ $schoolSetting->principal_nip ?? '-' }}</div>
            </div>
        </div>

    </div>

</body>
</html>
