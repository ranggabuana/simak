@extends('layouts.app', ['title' => 'Angka Partisipasi Sekolah (APS)'])

@section('content')
<div class="space-y-8">

    <!-- Formula Reference Banner (From Flowchart.pdf) -->
    <div class="p-6 rounded-3xl bg-emerald-50/70 border border-emerald-200 text-emerald-950 shadow-sm space-y-4">
        <!-- Formula Explanation (Full Width) -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-600 text-white tracking-wider">Formula Flowchart</span>
                <h2 class="text-base font-bold text-slate-900">Rumus Angka Partisipasi Sekolah (APS)</h2>
            </div>

            <!-- Box Persamaan Matematis -->
            <div class="p-3.5 bg-white/95 rounded-2xl border border-emerald-100 shadow-2xs space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>Persamaan Matematis:</span>
                </div>
                <p class="text-xs text-slate-700 leading-relaxed font-mono bg-emerald-50/50 p-2.5 rounded-xl border border-emerald-100/70">
                    APS = (<span class="text-emerald-700 font-bold">Data A: Anak Usia Tertentu Bersekolah di Semua Jenjang</span> / <span class="text-teal-700 font-bold">Data B: Penduduk Usia Kelompok Tersebut</span>) &times; 100%
                </p>
                <p class="text-[11px] text-slate-500 italic">
                    *Contoh: APS SD/MI = (Jumlah anak usia 7–12 tahun yang bersekolah di jenjang manapun / Jumlah total penduduk usia 7–12 tahun di Banjarnegara) &times; 100%
                </p>
            </div>

            <!-- Keterangan Sumber Data & Alur Penilaian -->
            <div class="space-y-2 pt-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Keterangan Sumber Data & Alur Penilaian (Data A & Data B):
                    </span>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-md border border-emerald-200">
                        Integrasi Sistem
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Data A (Pembilang) -->
                    <div class="bg-white/95 rounded-2xl p-4 border border-emerald-100 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Data A (Pembilang / Numerator)
                            </span>
                            <a href="{{ route('siswa.index') }}" 
                               class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                <span>Buka Menu Siswa</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Jumlah Anak Usia Tertentu yang Bersekolah di Semua Jenjang</p>
                            <p class="text-[11px] text-emerald-700 font-semibold mt-0.5">
                                Sumber: Menu Master Data &rarr; <span class="underline">Partisipasi Siswa (Dapodik)</span>
                            </p>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Diambil dari kolom kelompok umur (kolom <code>siswa_usia_5_6</code>, <code>siswa_usia_7_12</code>, <code>siswa_usia_13_15</code>, <code>siswa_usia_16_18</code>). Menghitung seluruh anak pada kelompok umur tersebut yang sedang bersekolah di jenjang mana pun (formal maupun paket kesetaraan).
                        </p>
                    </div>

                    <!-- Data B (Penyebut) -->
                    <div class="bg-white/95 rounded-2xl p-4 border border-emerald-100 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-teal-50 text-teal-700 border border-teal-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                                Data B (Penyebut / Denominator)
                            </span>
                            <a href="{{ route('tujuan1.index') }}" 
                               class="text-[11px] font-bold text-teal-600 hover:text-teal-800 hover:underline inline-flex items-center gap-1">
                                <span>Buka Menu Penduduk</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Jumlah Penduduk pada Kelompok Usia Tersebut</p>
                            <p class="text-[11px] text-teal-700 font-semibold mt-0.5">
                                Sumber: Menu Master Data &rarr; <span class="underline">Tujuan 1: Penduduk Usia Sekolah</span>
                            </p>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Diambil dari data agregat kependudukan Dukcapil Kabupaten Banjarnegara per rentang usia resmi jenjang: <strong>5–6 th</strong> (PAUD), <strong>7–12 th</strong> (SD/MI), <strong>13–15 th</strong> (SMP/MTs), dan <strong>16–18 th</strong> (SMA/SMK).
                        </p>
                    </div>
                </div>

                <!-- Alur Penilaian & Target Renstra Footer Note -->
                <div class="p-3 bg-white/90 rounded-2xl border border-emerald-100 text-[11px] text-slate-700 shadow-2xs flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">ℹ</span>
                    <div class="space-y-0.5">
                        <strong class="text-slate-900 font-semibold">Alur Penilaian Kinerja:</strong>
                        <p class="text-slate-600 leading-relaxed">
                            Sistem menghitung persentase capaian riil dengan rumus <code>(Data A &divide; Data B) &times; 100%</code>, lalu membandingkannya terhadap sasaran pada menu 
                            <a href="{{ route('renstra.target') }}" class="font-bold text-emerald-700 hover:underline">Target Renstra (2025–2029)</a>. 
                            Nilai APS <em>maksimal 100%</em> dan menjadi tolok ukur utama pemetaan <strong>Anak Tidak Sekolah (ATS)</strong> di Banjarnegara (100% &minus; APS = Estimasi ATS).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dedicated Action Toolbar (Divider + Filter Gender Left + Unduh Excel Right) -->
        <div class="pt-3.5 border-t border-emerald-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Gender:
                </span>
                <div class="inline-flex items-center p-1 bg-white rounded-2xl border border-emerald-200 text-xs font-semibold shadow-2xs">
                    <a href="{{ route('indikator.aps', array_merge(request()->query(), ['gender' => ''])) }}" 
                       class="px-3.5 py-1.5 rounded-xl transition {{ empty($gender) ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                    <a href="{{ route('indikator.aps', array_merge(request()->query(), ['gender' => 'L'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl transition {{ $gender === 'L' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        ♂ Laki-laki
                    </a>
                    <a href="{{ route('indikator.aps', array_merge(request()->query(), ['gender' => 'P'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl transition {{ $gender === 'P' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        ♀ Perempuan
                    </a>
                </div>
            </div>

            <a href="{{ route('export.kecamatan', array_merge(request()->query(), ['tahun' => $tahun])) }}" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-emerald-700 hover:text-emerald-800 border border-emerald-200 font-bold text-xs shadow-2xs hover:shadow-xs transition shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Rekap Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- 4 Age Breakdown Cards (5-6, 7-12, 13-15, 16-18) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 5 - 6 Tahun (PAUD) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APS 5 – 6 TAHUN</span>
                @if($gender)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                        {{ $gender === 'L' ? '♂ L' : '♀ P' }}
                    </span>
                @endif
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['aps']['paud'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_aps_paud ?? 70.1 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Bersekolah (Semua Jenjang):</span> <strong>{{ number_format($kabupaten['populasi']['usia_5_6'] * ($kabupaten['aps']['paud']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 5–6 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_5_6']) }}</strong></div>
            </div>
        </div>

        <!-- 7 - 12 Tahun (SD/MI) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APS 7 – 12 TAHUN</span>
                @if($gender)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                        {{ $gender === 'L' ? '♂ L' : '♀ P' }}
                    </span>
                @endif
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-600">{{ number_format($kabupaten['aps']['sd'], 2) }}%</span>
                <span class="text-xs font-semibold text-emerald-600">Target: {{ $kabupaten['target']?->target_aps_sd ?? 99.2 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Bersekolah (Semua Jenjang):</span> <strong>{{ number_format($kabupaten['populasi']['usia_7_12'] * ($kabupaten['aps']['sd']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 7–12 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_7_12']) }}</strong></div>
            </div>
        </div>

        <!-- 13 - 15 Tahun (SMP/MTs) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APS 13 – 15 TAHUN</span>
                @if($gender)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                        {{ $gender === 'L' ? '♂ L' : '♀ P' }}
                    </span>
                @endif
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['aps']['smp'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_aps_smp ?? 95.8 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Bersekolah (Semua Jenjang):</span> <strong>{{ number_format($kabupaten['populasi']['usia_13_15'] * ($kabupaten['aps']['smp']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 13–15 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_13_15']) }}</strong></div>
            </div>
        </div>

        <!-- 16 - 18 Tahun (SMA/SMK) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APS 16 – 18 TAHUN</span>
                @if($gender)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                        {{ $gender === 'L' ? '♂ L' : '♀ P' }}
                    </span>
                @endif
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['aps']['sma'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_aps_sma ?? 78.9 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Bersekolah (Semua Jenjang):</span> <strong>{{ number_format($kabupaten['populasi']['usia_16_18'] * ($kabupaten['aps']['sma']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 16–18 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_16_18']) }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Analisis Paritas Gender APS (Gender Parity Index / GPI) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Analisis Paritas Gender APS (Gender Parity Index / GPI)</h3>
                <p class="text-xs text-slate-500">Perbandingan Angka Partisipasi Sekolah Laki-laki vs Perempuan dan Rasio Paritas Gender (Target Ideal GPI = 0.97 - 1.03)</p>
            </div>
            <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto">
                Tahun {{ $tahun }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px] block">Usia 5-6 Th</span>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Laki-laki:</span>
                    <strong class="text-emerald-700 font-bold">{{ number_format($kabupaten['gender_comparison']['aps_paud_l'] ?? 0, 1) }}%</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Perempuan:</span>
                    <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['aps_paud_p'] ?? 0, 1) }}%</strong>
                </div>
                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                    <span class="font-semibold text-slate-600">GPI (P/L):</span>
                    <span class="px-2 py-0.5 rounded font-extrabold text-[11px] bg-white border border-slate-200 text-slate-800">
                        {{ number_format($kabupaten['gender_comparison']['gpi_aps_paud'] ?? 1, 2) }}
                    </span>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100 space-y-2">
                <span class="font-bold text-emerald-900 uppercase tracking-wider text-[11px] block">Usia 7-12 Th</span>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Laki-laki:</span>
                    <strong class="text-emerald-700 font-bold">{{ number_format($kabupaten['gender_comparison']['aps_sd_l'] ?? 0, 1) }}%</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Perempuan:</span>
                    <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['aps_sd_p'] ?? 0, 1) }}%</strong>
                </div>
                <div class="pt-2 border-t border-emerald-200/70 flex items-center justify-between">
                    <span class="font-semibold text-slate-700">GPI (P/L):</span>
                    <span class="px-2 py-0.5 rounded font-extrabold text-[11px] bg-white border border-emerald-200 text-emerald-900">
                        {{ number_format($kabupaten['gender_comparison']['gpi_aps_sd'] ?? 1, 2) }}
                    </span>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-teal-50/50 border border-teal-100 space-y-2">
                <span class="font-bold text-teal-900 uppercase tracking-wider text-[11px] block">Usia 13-15 Th</span>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Laki-laki:</span>
                    <strong class="text-emerald-700 font-bold">{{ number_format($kabupaten['gender_comparison']['aps_smp_l'] ?? 0, 1) }}%</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Perempuan:</span>
                    <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['aps_smp_p'] ?? 0, 1) }}%</strong>
                </div>
                <div class="pt-2 border-t border-teal-200/70 flex items-center justify-between">
                    <span class="font-semibold text-slate-700">GPI (P/L):</span>
                    <span class="px-2 py-0.5 rounded font-extrabold text-[11px] bg-white border border-teal-200 text-teal-900">
                        {{ number_format($kabupaten['gender_comparison']['gpi_aps_smp'] ?? 1, 2) }}
                    </span>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px] block">Usia 16-18 Th</span>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Laki-laki:</span>
                    <strong class="text-emerald-700 font-bold">{{ number_format($kabupaten['gender_comparison']['aps_sma_l'] ?? 0, 1) }}%</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Perempuan:</span>
                    <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['aps_sma_p'] ?? 0, 1) }}%</strong>
                </div>
                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                    <span class="font-semibold text-slate-600">GPI (P/L):</span>
                    <span class="px-2 py-0.5 rounded font-extrabold text-[11px] bg-white border border-slate-200 text-slate-800">
                        {{ number_format($kabupaten['gender_comparison']['gpi_aps_sma'] ?? 1, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Basic Wilayah Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-900">Agregat Angka Partisipasi Sekolah per Kecamatan</h3>
            <p class="text-xs text-slate-500">Menghitung seluruh anak usia sekolah yang berpartisipasi pada jenjang formal maupun kesetaraan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Nama Kecamatan</th>
                        <th class="py-3.5 px-4 text-center">APS 5–6 Th {{ $gender ? '('.$gender.')' : '' }}</th>
                        <th class="py-3.5 px-4 text-center">APS 7–12 Th {{ $gender ? '('.$gender.')' : '' }}</th>
                        <th class="py-3.5 px-4 text-center">APS 13–15 Th {{ $gender ? '('.$gender.')' : '' }}</th>
                        <th class="py-3.5 px-4 text-center">APS 16–18 Th {{ $gender ? '('.$gender.')' : '' }}</th>
                        <th class="py-3.5 px-4 text-right">Anak Usia 7-12 Th</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kecamatanList as $kec)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-mono text-slate-400">{{ $kec['kode'] }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $kec['nama'] }}</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['aps']['paud'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center font-bold text-emerald-600">{{ number_format($kec['aps']['sd'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['aps']['smp'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['aps']['sma'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-right font-medium text-slate-700">
                            {{ number_format($kec['populasi']['usia_7_12']) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
