@extends('layouts.app', ['title' => 'Angka Partisipasi Sekolah (APS)'])

@section('content')
<div class="space-y-8" x-data="{ infoModal: false }">

    <!-- Formula Reference Banner (Compact with Modal Trigger) -->
    <div class="p-5 sm:p-6 rounded-3xl bg-emerald-50/70 border border-emerald-200 text-emerald-950 shadow-sm space-y-4">
        <!-- Formula Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-600 text-white tracking-wider">Formula Flowchart</span>
                    <h2 class="text-base font-bold text-slate-900">Rumus Angka Partisipasi Sekolah (APS)</h2>
                </div>
                <p class="text-xs text-slate-600">
                    APS mengukur persentase daya serap anak kelompok usia tertentu yang bersekolah di seluruh jenjang pendidikan (termasuk paket kesetaraan).
                </p>
            </div>

            <!-- Tombol Buka Popup Modal Info Rumus -->
            <button type="button" 
                    @click="infoModal = true"
                    class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 hover:text-emerald-800 border border-emerald-300 font-bold text-xs shadow-2xs hover:shadow-xs transition shrink-0 cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Info Rumus & Alur Sumber Data</span>
                <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-[10px] text-emerald-800 font-extrabold">Popup</span>
            </button>
        </div>

        <!-- Formula Compact One-Liner Box -->
        <div class="p-3 bg-white/95 rounded-2xl border border-emerald-100 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-2.5 text-xs text-slate-700">
            <div class="flex items-center gap-2 font-mono flex-wrap">
                <span class="px-2 py-0.5 rounded-md bg-emerald-100/80 text-emerald-800 text-[10px] font-extrabold uppercase">Rumus</span>
                <span>APS = (<strong class="text-emerald-700">Data A: Anak Usia Kelompok Bersekolah di Semua Jenjang</strong> / <strong class="text-teal-700">Data B: Penduduk Usia Kelompok</strong>) &times; 100%</span>
            </div>
            <div class="text-[11px] text-slate-500 flex items-center gap-2 shrink-0">
                <span>Data A: Dapodik</span>
                <span class="text-slate-300">&bull;</span>
                <span>Data B: Dukcapil</span>
                <button type="button" @click="infoModal = true" class="text-emerald-600 font-bold hover:underline cursor-pointer">
                    Lihat Rincian &rarr;
                </button>
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

    <!-- Interactive Visual Analytics (Charts Section) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart 1: Perbandingan Capaian APS Kelompok Umur vs Target Renstra (Bar Chart) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Perbandingan APS Riil vs Target Renstra</h3>
                    <p class="text-xs text-slate-500">Persentase partisipasi sekolah penduduk usia 5–18 tahun Kabupaten Banjarnegara</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-medium">
                    <span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold">
                        <span class="w-3 h-3 rounded bg-emerald-600 inline-block"></span> Capaian Riil
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-amber-500 font-bold">
                        <span class="w-3 h-3 rounded bg-amber-400 inline-block"></span> Target Renstra
                    </span>
                </div>
            </div>
            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="apsJenjangChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Komparasi Gender APS Tiap Kelompok Usia (L vs P) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Komparasi APS Gender</h3>
                    <p class="text-xs text-slate-500">Laki-laki (♂) vs Perempuan (♀)</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Paritas
                </span>
            </div>
            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="apsGenderChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 3: Distribusi Sebaran APS 20 Kecamatan dengan Filter Kelompok Usia -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4" x-data="{ activeFilter: 'all' }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Sebaran APS per Kelompok Usia di 20 Kecamatan Banjarnegara</h3>
                <p class="text-xs text-slate-500">Visualisasi komparasi capaian angka partisipasi sekolah per kecamatan</p>
            </div>
            <!-- Switcher Kelompok Usia Pill -->
            <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-100 rounded-2xl self-start sm:self-auto">
                <button type="button" 
                        @click="activeFilter = 'all'; updateApsChart('all')" 
                        :class="activeFilter === 'all' ? 'bg-white text-emerald-600 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 py-1 text-[11px] rounded-xl transition cursor-pointer">
                    Semua Usia
                </button>
                <button type="button" 
                        @click="activeFilter = 'paud'; updateApsChart('paud')" 
                        :class="activeFilter === 'paud' ? 'bg-white text-emerald-600 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 py-1 text-[11px] rounded-xl transition cursor-pointer">
                    5–6 Th
                </button>
                <button type="button" 
                        @click="activeFilter = 'sd'; updateApsChart('sd')" 
                        :class="activeFilter === 'sd' ? 'bg-white text-emerald-600 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 py-1 text-[11px] rounded-xl transition cursor-pointer">
                    7–12 Th (SD)
                </button>
                <button type="button" 
                        @click="activeFilter = 'smp'; updateApsChart('smp')" 
                        :class="activeFilter === 'smp' ? 'bg-white text-emerald-600 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 py-1 text-[11px] rounded-xl transition cursor-pointer">
                    13–15 Th (SMP)
                </button>
                <button type="button" 
                        @click="activeFilter = 'sma'; updateApsChart('sma')" 
                        :class="activeFilter === 'sma' ? 'bg-white text-emerald-600 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 py-1 text-[11px] rounded-xl transition cursor-pointer">
                    16–18 Th (SMA)
                </button>
            </div>
        </div>
        <div class="relative h-72 sm:h-80 w-full">
            <canvas id="apsKecamatanChart"></canvas>
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

    <!-- POPUP MODAL: INFO RUMUS & ALUR SUMBER DATA APS -->
    <div x-show="infoModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="infoModal = false">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto"
             @click.away="infoModal = false">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-slate-100 pb-3 gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-emerald-50 text-emerald-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-emerald-600 text-white tracking-wider">Formula Flowchart</span>
                            <h3 class="text-base font-bold text-slate-900">Info Rumus & Alur Sumber Data APS</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Penjelasan alur kalkulasi Angka Partisipasi Sekolah dan integrasi antar menu sistem SIMAK</p>
                    </div>
                </div>
                <button type="button" 
                        @click="infoModal = false" 
                        class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content Body -->
            <div class="space-y-4 text-xs text-slate-700">
                <!-- Persamaan Matematis Lengkap -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        Persamaan Matematis Kemendikbud:
                    </span>
                    <p class="p-3 bg-white font-mono rounded-xl border border-slate-200 text-slate-800 leading-relaxed shadow-2xs">
                        APS = (<span class="text-emerald-700 font-bold">Data A: Anak Usia Tertentu Bersekolah di Semua Jenjang</span> / <span class="text-teal-700 font-bold">Data B: Penduduk Usia Kelompok Tersebut</span>) &times; 100%
                    </p>
                    <p class="text-[11px] text-slate-500 italic">
                        *Contoh: APS SD/MI = (Jumlah anak usia 7–12 tahun yang bersekolah di jenjang manapun / Jumlah total penduduk usia 7–12 tahun di Banjarnegara) &times; 100%
                    </p>
                </div>

                <!-- Sumber Data Pembilang & Penyebut (Masing-masing 1 Baris Penuh) -->
                <div class="space-y-3">
                    <!-- Baris 1: Data A (Pembilang) -->
                    <div class="bg-emerald-50/50 rounded-2xl p-4 border border-emerald-200/80 space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-emerald-100 pb-2">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-emerald-600 text-white shadow-2xs">
                                    Data A (Pembilang / Numerator)
                                </span>
                                <span class="text-xs font-bold text-slate-900">Jumlah Anak Usia Tertentu yang Bersekolah di Semua Jenjang</span>
                            </div>
                            <a href="{{ route('siswa.index') }}" 
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] border border-emerald-200 shadow-2xs transition shrink-0">
                                <span>Buka Menu Siswa (Dapodik)</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            <span class="font-semibold text-slate-800">Sumber:</span> Menu 📁 <strong class="text-emerald-900">Master Data &rarr; Partisipasi Siswa (Dapodik)</strong> (kolom kelompok umur <code>siswa_usia_5_6</code>, <code>siswa_usia_7_12</code>, <code>siswa_usia_13_15</code>, <code>siswa_usia_16_18</code>). Menghitung seluruh anak pada kelompok umur tersebut yang sedang bersekolah di jenjang mana pun (formal maupun paket kesetaraan non-formal).
                        </p>
                    </div>

                    <!-- Baris 2: Data B (Penyebut) -->
                    <div class="bg-teal-50/50 rounded-2xl p-4 border border-teal-200/80 space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-teal-100 pb-2">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-teal-600 text-white shadow-2xs">
                                    Data B (Penyebut / Denominator)
                                </span>
                                <span class="text-xs font-bold text-slate-900">Jumlah Penduduk pada Kelompok Usia Tersebut</span>
                            </div>
                            <a href="{{ route('tujuan1.index') }}" 
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white hover:bg-teal-100 text-teal-700 font-bold text-[11px] border border-teal-200 shadow-2xs transition shrink-0">
                                <span>Buka Menu Penduduk</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            <span class="font-semibold text-slate-800">Sumber:</span> Menu 📁 <strong class="text-teal-900">Master Data &rarr; Tujuan 1: Penduduk Usia Sekolah</strong>. Total data agregat kependudukan Dukcapil Kabupaten Banjarnegara per rentang usia resmi jenjang: <strong>5–6 th</strong> (PAUD), <strong>7–12 th</strong> (SD/MI), <strong>13–15 th</strong> (SMP/MTs), dan <strong>16–18 th</strong> (SMA/SMK).
                        </p>
                    </div>
                </div>

                <!-- Alur Penilaian & Target Renstra -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-[11px] text-slate-700 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shrink-0">⚡</span>
                        <strong class="text-slate-900 font-bold">Alur Penilaian & Komparasi Kinerja:</strong>
                    </div>
                    <p class="text-slate-600 leading-relaxed pl-7">
                        1. Sistem menghitung persentase capaian riil dengan rumus <code>(Data A &divide; Data B) &times; 100%</code>.<br>
                        2. Capaian riil dikomparasikan terhadap sasaran pada menu 
                        <a href="{{ route('renstra.target') }}" class="font-bold text-emerald-700 hover:underline">Target Renstra (2025–2029)</a>.<br>
                        3. <em>Catatan Khusus APS:</em> Nilai capaian <strong>maksimal bernilai 100%</strong> dan menjadi tolok ukur utama pemetaan <strong>Anak Tidak Sekolah (ATS)</strong> di Banjarnegara (100% &minus; APS = Estimasi ATS).
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                <button type="button" 
                        @click="infoModal = false" 
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                    Tutup Informasi
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Chart APS Riil vs Target
        const ctxJenjang = document.getElementById('apsJenjangChart')?.getContext('2d');
        if (ctxJenjang) {
            new Chart(ctxJenjang, {
                type: 'bar',
                data: {
                    labels: ['Usia 5-6 Th', 'Usia 7-12 Th', 'Usia 13-15 Th', 'Usia 16-18 Th'],
                    datasets: [
                        {
                            label: 'Capaian Riil (%)',
                            data: [
                                {{ (float) ($kabupaten['aps']['paud'] ?? 0) }},
                                {{ (float) ($kabupaten['aps']['sd'] ?? 0) }},
                                {{ (float) ($kabupaten['aps']['smp'] ?? 0) }},
                                {{ (float) ($kabupaten['aps']['sma'] ?? 0) }}
                            ],
                            backgroundColor: 'rgba(5, 150, 105, 0.85)',
                            borderColor: 'rgb(5, 150, 105)',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.6
                        },
                        {
                            label: 'Target Renstra (%)',
                            data: [
                                {{ (float) ($kabupaten['target']?->target_aps_paud ?? 85.0) }},
                                {{ (float) ($kabupaten['target']?->target_aps_sd ?? 99.5) }},
                                {{ (float) ($kabupaten['target']?->target_aps_smp ?? 95.8) }},
                                {{ (float) ($kabupaten['target']?->target_aps_sma ?? 78.9) }}
                            ],
                            backgroundColor: 'rgba(251, 191, 36, 0.85)',
                            borderColor: 'rgb(245, 158, 11)',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${Number(ctx.raw).toFixed(2)}%`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 105,
                            grid: { color: '#f1f5f9' },
                            ticks: { callback: v => v + '%' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Chart APS Gender (L vs P)
        const ctxGender = document.getElementById('apsGenderChart')?.getContext('2d');
        if (ctxGender) {
            new Chart(ctxGender, {
                type: 'bar',
                data: {
                    labels: ['5-6 Th', '7-12 Th', '13-15 Th', '16-18 Th'],
                    datasets: [
                        {
                            label: 'Laki-laki (%)',
                            data: [
                                {{ (float) ($kabupaten['gender_comparison']['aps_paud_l'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['aps_sd_l'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['aps_smp_l'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['aps_sma_l'] ?? 0) }}
                            ],
                            backgroundColor: 'rgba(5, 150, 105, 0.85)',
                            borderRadius: 4
                        },
                        {
                            label: 'Perempuan (%)',
                            data: [
                                {{ (float) ($kabupaten['gender_comparison']['aps_paud_p'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['aps_sd_p'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['aps_smp_p'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['aps_sma_p'] ?? 0) }}
                            ],
                            backgroundColor: 'rgba(236, 72, 153, 0.85)',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 12, font: { size: 11 } }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${Number(ctx.raw).toFixed(2)}%`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 105,
                            grid: { color: '#f1f5f9' },
                            ticks: { callback: v => v + '%' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 3. Chart Sebaran APS 20 Kecamatan dengan Switcher Kelompok Usia
        const ctxKec = document.getElementById('apsKecamatanChart')?.getContext('2d');
        if (ctxKec) {
            const kecLabels = {!! json_encode($kecamatanList->pluck('nama')) !!};
            const apsPaudData = {!! json_encode($kecamatanList->pluck('aps.paud')) !!};
            const apsSdData = {!! json_encode($kecamatanList->pluck('aps.sd')) !!};
            const apsSmpData = {!! json_encode($kecamatanList->pluck('aps.smp')) !!};
            const apsSmaData = {!! json_encode($kecamatanList->pluck('aps.sma')) !!};

            const dsPaud = {
                label: 'APS 5–6 Th (%)',
                data: apsPaudData,
                backgroundColor: 'rgba(245, 158, 11, 0.85)',
                borderRadius: 4
            };
            const dsSd = {
                label: 'APS 7–12 Th (%)',
                data: apsSdData,
                backgroundColor: 'rgba(5, 150, 105, 0.85)',
                borderRadius: 4
            };
            const dsSmp = {
                label: 'APS 13–15 Th (%)',
                data: apsSmpData,
                backgroundColor: 'rgba(13, 148, 136, 0.85)',
                borderRadius: 4
            };
            const dsSma = {
                label: 'APS 16–18 Th (%)',
                data: apsSmaData,
                backgroundColor: 'rgba(14, 165, 233, 0.85)',
                borderRadius: 4
            };

            const datasetsMap = {
                all: [dsPaud, dsSd, dsSmp, dsSma],
                paud: [dsPaud],
                sd: [dsSd],
                smp: [dsSmp],
                sma: [dsSma]
            };

            const apsKecChart = new Chart(ctxKec, {
                type: 'bar',
                data: {
                    labels: kecLabels,
                    datasets: datasetsMap.all
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { boxWidth: 12, font: { size: 11 } }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${Number(ctx.raw).toFixed(2)}%`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 105,
                            grid: { color: '#f1f5f9' },
                            ticks: { callback: v => v + '%' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 45,
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });

            window.updateApsChart = function (jenjang) {
                apsKecChart.data.datasets = datasetsMap[jenjang] || datasetsMap.all;
                apsKecChart.options.plugins.legend.display = (jenjang === 'all');
                apsKecChart.update();
            };
        }
    });
</script>
@endpush
