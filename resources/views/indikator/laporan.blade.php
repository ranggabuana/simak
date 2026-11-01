<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Resmi {{ strtoupper($tipe) }} Tahun {{ $tahun }} - DINDIKPORA Banjarnegara</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-banjarnegara.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Report Specific Layouts (Independent of Tailwind CDN/Compiler) */
        .report-sheet {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px 32px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .report-grid-4 {
            display: flex;
            gap: 12px;
            width: 100%;
        }
        .report-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px;
        }
        .report-grid-2 {
            display: flex;
            gap: 14px;
            width: 100%;
        }
        .report-chart-box {
            flex: 1;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: fixed;
        }
        .report-table th, .report-table td {
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            word-wrap: break-word;
        }
        .report-table thead tr {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
        }
        .report-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge-spm {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: 700;
        }

        /* Print Media Styles */
        @media print {
            .no-print { display: none !important; }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 7.5pt !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .report-sheet {
                max-width: 100% !important;
                width: 100% !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
                padding-top: 15px !important;
            }
            .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .report-table th, .report-table td {
                padding: 3px 4px !important;
                font-size: 7pt !important;
                border-color: #94a3b8 !important;
            }
            @page {
                size: A4 landscape;
                margin: 8mm 10mm 8mm 10mm;
            }
        }
    </style>
</head>
<body class="antialiased py-6 px-3 sm:px-6">

    <!-- TOP ACTION TOOLBAR (TIDAK DICETAK KE PDF) -->
    <div class="max-w-[1200px] mx-auto mb-5 no-print">
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
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
                        <span>Laporan Resmi {{ strtoupper($officialData['labels']['title']) }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-blue-800 uppercase tracking-wide">A4 Landscape</span>
                    </h1>
                    <p class="text-[11px] text-slate-500">Dinas Pendidikan, Kepemudaan dan Olahraga Kab. Banjarnegara &bull; T.A {{ $tahun }}</p>
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

                <!-- Tombol Cetak / Simpan PDF Langsung -->
                <button type="button" 
                        onclick="window.print()" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm hover:shadow transition cursor-pointer"
                        title="Buka dialog cetak atau Simpan sebagai PDF">
                    <svg class="w-4 h-4 text-blue-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak / Simpan PDF (A4)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== LEMBAR DOKUMEN UTAMA ==================== -->
    <div class="report-sheet space-y-5">
        
        <!-- ==================== HALAMAN 1: KOP SURAT, KPI & 3 GRAFIK ==================== -->
        <div class="avoid-break space-y-3">
            
            <!-- KOP SURAT RESMI -->
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 8px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <img src="{{ asset('images/logo-banjarnegara.png') }}" 
                         alt="Logo Banjarnegara" 
                         style="width: 50px; height: 60px; object-fit: contain; flex-shrink: 0;"
                         width="50" 
                         height="60">
                    <div style="line-height: 1.25; color: #0f172a;">
                        <div style="font-size: 8.5pt; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: #475569;">Pemerintah Kabupaten Banjarnegara</div>
                        <div style="font-size: 12.5pt; font-weight: 900; letter-spacing: 0.02em; text-transform: uppercase; color: #020617;">Dinas Pendidikan, Kepemudaan dan Olahraga</div>
                        <div style="font-size: 7.5pt; color: #475569;">Jalan Mayjend Soetojo No. 51 Banjarnegara 53412 &bull; Telp. (0286) 591244</div>
                        <div style="font-size: 7pt; font-family: 'JetBrains Mono', monospace; color: #64748b;">Laman: dindikpora.banjarnegarakab.go.id &bull; Pos-el: dindikpora@banjarnegarakab.go.id</div>
                    </div>
                </div>
                <div style="text-align: right; font-family: 'JetBrains Mono', monospace; font-size: 7.5pt; color: #475569;">
                    <div style="padding: 5px 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <strong style="color: #0f172a; display: block;">DOKUMEN RESMI SAKIP</strong>
                        <span>Tahun Anggaran {{ $tahun }}</span>
                    </div>
                </div>
            </div>

            <!-- JUDUL RESMI DOKUMEN -->
            <div style="text-align: center; padding: 4px 0;">
                <div style="font-size: 7.5pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #2563eb;">
                    Laporan Kinerja Urusan Pendidikan
                </div>
                <h2 style="font-size: 13pt; font-weight: 900; text-transform: uppercase; margin: 2px 0 0 0; color: #0f172a;">
                    {{ $officialData['labels']['title'] }}
                </h2>
                <div style="font-size: 8pt; color: #64748b; margin-top: 1px;">
                    Rekapitulasi Capaian Indikator Kinerja Berdasarkan Jenjang & Sebaran Wilayah Kabupaten Banjarnegara
                </div>
            </div>

            <!-- 4 KARTU KPI KABUPATEN (1 BARIS, 4 KOLOM PROPORSIONAL) -->
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
            <div class="report-grid-4">
                <div class="report-card">
                    <span style="font-size: 7pt; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">PAUD / TK (5–6 Th)</span>
                    <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 2px;">
                        <span class="font-mono" style="font-size: 14pt; font-weight: 900; color: #0f172a;">{{ number_format($capaianPaud, 2) }}%</span>
                        <span style="font-size: 7.5pt; font-weight: 600; color: #64748b;">Target: {{ number_format($targetPaud, 1) }}%</span>
                    </div>
                </div>

                <div class="report-card">
                    <span style="font-size: 7pt; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">SD / Sederajat (7–12 Th)</span>
                    <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 2px;">
                        <span class="font-mono" style="font-size: 14pt; font-weight: 900; color: #0f172a;">{{ number_format($capaianSd, 2) }}%</span>
                        <span style="font-size: 7.5pt; font-weight: 600; color: #64748b;">Target: {{ number_format($targetSd, 1) }}%</span>
                    </div>
                </div>

                <div class="report-card">
                    <span style="font-size: 7pt; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">SMP / Sederajat (13–15 Th)</span>
                    <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 2px;">
                        <span class="font-mono" style="font-size: 14pt; font-weight: 900; color: #0f172a;">{{ number_format($capaianSmp, 2) }}%</span>
                        <span style="font-size: 7.5pt; font-weight: 600; color: #64748b;">Target: {{ number_format($targetSmp, 1) }}%</span>
                    </div>
                </div>

                <div class="report-card">
                    <span style="font-size: 7pt; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">SMA / SMK (16–18 Th)</span>
                    <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 2px;">
                        <span class="font-mono" style="font-size: 14pt; font-weight: 900; color: #0f172a;">{{ number_format($capaianSma, 2) }}%</span>
                        <span style="font-size: 7.5pt; font-weight: 600; color: #64748b;">Target: {{ number_format($targetSma, 1) }}%</span>
                    </div>
                </div>
            </div>

            <!-- GRAFIK 1 & 2: BERDAMPINGAN -->
            <div class="report-grid-2">
                <div class="report-chart-box">
                    <div style="font-size: 8pt; font-weight: 700; text-align: center; text-transform: uppercase; color: #334155; margin-bottom: 4px;">
                        Capaian Riil vs Target Renstra per Jenjang (%)
                    </div>
                    <div style="height: 165px; position: relative;">
                        <canvas id="chartJenjangTarget"></canvas>
                    </div>
                </div>

                <div class="report-chart-box">
                    <div style="font-size: 8pt; font-weight: 700; text-align: center; text-transform: uppercase; color: #334155; margin-bottom: 4px;">
                        Komparasi Capaian Gender (Laki-laki vs Perempuan) (%)
                    </div>
                    <div style="height: 165px; position: relative;">
                        <canvas id="chartGender"></canvas>
                    </div>
                </div>
            </div>

            <!-- GRAFIK 3: SEBARAN 20 KECAMATAN -->
            <div class="report-chart-box" style="margin-top: 4px;">
                <div style="font-size: 8pt; font-weight: 700; text-align: center; text-transform: uppercase; color: #334155; margin-bottom: 4px;">
                    Sebaran Capaian {{ strtoupper($tipe) }} SD & SMP pada 20 Kecamatan se-Banjarnegara (%)
                </div>
                <div style="height: 175px; position: relative;">
                    <canvas id="chartKecamatan"></canvas>
                </div>
            </div>
        </div>

        <!-- ==================== HALAMAN 2: TABEL REKAPITULASI CAPAIAN 20 KECAMATAN (100% FIT & LEMBAR TANDA TANGAN) ==================== -->
        <div class="page-break space-y-3">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #cbd5e1; padding-bottom: 6px;">
                <div>
                    <h3 style="font-size: 9.5pt; font-weight: 900; text-transform: uppercase; margin: 0; color: #0f172a;">
                        1. Tabel Rekapitulasi Capaian {{ strtoupper($officialData['labels']['title']) }} Seluruh Kecamatan
                    </h3>
                    <div style="font-size: 7.5pt; color: #64748b;">Komparasi persentase capaian seluruh jenjang pendidikan dalam satu lembar utuh tanpa terpotong</div>
                </div>
                <div style="font-size: 7.5pt; font-family: 'JetBrains Mono', monospace; color: #64748b;">
                    T.A {{ $tahun }} &bull; 20 Kecamatan
                </div>
            </div>

            <!-- TABEL 7 KOLOM (FIT 100% LEBAR KERTAS A4 LANDSCAPE) -->
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 32px; text-align: center;">No</th>
                        <th style="width: 170px; text-align: left;">Kecamatan</th>
                        <th style="text-align: right;">PAUD / TK (5–6 Th)</th>
                        <th style="text-align: right;">SD / Sederajat (7–12 Th)</th>
                        <th style="text-align: right;">SMP / Sederajat (13–15 Th)</th>
                        <th style="text-align: right;">SMA / SMK (16–18 Th)</th>
                        <th style="text-align: right;">Rata-rata Capaian</th>
                        <th style="width: 100px; text-align: center;">Status SPM</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalRata = 0;
                        $countKec = count($officialData['rows']);
                    @endphp
                    @foreach($officialData['rows'] as $r)
                        @php
                            $cPaud = (float) $r['paud']['capaian']['jml'];
                            $cSd = (float) $r['sd']['capaian']['jml'];
                            $cSmp = (float) $r['smp']['capaian']['jml'];
                            $cSma = (float) ($r['sma']['capaian']['jml'] ?? 0);
                            $avg = ($cPaud + $cSd + $cSmp + $cSma) / 4;
                            $totalRata += $avg;
                            $isTuntas = $avg >= 85.0;
                        @endphp
                        <tr>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #64748b;">{{ $r['no'] }}</td>
                            <td style="font-weight: 700; color: #0f172a;">{{ $r['nama'] }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #2563eb;">{{ number_format($cPaud, 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #4f46e5;">{{ number_format($cSd, 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #0284c7;">{{ number_format($cSmp, 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #059669;">{{ number_format($cSma, 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #0f172a;">{{ number_format($avg, 2) }}%</td>
                            <td style="text-align: center;">
                                @if($isTuntas)
                                    <span class="badge-spm" style="background: #dcfce7; color: #166534;">Tuntas SPM</span>
                                @else
                                    <span class="badge-spm" style="background: #fef3c7; color: #92400e;">Perhatian</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php $tot = $officialData['total']; @endphp
                    <tr style="background: #e2e8f0; font-weight: 900; color: #0f172a;">
                        <td style="text-align: center;">#</td>
                        <td>TOTAL KABUPATEN</td>
                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['paud']['capaian']['jml'], 2) }}%</td>
                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['sd']['capaian']['jml'], 2) }}%</td>
                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['smp']['capaian']['jml'], 2) }}%</td>
                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['sma']['capaian']['jml'] ?? 0, 2) }}%</td>
                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($countKec > 0 ? $totalRata / $countKec : 0, 2) }}%</td>
                        <td style="text-align: center; font-size: 7pt; text-transform: uppercase;">Rata-rata Kab.</td>
                    </tr>
                </tfoot>
            </table>

            <!-- LEMBAR PENGESAHAN DOKUMEN RESMI (TANDA TANGAN) -->
            <div class="avoid-break" style="display: flex; justify-content: space-between; padding-top: 18px;">
                <div style="width: 280px; text-align: center; font-size: 7.5pt; color: #1e293b; line-height: 1.4;">
                    <div>Mengetahui,</div>
                    <div style="font-weight: 800; text-transform: uppercase;">Kasubbag Program & Evaluasi</div>
                    <div style="height: 50px;"></div>
                    <div style="font-weight: 800; text-decoration: underline;">SUBBAG PERENCANAAN</div>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 7pt; color: #64748b;">NIP. 19790512 200501 1 008</div>
                </div>

                <div style="width: 300px; text-align: center; font-size: 7.5pt; color: #1e293b; line-height: 1.4;">
                    <div>Banjarnegara, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                    <div style="font-weight: 800; text-transform: uppercase;">Kepala Dinas Pendidikan, Kepemudaan dan Olahraga</div>
                    <div style="height: 50px;"></div>
                    <div style="font-weight: 800; text-decoration: underline;">Dr. H. TEGUH HANDOKO, S.Sos., M.Si.</div>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 7pt; color: #64748b;">Pembina Utama Muda / IV c &bull; NIP. 19680815 199303 1 007</div>
                </div>
            </div>
        </div>

        <!-- ==================== HALAMAN 3: TABEL RINCIAN PENDUDUK & SISWA (PAUD & SD) ==================== -->
        <div class="page-break space-y-4">
            <div style="border-bottom: 1.5px solid #cbd5e1; padding-bottom: 6px;">
                <h3 style="font-size: 9.5pt; font-weight: 900; text-transform: uppercase; margin: 0; color: #0f172a;">
                    2. Rincian Data Kependudukan & Partisipasi Siswa: PAUD & SD/Sederajat
                </h3>
                <div style="font-size: 7.5pt; color: #64748b;">Rincian agregat penduduk Dukcapil, siswa aktif Dapodik/EMIS, dan rasio capaian gender (L, P, Jumlah)</div>
            </div>

            <!-- SUB-TABEL PAUD -->
            <div class="avoid-break space-y-1">
                <div style="font-size: 8pt; font-weight: 800; color: #1e40af; text-transform: uppercase; background: #eff6ff; padding: 4px 8px; border-radius: 6px; border: 1px solid #bfdbfe;">
                    A. {{ $officialData['labels']['paud_title'] }}
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 28px; text-align: center;">No</th>
                            <th rowspan="2" style="width: 140px; text-align: left;">Kecamatan</th>
                            <th colspan="3" style="text-align: center;">{{ $officialData['labels']['paud_penduduk'] }}</th>
                            <th colspan="3" style="text-align: center;">{{ $officialData['labels']['paud_siswa'] }}</th>
                            <th colspan="3" style="text-align: center; background: #dbeafe; color: #1e40af;">{{ $officialData['labels']['paud_capaian'] }}</th>
                        </tr>
                        <tr style="background: #f8fafc; font-size: 7pt;">
                            <th style="text-align: right;">L</th>
                            <th style="text-align: right;">P</th>
                            <th style="text-align: right;">Jml</th>
                            <th style="text-align: right;">L</th>
                            <th style="text-align: right;">P</th>
                            <th style="text-align: right;">Jml</th>
                            <th style="text-align: right; background: #eff6ff;">L (%)</th>
                            <th style="text-align: right; background: #eff6ff;">P (%)</th>
                            <th style="text-align: right; background: #dbeafe; font-weight: 900;">Jml (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($officialData['rows'] as $r)
                        <tr>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #64748b;">{{ $r['no'] }}</td>
                            <td style="font-weight: 700;">{{ $r['nama'] }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['paud']['penduduk']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['paud']['penduduk']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ number_format($r['paud']['penduduk']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['paud']['siswa']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['paud']['siswa']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ number_format($r['paud']['siswa']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #2563eb;">{{ number_format($r['paud']['capaian']['l'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #db2777;">{{ number_format($r['paud']['capaian']['p'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 900; background: #eff6ff; color: #1e3a8a;">{{ number_format($r['paud']['capaian']['jml'], 2) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #e2e8f0; font-weight: 900; color: #0f172a;">
                            <td style="text-align: center;">#</td>
                            <td>JUMLAH</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['paud']['penduduk']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['paud']['penduduk']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['paud']['penduduk']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['paud']['siswa']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['paud']['siswa']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['paud']['siswa']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #1e40af;">{{ number_format($tot['paud']['capaian']['l'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #9d174d;">{{ number_format($tot['paud']['capaian']['p'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; background: #cbd5e1;">{{ number_format($tot['paud']['capaian']['jml'], 2) }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- SUB-TABEL SD -->
            <div class="avoid-break space-y-1">
                <div style="font-size: 8pt; font-weight: 800; color: #3730a3; text-transform: uppercase; background: #eef2ff; padding: 4px 8px; border-radius: 6px; border: 1px solid #c7d2fe;">
                    B. {{ $officialData['labels']['sd_title'] }}
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 28px; text-align: center;">No</th>
                            <th rowspan="2" style="width: 140px; text-align: left;">Kecamatan</th>
                            <th colspan="3" style="text-align: center;">{{ $officialData['labels']['sd_penduduk'] }}</th>
                            <th colspan="3" style="text-align: center;">{{ $officialData['labels']['sd_siswa'] }}</th>
                            <th colspan="3" style="text-align: center; background: #e0e7ff; color: #3730a3;">{{ $officialData['labels']['sd_capaian'] }}</th>
                        </tr>
                        <tr style="background: #f8fafc; font-size: 7pt;">
                            <th style="text-align: right;">L</th>
                            <th style="text-align: right;">P</th>
                            <th style="text-align: right;">Jml</th>
                            <th style="text-align: right;">L</th>
                            <th style="text-align: right;">P</th>
                            <th style="text-align: right;">Jml</th>
                            <th style="text-align: right; background: #eef2ff;">L (%)</th>
                            <th style="text-align: right; background: #eef2ff;">P (%)</th>
                            <th style="text-align: right; background: #e0e7ff; font-weight: 900;">Jml (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($officialData['rows'] as $r)
                        <tr>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #64748b;">{{ $r['no'] }}</td>
                            <td style="font-weight: 700;">{{ $r['nama'] }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['sd']['penduduk']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['sd']['penduduk']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ number_format($r['sd']['penduduk']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['sd']['siswa']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['sd']['siswa']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ number_format($r['sd']['siswa']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #4338ca;">{{ number_format($r['sd']['capaian']['l'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #db2777;">{{ number_format($r['sd']['capaian']['p'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 900; background: #eef2ff; color: #312e81;">{{ number_format($r['sd']['capaian']['jml'], 2) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #e2e8f0; font-weight: 900; color: #0f172a;">
                            <td style="text-align: center;">#</td>
                            <td>JUMLAH</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['sd']['penduduk']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['sd']['penduduk']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['sd']['penduduk']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['sd']['siswa']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['sd']['siswa']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['sd']['siswa']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #3730a3;">{{ number_format($tot['sd']['capaian']['l'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #9d174d;">{{ number_format($tot['sd']['capaian']['p'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; background: #cbd5e1;">{{ number_format($tot['sd']['capaian']['jml'], 2) }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ==================== HALAMAN 4: TABEL RINCIAN PENDUDUK & SISWA (SMP & SMA/SMK) ==================== -->
        <div class="page-break space-y-4">
            <div style="border-bottom: 1.5px solid #cbd5e1; padding-bottom: 6px;">
                <h3 style="font-size: 9.5pt; font-weight: 900; text-transform: uppercase; margin: 0; color: #0f172a;">
                    3. Rincian Data Kependudukan & Partisipasi Siswa: SMP & SMA/SMK
                </h3>
                <div style="font-size: 7.5pt; color: #64748b;">Rincian agregat penduduk Dukcapil, siswa aktif Dapodik/EMIS, dan rasio capaian gender (L, P, Jumlah)</div>
            </div>

            <!-- SUB-TABEL SMP -->
            <div class="avoid-break space-y-1">
                <div style="font-size: 8pt; font-weight: 800; color: #0369a1; text-transform: uppercase; background: #f0f9ff; padding: 4px 8px; border-radius: 6px; border: 1px solid #bae6fd;">
                    A. {{ $officialData['labels']['smp_title'] }}
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 28px; text-align: center;">No</th>
                            <th rowspan="2" style="width: 140px; text-align: left;">Kecamatan</th>
                            <th colspan="3" style="text-align: center;">{{ $officialData['labels']['smp_penduduk'] }}</th>
                            <th colspan="3" style="text-align: center;">{{ $officialData['labels']['smp_siswa'] }}</th>
                            <th colspan="3" style="text-align: center; background: #e0f2fe; color: #0369a1;">{{ $officialData['labels']['smp_capaian'] }}</th>
                        </tr>
                        <tr style="background: #f8fafc; font-size: 7pt;">
                            <th style="text-align: right;">L</th>
                            <th style="text-align: right;">P</th>
                            <th style="text-align: right;">Jml</th>
                            <th style="text-align: right;">L</th>
                            <th style="text-align: right;">P</th>
                            <th style="text-align: right;">Jml</th>
                            <th style="text-align: right; background: #f0f9ff;">L (%)</th>
                            <th style="text-align: right; background: #f0f9ff;">P (%)</th>
                            <th style="text-align: right; background: #e0f2fe; font-weight: 900;">Jml (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($officialData['rows'] as $r)
                        <tr>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #64748b;">{{ $r['no'] }}</td>
                            <td style="font-weight: 700;">{{ $r['nama'] }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['smp']['penduduk']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['smp']['penduduk']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ number_format($r['smp']['penduduk']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['smp']['siswa']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['smp']['siswa']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ number_format($r['smp']['siswa']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #0284c7;">{{ number_format($r['smp']['capaian']['l'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #db2777;">{{ number_format($r['smp']['capaian']['p'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 900; background: #f0f9ff; color: #0c4a6e;">{{ number_format($r['smp']['capaian']['jml'], 2) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #e2e8f0; font-weight: 900; color: #0f172a;">
                            <td style="text-align: center;">#</td>
                            <td>JUMLAH</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['smp']['penduduk']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['smp']['penduduk']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['smp']['penduduk']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['smp']['siswa']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['smp']['siswa']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($tot['smp']['siswa']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #0369a1;">{{ number_format($tot['smp']['capaian']['l'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #9d174d;">{{ number_format($tot['smp']['capaian']['p'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; background: #cbd5e1;">{{ number_format($tot['smp']['capaian']['jml'], 2) }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- SUB-TABEL SMA / SMK -->
            <div class="avoid-break space-y-1">
                <div style="font-size: 8pt; font-weight: 800; color: #065f46; text-transform: uppercase; background: #ecfdf5; padding: 4px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                    B. {{ $officialData['labels']['sma_title'] ?? 'SMA / SMK (Usia 16–18 Tahun)' }}
                </div>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 28px; text-align: center;">No</th>
                            <th rowspan="2" style="width: 140px; text-align: left;">Kecamatan</th>
                            <th colspan="3" style="text-align: center;">{{ $officialData['labels']['sma_penduduk'] ?? 'Penduduk (16–18 Th)' }}</th>
                            <th colspan="3" style="text-align: center;">{{ $officialData['labels']['sma_siswa'] ?? 'Siswa SMA/SMK' }}</th>
                            <th colspan="3" style="text-align: center; background: #d1fae5; color: #065f46;">{{ $officialData['labels']['sma_capaian'] ?? 'Capaian SMA/SMK (%)' }}</th>
                        </tr>
                        <tr style="background: #f8fafc; font-size: 7pt;">
                            <th style="text-align: right;">L</th>
                            <th style="text-align: right;">P</th>
                            <th style="text-align: right;">Jml</th>
                            <th style="text-align: right;">L</th>
                            <th style="text-align: right;">P</th>
                            <th style="text-align: right;">Jml</th>
                            <th style="text-align: right; background: #ecfdf5;">L (%)</th>
                            <th style="text-align: right; background: #ecfdf5;">P (%)</th>
                            <th style="text-align: right; background: #d1fae5; font-weight: 900;">Jml (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($officialData['rows'] as $r)
                        <tr>
                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #64748b;">{{ $r['no'] }}</td>
                            <td style="font-weight: 700;">{{ $r['nama'] }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['sma']['penduduk']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['sma']['penduduk']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ number_format($r['sma']['penduduk']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['sma']['siswa']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($r['sma']['siswa']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ number_format($r['sma']['siswa']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #059669;">{{ number_format($r['sma']['capaian']['l'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #db2777;">{{ number_format($r['sma']['capaian']['p'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 900; background: #ecfdf5; color: #064e3b;">{{ number_format($r['sma']['capaian']['jml'], 2) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php $smaTot = $tot['sma'] ?? ['penduduk' => ['l' => 0, 'p' => 0, 'jml' => 0], 'siswa' => ['l' => 0, 'p' => 0, 'jml' => 0], 'capaian' => ['l' => 0, 'p' => 0, 'jml' => 0]]; @endphp
                        <tr style="background: #e2e8f0; font-weight: 900; color: #0f172a;">
                            <td style="text-align: center;">#</td>
                            <td>JUMLAH</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($smaTot['penduduk']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($smaTot['penduduk']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($smaTot['penduduk']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($smaTot['siswa']['l']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($smaTot['siswa']['p']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">{{ number_format($smaTot['siswa']['jml']) }}</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #065f46;">{{ number_format($smaTot['capaian']['l'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #9d174d;">{{ number_format($smaTot['capaian']['p'], 2) }}%</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; background: #cbd5e1;">{{ number_format($smaTot['capaian']['jml'], 2) }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- CATATAN KAKI SUMBER DATA -->
            <div style="font-size: 7.5pt; color: #64748b; font-style: italic; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                Sumber data resmi : DISDUKCAPIL Kab. Banjarnegara, DAPODIK KEMENDIKBUDRISTEK, dan EMIS KEMENAG RI. Dokumen dicetak secara terotomatisasi melalui SIMAK Pendidikan Kab. Banjarnegara pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }} WIB.
            </div>
        </div>

    </div>

    <script>
        let chartInstanceJenjang = null;
        let chartInstanceGender = null;
        let chartInstanceKecamatan = null;

        document.addEventListener('DOMContentLoaded', function () {
            // Setup Chart Defaults for crisp print & display
            Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
            Chart.defaults.color = '#334155';

            // 1. Chart Jenjang vs Target
            const ctxJenjang = document.getElementById('chartJenjangTarget')?.getContext('2d');
            if (ctxJenjang) {
                chartInstanceJenjang = new Chart(ctxJenjang, {
                    type: 'bar',
                    data: {
                        labels: ['PAUD', 'SD/MI', 'SMP/MTs', 'SMA/SMK'],
                        datasets: [
                            {
                                label: 'Capaian Riil (%)',
                                data: [{{ $capaianPaud }}, {{ $capaianSd }}, {{ $capaianSmp }}, {{ $capaianSma }}],
                                backgroundColor: 'rgba(37, 99, 235, 0.85)',
                                borderColor: '#1d4ed8',
                                borderWidth: 1,
                                borderRadius: 4,
                                barPercentage: 0.6
                            },
                            {
                                label: 'Target Renstra (%)',
                                data: [{{ $targetPaud }}, {{ $targetSd }}, {{ $targetSmp }}, {{ $targetSma }}],
                                backgroundColor: 'rgba(234, 88, 12, 0.85)',
                                borderColor: '#c2410c',
                                borderWidth: 1,
                                borderRadius: 4,
                                barPercentage: 0.6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 10, font: { size: 8.5, weight: 'bold' } } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 120,
                                ticks: { font: { size: 8 }, callback: v => v + '%' }
                            },
                            x: {
                                ticks: { font: { size: 8, weight: 'bold' } }
                            }
                        }
                    }
                });
            }

            // 2. Chart Gender (L vs P)
            const ctxGender = document.getElementById('chartGender')?.getContext('2d');
            if (ctxGender) {
                chartInstanceGender = new Chart(ctxGender, {
                    type: 'bar',
                    data: {
                        labels: ['PAUD', 'SD/MI', 'SMP/MTs', 'SMA/SMK'],
                        datasets: [
                            {
                                label: 'Laki-laki ♂ (%)',
                                data: [
                                    {{ (float) $tot['paud']['capaian']['l'] }},
                                    {{ (float) $tot['sd']['capaian']['l'] }},
                                    {{ (float) $tot['smp']['capaian']['l'] }},
                                    {{ (float) ($tot['sma']['capaian']['l'] ?? 0) }}
                                ],
                                backgroundColor: 'rgba(37, 99, 235, 0.85)',
                                borderColor: '#1d4ed8',
                                borderWidth: 1,
                                borderRadius: 4,
                                barPercentage: 0.6
                            },
                            {
                                label: 'Perempuan ♀ (%)',
                                data: [
                                    {{ (float) $tot['paud']['capaian']['p'] }},
                                    {{ (float) $tot['sd']['capaian']['p'] }},
                                    {{ (float) $tot['smp']['capaian']['p'] }},
                                    {{ (float) ($tot['sma']['capaian']['p'] ?? 0) }}
                                ],
                                backgroundColor: 'rgba(219, 39, 119, 0.85)',
                                borderColor: '#be185d',
                                borderWidth: 1,
                                borderRadius: 4,
                                barPercentage: 0.6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 10, font: { size: 8.5, weight: 'bold' } } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 120,
                                ticks: { font: { size: 8 }, callback: v => v + '%' }
                            },
                            x: {
                                ticks: { font: { size: 8, weight: 'bold' } }
                            }
                        }
                    }
                });
            }

            // 3. Chart Sebaran 20 Kecamatan
            @php
                $labelsKec = [];
                $sdKec = [];
                $smpKec = [];
                foreach($officialData['rows'] as $r) {
                    $labelsKec[] = $r['nama'];
                    $sdKec[] = (float) $r['sd']['capaian']['jml'];
                    $smpKec[] = (float) $r['smp']['capaian']['jml'];
                }
            @endphp
            const ctxKec = document.getElementById('chartKecamatan')?.getContext('2d');
            if (ctxKec) {
                chartInstanceKecamatan = new Chart(ctxKec, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($labelsKec) !!},
                        datasets: [
                            {
                                label: 'SD / Sederajat (%)',
                                data: {!! json_encode($sdKec) !!},
                                backgroundColor: 'rgba(79, 70, 229, 0.85)',
                                borderColor: '#4338ca',
                                borderWidth: 1,
                                borderRadius: 3
                            },
                            {
                                label: 'SMP / Sederajat (%)',
                                data: {!! json_encode($smpKec) !!},
                                backgroundColor: 'rgba(14, 165, 233, 0.85)',
                                borderColor: '#0284c7',
                                borderWidth: 1,
                                borderRadius: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 10, font: { size: 8.5, weight: 'bold' } } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { font: { size: 7.5 }, callback: v => v + '%' }
                            },
                            x: {
                                ticks: {
                                    font: { size: 7.5 },
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