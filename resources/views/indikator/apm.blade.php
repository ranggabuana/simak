@extends('layouts.app', ['title' => 'Angka Partisipasi Murni (APM)'])

@section('content')
<div class="space-y-8" x-data="{ infoModal: false }">

    <!-- Formula Reference Banner (Compact with Modal Trigger) -->
    <div class="p-5 sm:p-6 rounded-3xl bg-indigo-50/70 border border-indigo-200 text-indigo-950 shadow-sm space-y-4">
        <!-- Formula Header & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-indigo-600 text-white tracking-wider">Formula Flowchart</span>
                    <h2 class="text-base font-bold text-slate-900">Rumus Angka Partisipasi Murni (APM)</h2>
                </div>
                <p class="text-xs text-slate-600">
                    APM mengukur persentase anak usia sekolah tertentu yang bersekolah tepat pada jenjang yang sesuai dengan kelompok usianya.
                </p>
            </div>

            <!-- Tombol Buka Popup Modal Info Rumus -->
            <button type="button" 
                    @click="infoModal = true"
                    class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-indigo-50 text-indigo-700 hover:text-indigo-800 border border-indigo-300 font-bold text-xs shadow-2xs hover:shadow-xs transition shrink-0 cursor-pointer">
                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Info Rumus & Alur Sumber Data</span>
                <span class="px-1.5 py-0.5 rounded-md bg-indigo-100 text-[10px] text-indigo-800 font-extrabold">Popup</span>
            </button>
        </div>

        <!-- Formula Compact One-Liner Box -->
        <div class="p-3 bg-white/95 rounded-2xl border border-indigo-100 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-2.5 text-xs text-slate-700">
            <div class="flex items-center gap-2 font-mono flex-wrap">
                <span class="px-2 py-0.5 rounded-md bg-indigo-100/80 text-indigo-800 text-[10px] font-extrabold uppercase">Rumus</span>
                <span>APM = (<strong class="text-indigo-700">Data A: Siswa Usia Resmi di Jenjangnya</strong> / <strong class="text-purple-700">Data B: Penduduk Usia Jenjang</strong>) &times; 100%</span>
            </div>
            <div class="text-[11px] text-slate-500 flex items-center gap-2 shrink-0">
                <span>Data A: Siswa Usia Sesuai</span>
                <span class="text-slate-300">&bull;</span>
                <span>Data B: Dukcapil</span>
                <button type="button" @click="infoModal = true" class="text-indigo-600 font-bold hover:underline cursor-pointer">
                    Lihat Rincian &rarr;
                </button>
            </div>
        </div>

        <!-- Dedicated Action Toolbar (Divider + Filter Gender Left + Unduh Excel Right) -->
        <div class="pt-3.5 border-t border-indigo-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Gender:
                </span>
                <div class="inline-flex items-center p-1 bg-white rounded-2xl border border-indigo-200 text-xs font-semibold shadow-2xs">
                    <a href="{{ route('indikator.apm', array_merge(request()->query(), ['gender' => ''])) }}" 
                       class="px-3.5 py-1.5 rounded-xl transition {{ empty($gender) ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                    <a href="{{ route('indikator.apm', array_merge(request()->query(), ['gender' => 'L'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl transition {{ $gender === 'L' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        ♂ Laki-laki
                    </a>
                    <a href="{{ route('indikator.apm', array_merge(request()->query(), ['gender' => 'P'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl transition {{ $gender === 'P' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        ♀ Perempuan
                    </a>
                </div>
            </div>

            <a href="{{ route('export.kecamatan', array_merge(request()->query(), ['tahun' => $tahun])) }}" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-indigo-700 hover:text-indigo-800 border border-indigo-200 font-bold text-xs shadow-2xs hover:shadow-xs transition shrink-0">
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
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APM 5 – 6 TAHUN (PAUD/TK)</span>
                @if($gender)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                        {{ $gender === 'L' ? '♂ L' : '♀ P' }}
                    </span>
                @endif
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['apm']['paud'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_apm_paud ?? 65.2 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Siswa Usia Tepat (5-6 Th):</span> <strong>{{ number_format($kabupaten['populasi']['usia_5_6'] * ($kabupaten['apm']['paud']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 5–6 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_5_6']) }}</strong></div>
            </div>
        </div>

        <!-- 7 - 12 Tahun (SD/MI) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APM 7 – 12 TAHUN (SD/MI)</span>
                @if($gender)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                        {{ $gender === 'L' ? '♂ L' : '♀ P' }}
                    </span>
                @endif
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-indigo-600">{{ number_format($kabupaten['apm']['sd'], 2) }}%</span>
                <span class="text-xs font-semibold text-emerald-600">Target: {{ $kabupaten['target']?->target_apm_sd ?? 98.4 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Siswa Usia Tepat (7-12 Th):</span> <strong>{{ number_format($kabupaten['populasi']['usia_7_12'] * ($kabupaten['apm']['sd']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 7–12 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_7_12']) }}</strong></div>
            </div>
        </div>

        <!-- 13 - 15 Tahun (SMP/MTs) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APM 13 – 15 TAHUN (SMP/MTS)</span>
                @if($gender)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                        {{ $gender === 'L' ? '♂ L' : '♀ P' }}
                    </span>
                @endif
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['apm']['smp'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_apm_smp ?? 86.8 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Siswa Usia Tepat (13-15 Th):</span> <strong>{{ number_format($kabupaten['populasi']['usia_13_15'] * ($kabupaten['apm']['smp']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 13–15 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_13_15']) }}</strong></div>
            </div>
        </div>

        <!-- 16 - 18 Tahun (SMA/SMK) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APM 16 – 18 TAHUN (SMA/SMK)</span>
                @if($gender)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                        {{ $gender === 'L' ? '♂ L' : '♀ P' }}
                    </span>
                @endif
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['apm']['sma'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_apm_sma ?? 72.5 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Siswa Usia Tepat (16-18 Th):</span> <strong>{{ number_format($kabupaten['populasi']['usia_16_18'] * ($kabupaten['apm']['sma']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 16–18 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_16_18']) }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Interactive Visual Analytics (Charts Section) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart 1: Perbandingan Capaian APM per Jenjang vs Target Renstra (Bar Chart) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Perbandingan APM Riil vs Target Renstra</h3>
                    <p class="text-xs text-slate-500">Persentase partisipasi murni usia tepat per jenjang pendidikan Kabupaten Banjarnegara</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-medium">
                    <span class="inline-flex items-center gap-1.5 text-indigo-600 font-bold">
                        <span class="w-3 h-3 rounded bg-indigo-600 inline-block"></span> Capaian Riil
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-amber-500 font-bold">
                        <span class="w-3 h-3 rounded bg-amber-400 inline-block"></span> Target Renstra
                    </span>
                </div>
            </div>
            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="apmJenjangChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Komparasi Gender APM Tiap Jenjang (L vs P) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Komparasi APM Gender</h3>
                    <p class="text-xs text-slate-500">Laki-laki (♂) vs Perempuan (♀)</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Paritas
                </span>
            </div>
            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="apmGenderChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 3: Distribusi Sebaran APM SD & SMP 20 Kecamatan -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Sebaran APM SD & SMP di 20 Kecamatan Banjarnegara</h3>
                <p class="text-xs text-slate-500">Visualisasi komparasi capaian partisipasi murni usia sekolah dasar per kecamatan</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-medium">
                <span class="inline-flex items-center gap-1.5 text-indigo-600 font-bold">
                    <span class="w-3 h-3 rounded bg-indigo-600 inline-block"></span> APM SD/MI
                </span>
                <span class="inline-flex items-center gap-1.5 text-purple-600 font-bold">
                    <span class="w-3 h-3 rounded bg-purple-500 inline-block"></span> APM SMP/MTs
                </span>
            </div>
        </div>
        <div class="relative h-72 sm:h-80 w-full">
            <canvas id="apmKecamatanChart"></canvas>
        </div>
    </div>

    <!-- Analisis Paritas Gender APM (Gender Parity Index / GPI) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Analisis Paritas Gender APM (Gender Parity Index / GPI)</h3>
                <p class="text-xs text-slate-500">Perbandingan Partisipasi Murni Laki-laki vs Perempuan dan Rasio Paritas Gender (Target Ideal GPI = 0.97 - 1.03)</p>
            </div>
            <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 self-start sm:self-auto">
                Tahun {{ $tahun }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px] block">PAUD (5-6 Th)</span>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Laki-laki:</span>
                    <strong class="text-indigo-700 font-bold">{{ number_format($kabupaten['gender_comparison']['apm_paud_l'] ?? 0, 1) }}%</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Perempuan:</span>
                    <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['apm_paud_p'] ?? 0, 1) }}%</strong>
                </div>
                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                    <span class="font-semibold text-slate-600">GPI (P/L):</span>
                    <span class="px-2 py-0.5 rounded font-extrabold text-[11px] bg-white border border-slate-200 text-slate-800">
                        {{ number_format($kabupaten['gender_comparison']['gpi_apm_paud'] ?? 1, 2) }}
                    </span>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-2">
                <span class="font-bold text-indigo-900 uppercase tracking-wider text-[11px] block">SD/MI (7-12 Th)</span>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Laki-laki:</span>
                    <strong class="text-indigo-700 font-bold">{{ number_format($kabupaten['gender_comparison']['apm_sd_l'] ?? 0, 1) }}%</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Perempuan:</span>
                    <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['apm_sd_p'] ?? 0, 1) }}%</strong>
                </div>
                <div class="pt-2 border-t border-indigo-200/70 flex items-center justify-between">
                    <span class="font-semibold text-slate-700">GPI (P/L):</span>
                    <span class="px-2 py-0.5 rounded font-extrabold text-[11px] bg-white border border-indigo-200 text-indigo-900">
                        {{ number_format($kabupaten['gender_comparison']['gpi_apm_sd'] ?? 1, 2) }}
                    </span>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-purple-50/50 border border-purple-100 space-y-2">
                <span class="font-bold text-purple-900 uppercase tracking-wider text-[11px] block">SMP/MTs (13-15 Th)</span>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Laki-laki:</span>
                    <strong class="text-indigo-700 font-bold">{{ number_format($kabupaten['gender_comparison']['apm_smp_l'] ?? 0, 1) }}%</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-600">Perempuan:</span>
                    <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['apm_smp_p'] ?? 0, 1) }}%</strong>
                </div>
                <div class="pt-2 border-t border-purple-200/70 flex items-center justify-between">
                    <span class="font-semibold text-slate-700">GPI (P/L):</span>
                    <span class="px-2 py-0.5 rounded font-extrabold text-[11px] bg-white border border-purple-200 text-purple-900">
                        {{ number_format($kabupaten['gender_comparison']['gpi_apm_smp'] ?? 1, 2) }}
                    </span>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px] block">SMA/SMK (16-18 Th)</span>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Laki-laki:</span>
                    <strong class="text-indigo-700 font-bold">{{ number_format($kabupaten['gender_comparison']['apm_sma_l'] ?? 0, 1) }}%</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Perempuan:</span>
                    <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['apm_sma_p'] ?? 0, 1) }}%</strong>
                </div>
                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                    <span class="font-semibold text-slate-600">GPI (P/L):</span>
                    <span class="px-2 py-0.5 rounded font-extrabold text-[11px] bg-white border border-slate-200 text-slate-800">
                        {{ number_format($kabupaten['gender_comparison']['gpi_apm_sma'] ?? 1, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Dual View Switcher (Basic Wilayah vs Basic Sekolah) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-2 p-1 rounded-2xl bg-slate-100 max-w-max">
                <a href="{{ route('indikator.apm', array_merge(request()->query(), ['tab' => 'wilayah'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'wilayah' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Basic Wilayah (20 Kecamatan)
                </a>
                <a href="{{ route('indikator.apm', array_merge(request()->query(), ['tab' => 'sekolah'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'sekolah' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Basic Sekolah (Satuan Pendidikan)
                </a>
            </div>

            @if($tab === 'sekolah')
            <div class="flex flex-wrap items-center gap-2.5">
                <form method="GET" class="flex flex-wrap items-center gap-2.5 text-xs">
                    <input type="hidden" name="tab" value="sekolah">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    
                    <select name="kecamatan_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 outline-none">
                        <option value="">Semua Kecamatan</option>
                        @foreach($allKecamatan as $k)
                            <option value="{{ $k->id }}" {{ $kecamatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>

                    <select name="jenjang" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 outline-none">
                        <option value="">Semua Jenjang</option>
                        <option value="PAUD" {{ $jenjang == 'PAUD' ? 'selected' : '' }}>PAUD/TK</option>
                        <option value="SD" {{ $jenjang == 'SD' ? 'selected' : '' }}>SD</option>
                        <option value="SMP" {{ $jenjang == 'SMP' ? 'selected' : '' }}>SMP</option>
                        <option value="SMA" {{ $jenjang == 'SMA' ? 'selected' : '' }}>SMA</option>
                        <option value="SMK" {{ $jenjang == 'SMK' ? 'selected' : '' }}>SMK</option>
                    </select>
                </form>

                <a href="{{ route('sekolah.index') }}" class="px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition flex items-center gap-1.5 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Kelola Data Sekolah</span>
                </a>
            </div>
            @endif
        </div>

        @if($tab === 'wilayah')
        <!-- Tabel Basic Wilayah -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Nama Kecamatan</th>
                        <th class="py-3.5 px-4 text-center">APM 5–6 Th {{ $gender ? '('.$gender.')' : '(PAUD)' }}</th>
                        <th class="py-3.5 px-4 text-center">APM 7–12 Th {{ $gender ? '('.$gender.')' : '(SD)' }}</th>
                        <th class="py-3.5 px-4 text-center">APM 13–15 Th {{ $gender ? '('.$gender.')' : '(SMP)' }}</th>
                        <th class="py-3.5 px-4 text-center">APM 16–18 Th {{ $gender ? '('.$gender.')' : '(SMA)' }}</th>
                        <th class="py-3.5 px-4 text-right">Penduduk 7–12 Th</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kecamatanList as $kec)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-mono text-slate-400">{{ $kec['kode'] }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $kec['nama'] }}</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['apm']['paud'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center font-bold text-indigo-600">{{ number_format($kec['apm']['sd'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['apm']['smp'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['apm']['sma'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-right font-medium text-slate-700">
                            {{ number_format($kec['populasi']['usia_7_12']) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <!-- Tabel Basic Sekolah -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">NPSN</th>
                        <th class="py-3.5 px-4">Nama Satuan Pendidikan</th>
                        <th class="py-3.5 px-4">Jenjang</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4 text-center">Akreditasi</th>
                        <th class="py-3.5 px-4 text-right">Siswa Usia Sesuai</th>
                        <th class="py-3.5 px-4 text-right">Total Siswa</th>
                        <th class="py-3.5 px-4 text-center">% Ketepatan Usia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sekolahList as $sch)
                    @php 
                        $p = $sch->partisipasiSiswa->first(); 
                        $rasio = ($p && $p->total_siswa_semua_usia > 0) ? round(($p->siswa_usia_sesuai / $p->total_siswa_semua_usia) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-mono text-slate-400">{{ $sch->npsn }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $sch->nama_sekolah }}</td>
                        <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded font-bold bg-slate-100 text-slate-700 text-[11px]">{{ $sch->jenjang }}</span></td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $sch->kecamatan->nama }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $sch->akreditasi === 'A' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                {{ $sch->akreditasi }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-indigo-700">{{ number_format($p?->siswa_usia_sesuai ?? 0) }}</td>
                        <td class="py-3.5 px-4 text-right font-medium text-slate-600">{{ number_format($p?->total_siswa_semua_usia ?? 0) }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ $rasio }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-6 text-slate-400">Tidak ada data satuan pendidikan ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    <!-- POPUP MODAL: INFO RUMUS & ALUR SUMBER DATA APM -->
    <div x-show="infoModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="infoModal = false">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto"
             @click.away="infoModal = false">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-slate-100 pb-3 gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-indigo-50 text-indigo-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-indigo-600 text-white tracking-wider">Formula Flowchart</span>
                            <h3 class="text-base font-bold text-slate-900">Info Rumus & Alur Sumber Data APM</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Penjelasan alur kalkulasi Angka Partisipasi Murni dan integrasi antar menu sistem SIMAK</p>
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
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        Persamaan Matematis Kemendikbud:
                    </span>
                    <p class="p-3 bg-white font-mono rounded-xl border border-slate-200 text-slate-800 leading-relaxed shadow-2xs">
                        APM = (<span class="text-indigo-700 font-bold">Data A: Siswa Usia Resmi di Jenjangnya</span> / <span class="text-purple-700 font-bold">Data B: Penduduk Usia Jenjang Tertentu</span>) &times; 100%
                    </p>
                    <p class="text-[11px] text-slate-500 italic">
                        *Contoh: APM SD/MI = (Jumlah siswa SD & MI yang benar-benar berusia 7–12 tahun / Jumlah total penduduk usia 7–12 tahun di Banjarnegara) &times; 100%
                    </p>
                </div>

                <!-- Sumber Data Pembilang & Penyebut (Masing-masing 1 Baris Penuh) -->
                <div class="space-y-3">
                    <!-- Baris 1: Data A (Pembilang) -->
                    <div class="bg-indigo-50/50 rounded-2xl p-4 border border-indigo-200/80 space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-indigo-100 pb-2">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-indigo-600 text-white shadow-2xs">
                                    Data A (Pembilang / Numerator)
                                </span>
                                <span class="text-xs font-bold text-slate-900">Jumlah Siswa Usia Resmi di Jenjangnya</span>
                            </div>
                            <a href="{{ route('siswa.index') }}" 
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] border border-indigo-200 shadow-2xs transition shrink-0">
                                <span>Buka Menu Siswa (Dapodik)</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            <span class="font-semibold text-slate-800">Sumber:</span> Menu 📁 <strong class="text-indigo-900">Master Data &rarr; Partisipasi Siswa (Dapodik)</strong> (kolom <strong>Siswa Usia Sesuai</strong>). Hanya menghitung peserta didik yang umurnya tepat sesuai usia standar jenjang (misal siswa SD/MI yang berumur tepat 7–12 tahun; siswa di luar usia ini tidak dihitung).
                        </p>
                    </div>

                    <!-- Baris 2: Data B (Penyebut) -->
                    <div class="bg-purple-50/50 rounded-2xl p-4 border border-purple-200/80 space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-purple-100 pb-2">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-purple-600 text-white shadow-2xs">
                                    Data B (Penyebut / Denominator)
                                </span>
                                <span class="text-xs font-bold text-slate-900">Jumlah Penduduk Usia Sekolah Jenjang Tersebut</span>
                            </div>
                            <a href="{{ route('tujuan1.index') }}" 
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white hover:bg-purple-100 text-purple-700 font-bold text-[11px] border border-purple-200 shadow-2xs transition shrink-0">
                                <span>Buka Menu Penduduk</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            <span class="font-semibold text-slate-800">Sumber:</span> Menu 📁 <strong class="text-purple-900">Master Data &rarr; Tujuan 1: Penduduk Usia Sekolah</strong>. Total data agregat kependudukan Dukcapil Kabupaten Banjarnegara per rentang usia resmi jenjang: <strong>5–6 th</strong> (PAUD), <strong>7–12 th</strong> (SD/MI), <strong>13–15 th</strong> (SMP/MTs), dan <strong>16–18 th</strong> (SMA/SMK).
                        </p>
                    </div>
                </div>

                <!-- Alur Penilaian & Target Renstra -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-[11px] text-slate-700 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shrink-0">⚡</span>
                        <strong class="text-slate-900 font-bold">Alur Penilaian & Komparasi Kinerja:</strong>
                    </div>
                    <p class="text-slate-600 leading-relaxed pl-7">
                        1. Sistem menghitung persentase capaian riil dengan rumus <code>(Data A &divide; Data B) &times; 100%</code>.<br>
                        2. Capaian riil dikomparasikan terhadap target sasaran pada menu 
                        <a href="{{ route('renstra.target') }}" class="font-bold text-indigo-700 hover:underline">Target Renstra (2025–2029)</a> untuk mengukur capaian dinas.<br>
                        3. <em>Catatan Khusus APM:</em> Nilai capaian <strong>maksimal bernilai 100%</strong>, mengukur ketepatan usia peserta didik tanpa anak terlambat masuk atau tinggal kelas.
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
        // 1. Chart APM Riil vs Target
        const ctxJenjang = document.getElementById('apmJenjangChart')?.getContext('2d');
        if (ctxJenjang) {
            new Chart(ctxJenjang, {
                type: 'bar',
                data: {
                    labels: ['PAUD (5-6 Th)', 'SD/MI (7-12 Th)', 'SMP/MTs (13-15 Th)', 'SMA/SMK (16-18 Th)'],
                    datasets: [
                        {
                            label: 'Capaian Riil (%)',
                            data: [
                                {{ (float) ($kabupaten['apm']['paud'] ?? 0) }},
                                {{ (float) ($kabupaten['apm']['sd'] ?? 0) }},
                                {{ (float) ($kabupaten['apm']['smp'] ?? 0) }},
                                {{ (float) ($kabupaten['apm']['sma'] ?? 0) }}
                            ],
                            backgroundColor: 'rgba(79, 70, 229, 0.85)',
                            borderColor: 'rgb(79, 70, 229)',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            barPercentage: 0.6,
                            categoryPercentage: 0.6
                        },
                        {
                            label: 'Target Renstra (%)',
                            data: [
                                {{ (float) ($kabupaten['target']?->target_apm_paud ?? 45.0) }},
                                {{ (float) ($kabupaten['target']?->target_apm_sd ?? 99.2) }},
                                {{ (float) ($kabupaten['target']?->target_apm_smp ?? 92.5) }},
                                {{ (float) ($kabupaten['target']?->target_apm_sma ?? 72.5) }}
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

        // 2. Chart APM Gender (L vs P)
        const ctxGender = document.getElementById('apmGenderChart')?.getContext('2d');
        if (ctxGender) {
            new Chart(ctxGender, {
                type: 'bar',
                data: {
                    labels: ['PAUD', 'SD/MI', 'SMP/MTs', 'SMA/SMK'],
                    datasets: [
                        {
                            label: 'Laki-laki (%)',
                            data: [
                                {{ (float) ($kabupaten['gender_comparison']['apm_paud_l'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['apm_sd_l'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['apm_smp_l'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['apm_sma_l'] ?? 0) }}
                            ],
                            backgroundColor: 'rgba(79, 70, 229, 0.85)',
                            borderRadius: 4
                        },
                        {
                            label: 'Perempuan (%)',
                            data: [
                                {{ (float) ($kabupaten['gender_comparison']['apm_paud_p'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['apm_sd_p'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['apm_smp_p'] ?? 0) }},
                                {{ (float) ($kabupaten['gender_comparison']['apm_sma_p'] ?? 0) }}
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

        // 3. Chart Sebaran APM 20 Kecamatan
        const ctxKec = document.getElementById('apmKecamatanChart')?.getContext('2d');
        if (ctxKec) {
            const kecLabels = {!! json_encode($kecamatanList->pluck('nama')) !!};
            const apmSdData = {!! json_encode($kecamatanList->pluck('apm.sd')) !!};
            const apmSmpData = {!! json_encode($kecamatanList->pluck('apm.smp')) !!};

            new Chart(ctxKec, {
                type: 'bar',
                data: {
                    labels: kecLabels,
                    datasets: [
                        {
                            label: 'APM SD/MI (%)',
                            data: apmSdData,
                            backgroundColor: 'rgba(79, 70, 229, 0.85)',
                            borderRadius: 5
                        },
                        {
                            label: 'APM SMP/MTs (%)',
                            data: apmSmpData,
                            backgroundColor: 'rgba(168, 85, 247, 0.85)',
                            borderRadius: 5
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
        }
    });
</script>
@endpush
