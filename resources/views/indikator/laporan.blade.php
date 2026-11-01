<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Resmi {{ strtoupper($tipe) }} Tahun {{ $tahun }} - Dindikpora Banjarnegara</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-banjarnegara.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 9.5pt; color: black !important; padding: 0 !important; }
            .page-break { page-break-before: always; }
            .avoid-break { page-break-inside: avoid; }
            .print-shadow-none { box-shadow: none !important; }
            .print-border { border-color: #cbd5e1 !important; }
            @page {
                size: A4 landscape;
                margin: 10mm 10mm 10mm 10mm;
            }
        }
    </style>
</head>
<body class="antialiased py-6 sm:py-8 px-2 sm:px-4">

    <!-- TOP ACTION BAR (TIDAK DICETAK KE PDF) -->
    <div class="max-w-[1300px] mx-auto mb-6 no-print">
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('indikator.'.$tipe, ['tahun' => $tahun]) }}" 
                   class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" 
                   title="Kembali ke Menu {{ strtoupper($tipe) }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>Laporan Visual Resmi {{ strtoupper($officialData['labels']['title']) }} (Grafik & Tabel)</span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-blue-100 text-blue-800 uppercase">A4 Landscape</span>
                    </h1>
                    <p class="text-[11px] text-slate-500">Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Banjarnegara (Tahun {{ $tahun }})</p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <form method="GET" class="flex items-center gap-2 text-xs">
                    <select name="tahun" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                        @foreach([2024, 2025, 2026, 2027, 2028, 2029] as $t)
                            <option value="{{ $t }}" {{ $tahun === $t ? 'selected' : '' }}>T.A {{ $t }}</option>
                        @endforeach
                    </select>
                </form>

                <a href="{{ route('export.'.$tipe, ['tahun' => $tahun]) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition">
                    <svg class="w-4 h-4 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Unduh Excel (.xlsx)</span>
                </a>

                <button type="button" 
                        onclick="window.print()" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm hover:shadow transition cursor-pointer">
                    <svg class="w-4 h-4 text-blue-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak / Simpan PDF (A4)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- LEMBAR DOKUMEN LAPORAN RESMI (A4 LANDSCAPE SIAP CETAK) -->
    <div class="max-w-[1300px] mx-auto bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 print-shadow-none print:border-none shadow-sm space-y-6">
        
        <!-- KOP SURAT DINAS RESMI -->
        <div class="border-b-2 border-slate-900 pb-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo-banjarnegara.png') }}" alt="Logo Banjarnegara" class="w-16 h-20 object-contain shrink-0">
                <div class="text-slate-900 leading-tight">
                    <p class="text-xs font-bold tracking-widest uppercase text-slate-600">Pemerintah Kabupaten Banjarnegara</p>
                    <h2 class="text-base sm:text-lg font-black tracking-wide uppercase text-slate-950">Dinas Pendidikan, Kepemudaan dan Olahraga</h2>
                    <p class="text-[11px] text-slate-600 font-medium">Jalan Mayjend Soetojo No. 51 Banjarnegara 53412 &bull; Telp. (0286) 591244</p>
                    <p class="text-[10px] text-slate-500 font-mono">Laman: dindikpora.banjarnegarakab.go.id &bull; Pos-el: dindikpora@banjarnegarakab.go.id</p>
                </div>
            </div>
            <div class="text-right hidden sm:block text-[11px] text-slate-500 font-mono">
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="block font-bold text-slate-800">DOKUMEN RESMI SAKIP</span>
                    <span>Tahun Anggaran {{ $tahun }}</span>
                </div>
            </div>
        </div>

        <!-- JUDUL LAPORAN RESMI -->
        <div class="text-center space-y-1 pt-1">
            <span class="inline-block px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-900">
                Laporan Kinerja Urusan Pendidikan
            </span>
            <h1 class="text-base sm:text-lg font-black text-slate-900 uppercase tracking-tight">
                {{ $officialData['labels']['full_title'] }} ({{ strtoupper($tipe) }})
            </h1>
            <p class="text-xs text-slate-600 font-medium">
                Capaian Indikator Kinerja Makro Berdasarkan Jenjang Pendidikan dan Sebaran Wilayah Kabupaten Banjarnegara
            </p>
        </div>

        <!-- 4 KPI SUMMARY CARDS (PAUD, SD, SMP, SMA/SMK) -->
        @php
            $targetPaud = (float) ($kabupaten['target']?->{"target_{$tipe}_paud"} ?? 78.5);
            $targetSd = (float) ($kabupaten['target']?->{"target_{$tipe}_sd"} ?? 101.8);
            $targetSmp = (float) ($kabupaten['target']?->{"target_{$tipe}_smp"} ?? 97.5);
            $targetSma = (float) ($kabupaten['target']?->{"target_{$tipe}_sma"} ?? 84.2);

            $capaianPaud = (float) ($kabupaten[$tipe]['paud'] ?? 0);
            $capaianSd = (float) ($kabupaten[$tipe]['sd'] ?? 0);
            $capaianSmp = (float) ($kabupaten[$tipe]['smp'] ?? 0);
            $capaianSma = (float) ($kabupaten[$tipe]['sma'] ?? 0);
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-200 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 block">PAUD / TK (5–6 Th)</span>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-black text-blue-950 font-mono">{{ number_format($capaianPaud, 2) }}%</span>
                    <span class="text-[10px] font-semibold text-slate-500">Target: {{ number_format($targetPaud, 1) }}%</span>
                </div>
                <div class="pt-1.5 border-t border-blue-200/60 text-[10px] flex justify-between text-slate-600">
                    <span>Status Renstra:</span>
                    <strong class="{{ $capaianPaud >= $targetPaud ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $capaianPaud >= $targetPaud ? 'Tercapai' : 'Di Bawah Target' }}
                    </strong>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-200 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 block">SD / Sederajat (7–12 Th)</span>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-black text-indigo-950 font-mono">{{ number_format($capaianSd, 2) }}%</span>
                    <span class="text-[10px] font-semibold text-slate-500">Target: {{ number_format($targetSd, 1) }}%</span>
                </div>
                <div class="pt-1.5 border-t border-indigo-200/60 text-[10px] flex justify-between text-slate-600">
                    <span>Status Renstra:</span>
                    <strong class="{{ $capaianSd >= $targetSd ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $capaianSd >= $targetSd ? 'Tercapai' : 'Di Bawah Target' }}
                    </strong>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-sky-50/70 border border-sky-200 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-sky-700 block">SMP / Sederajat (13–15 Th)</span>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-black text-sky-950 font-mono">{{ number_format($capaianSmp, 2) }}%</span>
                    <span class="text-[10px] font-semibold text-slate-500">Target: {{ number_format($targetSmp, 1) }}%</span>
                </div>
                <div class="pt-1.5 border-t border-sky-200/60 text-[10px] flex justify-between text-slate-600">
                    <span>Status Renstra:</span>
                    <strong class="{{ $capaianSmp >= $targetSmp ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $capaianSmp >= $targetSmp ? 'Tercapai' : 'Di Bawah Target' }}
                    </strong>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">SMA / SMK (16–18 Th)</span>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-black text-emerald-950 font-mono">{{ number_format($capaianSma, 2) }}%</span>
                    <span class="text-[10px] font-semibold text-slate-500">Target: {{ number_format($targetSma, 1) }}%</span>
                </div>
                <div class="pt-1.5 border-t border-emerald-200/60 text-[10px] flex justify-between text-slate-600">
                    <span>Status Renstra:</span>
                    <strong class="{{ $capaianSma >= $targetSma ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $capaianSma >= $targetSma ? 'Tercapai' : 'Di Bawah Target' }}
                    </strong>
                </div>
            </div>
        </div>

        <!-- BAGIAN 1: GRAFIK VISUALISASI CAPAIAN (CHARTS) -->
        <div class="space-y-4 avoid-break">
            <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                <span class="w-6 h-6 rounded-lg bg-blue-600 text-white font-extrabold flex items-center justify-center text-xs">1</span>
                <h3 class="text-sm font-bold text-slate-900 uppercase">Visualisasi Grafik Capaian Indikator {{ strtoupper($tipe) }}</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Grafik 1: Perbandingan Capaian vs Target Renstra -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <h4 class="text-xs font-bold text-slate-800 text-center">Perbandingan Capaian Riil vs Target Renstra per Jenjang (%)</h4>
                    <div class="relative h-56 w-full">
                        <canvas id="chartJenjangTarget"></canvas>
                    </div>
                </div>

                <!-- Grafik 2: Komparasi Gender Laki-laki vs Perempuan -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <h4 class="text-xs font-bold text-slate-800 text-center">Komparasi Capaian Gender (Laki-laki ♂ vs Perempuan ♀) (%)</h4>
                    <div class="relative h-56 w-full">
                        <canvas id="chartGender"></canvas>
                    </div>
                </div>
            </div>

            <!-- Grafik 3: Capaian Sebaran 20 Kecamatan -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <h4 class="text-xs font-bold text-slate-800 text-center">Sebaran Capaian {{ strtoupper($tipe) }} SD & SMP pada 20 Kecamatan se-Kabupaten Banjarnegara (%)</h4>
                <div class="relative h-60 w-full">
                    <canvas id="chartKecamatan"></canvas>
                </div>
            </div>
        </div>

        <!-- PAGE BREAK UNTUK CETAK DOKUMEN RESMI KE TABEL -->
        <div class="page-break"></div>

        <!-- BAGIAN 2: TABEL DATA RESMI FORMAT XLSX (4 BLOK JENJANG) -->
        <div class="space-y-4 pt-4">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-extrabold flex items-center justify-center text-xs">2</span>
                    <h3 class="text-sm font-bold text-slate-900 uppercase">Tabel Rekapitulasi Resmi Data Penduduk, Siswa, dan Capaian {{ strtoupper($tipe) }}</h3>
                </div>
                <span class="text-[10px] font-mono text-slate-400">Standar Pelaporan Resmi Dokumen {{ strtoupper($tipe) }}.xlsx</span>
            </div>

            <!-- TABEL RESMI BERDESAIN IDENTIK DENGAN BERKAS XLSX -->
            <div class="w-full overflow-x-auto rounded-xl border border-slate-300">
                <table class="w-full text-left text-[9px] border-collapse">
                    <thead>
                        {{-- Baris 1: Banner Header Tiap Jenjang --}}
                        <tr class="text-[10px] font-extrabold text-center tracking-wide border-b border-slate-300">
                            <th colspan="11" class="py-2 px-1 bg-blue-100 text-blue-900 border-r border-slate-300">
                                {{ $officialData['labels']['paud_title'] }}
                            </th>
                            <th class="w-2 bg-slate-200 p-0 border-r border-slate-300"></th>
                            <th colspan="11" class="py-2 px-1 bg-indigo-100 text-indigo-900 border-r border-slate-300">
                                {{ $officialData['labels']['sd_title'] }}
                            </th>
                            <th class="w-2 bg-slate-200 p-0 border-r border-slate-300"></th>
                            <th colspan="11" class="py-2 px-1 bg-sky-100 text-sky-900 border-r border-slate-300">
                                {{ $officialData['labels']['smp_title'] }}
                            </th>
                            <th class="w-2 bg-slate-200 p-0 border-r border-slate-300"></th>
                            <th colspan="11" class="py-2 px-1 bg-emerald-100 text-emerald-900">
                                {{ $officialData['labels']['sma_title'] }}
                            </th>
                        </tr>

                        {{-- Baris 2: Header Kolom Utama --}}
                        <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[9px] border-b border-slate-300">
                            {{-- PAUD --}}
                            <th rowspan="2" class="py-2 px-1 text-center border-r border-slate-300 w-6">No</th>
                            <th rowspan="2" class="py-2 px-1.5 border-r border-slate-300 min-w-[90px]">Kecamatan</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300">{{ $officialData['labels']['paud_penduduk'] }}</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300">{{ $officialData['labels']['paud_siswa'] }}</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300 bg-blue-50 text-blue-900">{{ $officialData['labels']['paud_capaian'] }}</th>

                            {{-- Spacer --}}
                            <th rowspan="2" class="w-2 bg-slate-200 p-0 border-r border-slate-300"></th>

                            {{-- SD --}}
                            <th rowspan="2" class="py-2 px-1 text-center border-r border-slate-300 w-6">No</th>
                            <th rowspan="2" class="py-2 px-1.5 border-r border-slate-300 min-w-[90px]">Kecamatan</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300">{{ $officialData['labels']['sd_penduduk'] }}</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300">{{ $officialData['labels']['sd_siswa'] }}</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300 bg-indigo-50 text-indigo-900">{{ $officialData['labels']['sd_capaian'] }}</th>

                            {{-- Spacer --}}
                            <th rowspan="2" class="w-2 bg-slate-200 p-0 border-r border-slate-300"></th>

                            {{-- SMP --}}
                            <th rowspan="2" class="py-2 px-1 text-center border-r border-slate-300 w-6">No</th>
                            <th rowspan="2" class="py-2 px-1.5 border-r border-slate-300 min-w-[90px]">Kecamatan</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300">{{ $officialData['labels']['smp_penduduk'] }}</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300">{{ $officialData['labels']['smp_siswa'] }}</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300 bg-sky-50 text-sky-900">{{ $officialData['labels']['smp_capaian'] }}</th>

                            {{-- Spacer --}}
                            <th rowspan="2" class="w-2 bg-slate-200 p-0 border-r border-slate-300"></th>

                            {{-- SMA --}}
                            <th rowspan="2" class="py-2 px-1 text-center border-r border-slate-300 w-6">No</th>
                            <th rowspan="2" class="py-2 px-1.5 border-r border-slate-300 min-w-[90px]">Kecamatan</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300">{{ $officialData['labels']['sma_penduduk'] }}</th>
                            <th colspan="3" class="py-1 px-1 text-center border-r border-slate-300">{{ $officialData['labels']['sma_siswa'] }}</th>
                            <th colspan="3" class="py-1 px-1 text-center bg-emerald-50 text-emerald-900">{{ $officialData['labels']['sma_capaian'] }}</th>
                        </tr>

                        {{-- Baris 3: Subheader L, P, Jml --}}
                        <tr class="bg-slate-100/90 text-slate-600 font-bold text-[8.5px] text-center border-b border-slate-300">
                            {{-- PAUD --}}
                            <th class="py-1 px-0.5 border-r border-slate-300">L</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">P</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">Jml</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">L</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">P</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">Jml</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-blue-50">L%</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-blue-50">P%</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-blue-50">Jml%</th>

                            {{-- SD --}}
                            <th class="py-1 px-0.5 border-r border-slate-300">L</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">P</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">Jml</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">L</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">P</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">Jml</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-indigo-50">L%</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-indigo-50">P%</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-indigo-50">Jml%</th>

                            {{-- SMP --}}
                            <th class="py-1 px-0.5 border-r border-slate-300">L</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">P</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">Jml</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">L</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">P</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">Jml</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-sky-50">L%</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-sky-50">P%</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-sky-50">Jml%</th>

                            {{-- SMA --}}
                            <th class="py-1 px-0.5 border-r border-slate-300">L</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">P</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">Jml</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">L</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">P</th>
                            <th class="py-1 px-0.5 border-r border-slate-300">Jml</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-emerald-50">L%</th>
                            <th class="py-1 px-0.5 border-r border-slate-300 bg-emerald-50">P%</th>
                            <th class="py-1 px-0.5 bg-emerald-50">Jml%</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @foreach($officialData['rows'] as $r)
                        <tr class="hover:bg-slate-50">
                            {{-- PAUD --}}
                            <td class="py-1 px-1 text-center text-slate-400 font-mono border-r border-slate-200">{{ $r['no'] }}</td>
                            <td class="py-1 px-1.5 font-semibold text-slate-900 border-r border-slate-200 whitespace-nowrap">{{ $r['nama'] }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['paud']['penduduk']['l']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['paud']['penduduk']['p']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-semibold">{{ number_format($r['paud']['penduduk']['jml']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['paud']['siswa']['l']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['paud']['siswa']['p']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-semibold">{{ number_format($r['paud']['siswa']['jml']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono text-blue-700 bg-blue-50/20">{{ number_format($r['paud']['capaian']['l'], 1) }}%</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono text-pink-700 bg-blue-50/20">{{ number_format($r['paud']['capaian']['p'], 1) }}%</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-bold text-blue-900 bg-blue-50/30">{{ number_format($r['paud']['capaian']['jml'], 1) }}%</td>

                            {{-- Spacer --}}
                            <td class="w-2 bg-slate-200 p-0 border-r border-slate-300"></td>

                            {{-- SD --}}
                            <td class="py-1 px-1 text-center text-slate-400 font-mono border-r border-slate-200">{{ $r['no'] }}</td>
                            <td class="py-1 px-1.5 font-semibold text-slate-900 border-r border-slate-200 whitespace-nowrap">{{ $r['nama'] }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['sd']['penduduk']['l']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['sd']['penduduk']['p']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-semibold">{{ number_format($r['sd']['penduduk']['jml']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['sd']['siswa']['l']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['sd']['siswa']['p']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-semibold">{{ number_format($r['sd']['siswa']['jml']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono text-indigo-700 bg-indigo-50/20">{{ number_format($r['sd']['capaian']['l'], 1) }}%</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono text-pink-700 bg-indigo-50/20">{{ number_format($r['sd']['capaian']['p'], 1) }}%</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-bold text-indigo-900 bg-indigo-50/30">{{ number_format($r['sd']['capaian']['jml'], 1) }}%</td>

                            {{-- Spacer --}}
                            <td class="w-2 bg-slate-200 p-0 border-r border-slate-300"></td>

                            {{-- SMP --}}
                            <td class="py-1 px-1 text-center text-slate-400 font-mono border-r border-slate-200">{{ $r['no'] }}</td>
                            <td class="py-1 px-1.5 font-semibold text-slate-900 border-r border-slate-200 whitespace-nowrap">{{ $r['nama'] }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['smp']['penduduk']['l']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['smp']['penduduk']['p']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-semibold">{{ number_format($r['smp']['penduduk']['jml']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['smp']['siswa']['l']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['smp']['siswa']['p']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-semibold">{{ number_format($r['smp']['siswa']['jml']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono text-sky-700 bg-sky-50/20">{{ number_format($r['smp']['capaian']['l'], 1) }}%</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono text-pink-700 bg-sky-50/20">{{ number_format($r['smp']['capaian']['p'], 1) }}%</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-bold text-sky-900 bg-sky-50/30">{{ number_format($r['smp']['capaian']['jml'], 1) }}%</td>

                            {{-- Spacer --}}
                            <td class="w-2 bg-slate-200 p-0 border-r border-slate-300"></td>

                            {{-- SMA --}}
                            <td class="py-1 px-1 text-center text-slate-400 font-mono border-r border-slate-200">{{ $r['no'] }}</td>
                            <td class="py-1 px-1.5 font-semibold text-slate-900 border-r border-slate-200 whitespace-nowrap">{{ $r['nama'] }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['sma']['penduduk']['l']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['sma']['penduduk']['p']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-semibold">{{ number_format($r['sma']['penduduk']['jml']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['sma']['siswa']['l']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono">{{ number_format($r['sma']['siswa']['p']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono font-semibold">{{ number_format($r['sma']['siswa']['jml']) }}</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono text-emerald-700 bg-emerald-50/20">{{ number_format($r['sma']['capaian']['l'], 1) }}%</td>
                            <td class="py-1 px-1 text-right border-r border-slate-200 font-mono text-pink-700 bg-emerald-50/20">{{ number_format($r['sma']['capaian']['p'], 1) }}%</td>
                            <td class="py-1 px-1 text-right font-mono font-bold text-emerald-900 bg-emerald-50/30">{{ number_format($r['sma']['capaian']['jml'], 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        @php $tot = $officialData['total']; @endphp
                        <tr class="bg-slate-200 text-slate-900 font-bold border-t-2 border-slate-400 text-[9px]">
                            {{-- PAUD --}}
                            <td class="py-1.5 px-1 border-r border-slate-300"></td>
                            <td class="py-1.5 px-1.5 font-extrabold border-r border-slate-300">Jumlah</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['penduduk']['l']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['penduduk']['p']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['penduduk']['jml']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['siswa']['l']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['siswa']['p']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['siswa']['jml']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold text-blue-900 bg-blue-100/60">{{ number_format($tot['paud']['capaian']['l'], 2) }}%</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold text-pink-900 bg-blue-100/60">{{ number_format($tot['paud']['capaian']['p'], 2) }}%</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-black text-blue-950 bg-blue-200/80">{{ number_format($tot['paud']['capaian']['jml'], 2) }}%</td>

                            {{-- Spacer --}}
                            <td class="w-2 bg-slate-300 p-0 border-r border-slate-300"></td>

                            {{-- SD --}}
                            <td class="py-1.5 px-1 border-r border-slate-300"></td>
                            <td class="py-1.5 px-1.5 font-extrabold border-r border-slate-300">Jumlah</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['penduduk']['l']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['penduduk']['p']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['penduduk']['jml']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['siswa']['l']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['siswa']['p']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['siswa']['jml']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold text-indigo-900 bg-indigo-100/60">{{ number_format($tot['sd']['capaian']['l'], 2) }}%</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold text-pink-900 bg-indigo-100/60">{{ number_format($tot['sd']['capaian']['p'], 2) }}%</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-black text-indigo-950 bg-indigo-200/80">{{ number_format($tot['sd']['capaian']['jml'], 2) }}%</td>

                            {{-- Spacer --}}
                            <td class="w-2 bg-slate-300 p-0 border-r border-slate-300"></td>

                            {{-- SMP --}}
                            <td class="py-1.5 px-1 border-r border-slate-300"></td>
                            <td class="py-1.5 px-1.5 font-extrabold border-r border-slate-300">Jumlah</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['penduduk']['l']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['penduduk']['p']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['penduduk']['jml']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['siswa']['l']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['siswa']['p']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['siswa']['jml']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold text-sky-900 bg-sky-100/60">{{ number_format($tot['smp']['capaian']['l'], 2) }}%</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold text-pink-900 bg-sky-100/60">{{ number_format($tot['smp']['capaian']['p'], 2) }}%</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-black text-sky-950 bg-sky-200/80">{{ number_format($tot['smp']['capaian']['jml'], 2) }}%</td>

                            {{-- Spacer --}}
                            <td class="w-2 bg-slate-300 p-0 border-r border-slate-300"></td>

                            {{-- SMA --}}
                            <td class="py-1.5 px-1 border-r border-slate-300"></td>
                            <td class="py-1.5 px-1.5 font-extrabold border-r border-slate-300">Jumlah</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['penduduk']['l']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['penduduk']['p']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['penduduk']['jml']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['siswa']['l']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['siswa']['p']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['siswa']['jml']) }}</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold text-emerald-900 bg-emerald-100/60">{{ number_format($tot['sma']['capaian']['l'], 2) }}%</td>
                            <td class="py-1.5 px-1 text-right border-r border-slate-300 font-mono font-extrabold text-pink-900 bg-emerald-100/60">{{ number_format($tot['sma']['capaian']['p'], 2) }}%</td>
                            <td class="py-1.5 px-1 text-right font-mono font-black text-emerald-950 bg-emerald-200/80">{{ number_format($tot['sma']['capaian']['jml'], 2) }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- CATATAN SUMBER DATA & LEMBAR PENGESAHAN KEPALA DINAS -->
            <div class="pt-4 flex flex-col sm:flex-row items-start justify-between gap-6 avoid-break">
                <div class="space-y-1.5 text-[11px] text-slate-600 max-w-lg">
                    <p class="font-bold text-slate-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Sumber & Metodologi Data:</span>
                    </p>
                    <p class="italic text-slate-500 leading-relaxed">
                        {{ $officialData['sumber'] }}
                    </p>
                    <p class="text-[10px] text-slate-400">
                        Dicetak secara otomatis melalui Sistem Informasi Manajemen Akuntabilitas Kinerja (SIMAK Pendidikan) &bull; {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB
                    </p>
                </div>

                <div class="text-center sm:text-right space-y-1 text-xs text-slate-800 shrink-0 w-full sm:w-auto">
                    <p>Banjarnegara, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p class="font-bold text-slate-900">Kepala Dinas Pendidikan, Kepemudaan dan Olahraga<br>Kabupaten Banjarnegara</p>
                    <div class="h-16"></div>
                    <p class="font-bold text-slate-950 text-sm underline">{{ $pimpinan->name ?? 'Dr. H. Teguh Handoko, S.Sos., M.Si.' }}</p>
                    <p class="text-[11px] font-mono text-slate-600">NIP. {{ $pimpinan->nip ?? '196807151992031005' }}</p>
                </div>
            </div>
        </div>

    </div>

    <!-- INISIALISASI GRAFIK CHART.JS (RAMAH PRINT TANPA ANIMASI) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Chart Jenjang vs Target Renstra
            const ctxJenjang = document.getElementById('chartJenjangTarget')?.getContext('2d');
            if (ctxJenjang) {
                new Chart(ctxJenjang, {
                    type: 'bar',
                    data: {
                        labels: ['PAUD (5-6 Th)', 'SD (7-12 Th)', 'SMP (13-15 Th)', 'SMA/SMK (16-18 Th)'],
                        datasets: [
                            {
                                label: 'Capaian Riil (%)',
                                data: [{{ $capaianPaud }}, {{ $capaianSd }}, {{ $capaianSmp }}, {{ $capaianSma }}],
                                backgroundColor: 'rgba(37, 99, 235, 0.85)',
                                borderColor: '#1d4ed8',
                                borderWidth: 1,
                                borderRadius: 6
                            },
                            {
                                label: 'Target Renstra (%)',
                                data: [{{ $targetPaud }}, {{ $targetSd }}, {{ $targetSmp }}, {{ $targetSma }}],
                                backgroundColor: 'rgba(245, 158, 11, 0.85)',
                                borderColor: '#d97706',
                                borderWidth: 1,
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10 } } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 120,
                                ticks: { font: { size: 9 }, callback: v => v + '%' }
                            },
                            x: {
                                ticks: { font: { size: 10, weight: 'bold' } }
                            }
                        }
                    }
                });
            }

            // 2. Chart Komparasi Gender (L vs P)
            @php
                $genPaudL = (float) ($kabupaten['gender_comparison']["{$tipe}_paud_l"] ?? 0);
                $genPaudP = (float) ($kabupaten['gender_comparison']["{$tipe}_paud_p"] ?? 0);
                $genSdL = (float) ($kabupaten['gender_comparison']["{$tipe}_sd_l"] ?? 0);
                $genSdP = (float) ($kabupaten['gender_comparison']["{$tipe}_sd_p"] ?? 0);
                $genSmpL = (float) ($kabupaten['gender_comparison']["{$tipe}_smp_l"] ?? 0);
                $genSmpP = (float) ($kabupaten['gender_comparison']["{$tipe}_smp_p"] ?? 0);
                $genSmaL = (float) ($kabupaten['gender_comparison']["{$tipe}_sma_l"] ?? 0);
                $genSmaP = (float) ($kabupaten['gender_comparison']["{$tipe}_sma_p"] ?? 0);
            @endphp
            const ctxGender = document.getElementById('chartGender')?.getContext('2d');
            if (ctxGender) {
                new Chart(ctxGender, {
                    type: 'bar',
                    data: {
                        labels: ['PAUD', 'SD/MI', 'SMP/MTs', 'SMA/SMK'],
                        datasets: [
                            {
                                label: 'Laki-laki ♂ (%)',
                                data: [{{ $genPaudL }}, {{ $genSdL }}, {{ $genSmpL }}, {{ $genSmaL }}],
                                backgroundColor: 'rgba(59, 130, 246, 0.85)',
                                borderColor: '#2563eb',
                                borderWidth: 1,
                                borderRadius: 6
                            },
                            {
                                label: 'Perempuan ♀ (%)',
                                data: [{{ $genPaudP }}, {{ $genSdP }}, {{ $genSmpP }}, {{ $genSmaP }}],
                                backgroundColor: 'rgba(236, 72, 153, 0.85)',
                                borderColor: '#db2777',
                                borderWidth: 1,
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10 } } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 120,
                                ticks: { font: { size: 9 }, callback: v => v + '%' }
                            },
                            x: {
                                ticks: { font: { size: 10, weight: 'bold' } }
                            }
                        }
                    }
                });
            }

            // 3. Chart Sebaran 20 Kecamatan (SD & SMP)
            @php
                $labelsKec = [];
                $sdKec = [];
                $smpKec = [];
                foreach ($officialData['rows'] as $r) {
                    $labelsKec[] = $r['nama'];
                    $sdKec[] = round($r['sd']['capaian']['jml'], 2);
                    $smpKec[] = round($r['smp']['capaian']['jml'], 2);
                }
            @endphp
            const ctxKec = document.getElementById('chartKecamatan')?.getContext('2d');
            if (ctxKec) {
                new Chart(ctxKec, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($labelsKec) !!},
                        datasets: [
                            {
                                label: 'SD / Sederajat (%)',
                                data: {!! json_encode($sdKec) !!},
                                backgroundColor: 'rgba(99, 102, 241, 0.85)',
                                borderColor: '#4f46e5',
                                borderWidth: 1,
                                borderRadius: 4
                            },
                            {
                                label: 'SMP / Sederajat (%)',
                                data: {!! json_encode($smpKec) !!},
                                backgroundColor: 'rgba(14, 165, 233, 0.85)',
                                borderColor: '#0284c7',
                                borderWidth: 1,
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10 } } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { font: { size: 9 }, callback: v => v + '%' }
                            },
                            x: {
                                ticks: {
                                    font: { size: 9 },
                                    autoSkip: false,
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>

