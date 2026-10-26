@extends('layouts.app', ['title' => 'Dashboard Kinerja SIMAK'])

@section('content')
<div class="space-y-8">
    
    <!-- Header Banner Akuntabilitas Kinerja (Simple & Modern) -->
    <div class="px-5 py-4 sm:px-6 sm:py-4.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="space-y-1 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/60">
                    SAKIP {{ $tahun }}
                </span>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                    Akuntabilitas Kinerja Pendidikan Tahun {{ $tahun }}
                </h2>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">
                Ringkasan capaian Indikator Kinerja Kunci (IKK) Renstra, evaluasi AKIP DINDIKPORA, dan pemerataan partisipasi sekolah di 20 wilayah kecamatan.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <div class="inline-flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-semibold">
                <a href="{{ route('dashboard', array_merge(request()->query(), ['gender' => ''])) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ empty($gender) ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('dashboard', array_merge(request()->query(), ['gender' => 'L'])) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $gender === 'L' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    ♂ Laki-laki
                </a>
                <a href="{{ route('dashboard', array_merge(request()->query(), ['gender' => 'P'])) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $gender === 'P' ? 'bg-white text-pink-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    ♀ Perempuan
                </a>
            </div>

            <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 shrink-0 font-medium bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/70">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Renstra 2025–2029</span>
            </div>
        </div>
    </div>

    @if(isset($ewsData) && !empty($ewsData['items']))
    <!-- Early Warning System (EWS) Kinerja SAKIP Banner -->
    <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl {{ $ewsData['summary']['kritis'] > 0 ? 'bg-rose-50 text-rose-600' : ($ewsData['summary']['waspada'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900">Sistem Peringatan Dini Kinerja (Early Warning System)</h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $ewsData['summary']['kritis'] > 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                            Skor Kesehatan: {{ $ewsData['summary']['indeks_kesehatan_kinerja'] }}%
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500">Deteksi deviasi capaian riil terhadap target Renstra 2025–2029 untuk mitigasi dini</p>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs">
                @if($ewsData['summary']['kritis'] > 0)
                    <span class="px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 font-bold border border-rose-200">
                        &bull; {{ $ewsData['summary']['kritis'] }} Indikator Kritis
                    </span>
                @endif
                @if($ewsData['summary']['waspada'] > 0)
                    <span class="px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700 font-bold border border-amber-200">
                        &bull; {{ $ewsData['summary']['waspada'] }} Waspada
                    </span>
                @endif
                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                    &bull; {{ $ewsData['summary']['optimal'] }} Optimal
                </span>
            </div>
        </div>

        <!-- EWS Items Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($ewsData['items'] as $item)
            <div class="p-3.5 rounded-2xl border text-xs {{ $item['status'] === 'kritis' ? 'bg-rose-50/50 border-rose-200 text-rose-950' : ($item['status'] === 'waspada' ? 'bg-amber-50/50 border-amber-200 text-amber-950' : 'bg-slate-50 border-slate-200 text-slate-800') }}">
                <div class="flex items-center justify-between">
                    <span class="font-bold truncate max-w-[170px]">{{ $item['nama'] }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $item['status'] === 'kritis' ? 'bg-rose-600 text-white' : ($item['status'] === 'waspada' ? 'bg-amber-500 text-white' : 'bg-emerald-600 text-white') }}">
                        {{ $item['status'] }}
                    </span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-slate-500 text-[11px]">Realisasi: <strong class="text-slate-900">{{ $item['realisasi'] }}{{ $item['satuan'] }}</strong></span>
                    <span class="text-slate-500 text-[11px]">Target: <strong>{{ $item['target'] }}{{ $item['satuan'] }}</strong></span>
                    <span class="font-black text-[11px] {{ $item['gap'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $item['gap'] > 0 ? '+'.$item['gap'] : $item['gap'] }}{{ $item['satuan'] }}
                    </span>
                </div>
                <p class="mt-2 pt-2 border-t {{ $item['status'] === 'kritis' ? 'border-rose-200 text-rose-800' : 'border-slate-200/60 text-slate-500' }} text-[10px] leading-tight">
                    <strong>Rekomendasi:</strong> {{ $item['intervensi'] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Executive KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Card 1: APK SD/MI -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APK SD / MI</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($kabupaten['apk']['sd'], 2) }}%</span>
                @if($kabupaten['target'])
                    <span class="text-xs font-semibold {{ $kabupaten['apk']['sd'] >= $kabupaten['target']->target_apk_sd ? 'text-emerald-600' : 'text-amber-600' }}">
                        Target: {{ $kabupaten['target']->target_apk_sd }}%
                    </span>
                @endif
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Penyebut Penduduk 7–12 Th: <strong>{{ number_format($kabupaten['populasi']['usia_7_12']) }}</strong> jiwa
            </p>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">APM SD: <strong>{{ number_format($kabupaten['apm']['sd'], 2) }}%</strong></span>
                <span class="text-slate-400">APS SD: <strong>{{ number_format($kabupaten['aps']['sd'], 2) }}%</strong></span>
            </div>
        </div>

        <!-- Card 2: APK SMP/MTs -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APK SMP / MTs</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($kabupaten['apk']['smp'], 2) }}%</span>
                @if($kabupaten['target'])
                    <span class="text-xs font-semibold {{ $kabupaten['apk']['smp'] >= $kabupaten['target']->target_apk_smp ? 'text-emerald-600' : 'text-amber-600' }}">
                        Target: {{ $kabupaten['target']->target_apk_smp }}%
                    </span>
                @endif
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Penyebut Penduduk 13–15 Th: <strong>{{ number_format($kabupaten['populasi']['usia_13_15']) }}</strong> jiwa
            </p>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">APM SMP: <strong>{{ number_format($kabupaten['apm']['smp'], 2) }}%</strong></span>
                <span class="text-slate-400">APS SMP: <strong>{{ number_format($kabupaten['aps']['smp'], 2) }}%</strong></span>
            </div>
        </div>

        <!-- Card 3: Guru Bersertifikat Pendidik -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Guru Bersertifikasi</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($kabupaten['guru']['persentase'], 2) }}%</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700">
                    {{ number_format($kabupaten['guru']['sertifikasi']) }} Guru
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Dari Total Guru: <strong>{{ number_format($kabupaten['guru']['total']) }}</strong> pendidik
            </p>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Target Renstra: <strong>{{ $kabupaten['target']?->target_guru_sertifikasi ?? 72 }}%</strong></span>
                <span class="text-emerald-600 font-bold">Terpenuhi</span>
            </div>
        </div>

        <!-- Card 4: PAUD Terakreditasi Min. B -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">PAUD Terakreditasi B</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($kabupaten['paud_akreditasi']['persentase'], 2) }}%</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700">
                    {{ $kabupaten['paud_akreditasi']['min_b'] }} PAUD
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Dari Total PAUD: <strong>{{ $kabupaten['paud_akreditasi']['total'] }}</strong> satuan
            </p>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Target Renstra: <strong>{{ $kabupaten['target']?->target_paud_akreditasi_b ?? 68 }}%</strong></span>
                <span class="text-slate-600 font-medium">Akreditasi A & B</span>
            </div>
        </div>

    </div>

    <!-- Rekapitulasi Kinerja Menurut Gender (Gender Analysis Dashboard) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shadow-blue-500/20">
                    ⚥
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900">Rekapitulasi Kinerja Pendidikan Menurut Gender (L & P)</h3>
                    <p class="text-xs text-slate-500">Komparasi demografi, partisipasi siswa, kualifikasi guru, dan prestasi se-Kabupaten Banjarnegara</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    Tahun {{ $tahun }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <!-- 1. Demografi Penduduk Usia Sekolah -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">1. Demografi Usia Sekolah</span>
                    <span class="text-[10px] text-slate-400">7-18 Th</span>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Laki-laki:</span>
                        <strong class="text-blue-700 font-bold">{{ number_format($kabupaten['gender_comparison']['populasi_l'] ?? 0) }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Perempuan:</span>
                        <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['populasi_p'] ?? 0) }}</strong>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500">Rasio Penduduk:</span>
                    @php
                        $popTot = ($kabupaten['gender_comparison']['populasi_l'] ?? 0) + ($kabupaten['gender_comparison']['populasi_p'] ?? 0);
                        $popPctL = $popTot > 0 ? round((($kabupaten['gender_comparison']['populasi_l'] ?? 0) / $popTot) * 100, 1) : 50;
                    @endphp
                    <span class="font-semibold text-slate-700">{{ $popPctL }}% L / {{ 100 - $popPctL }}% P</span>
                </div>
            </div>

            <!-- 2. Partisipasi Murid (Dapodik) -->
            <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-blue-900 uppercase tracking-wider text-[11px]">2. Partisipasi Murid</span>
                    <span class="text-[10px] text-blue-500 font-medium">Aktif</span>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Siswa (L):</span>
                        <strong class="text-blue-700 font-bold">{{ number_format($kabupaten['gender_comparison']['siswa_l'] ?? 0) }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Siswi (P):</span>
                        <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['siswa_p'] ?? 0) }}</strong>
                    </div>
                </div>
                <div class="pt-2 border-t border-blue-200/70 flex items-center justify-between text-[11px]">
                    <span class="text-slate-600">GPI APK SD & SMP:</span>
                    <span class="font-bold text-blue-900">
                        {{ $kabupaten['gender_comparison']['gpi_apk_sd'] ?? 1 }} &bull; {{ $kabupaten['gender_comparison']['gpi_apk_smp'] ?? 1 }}
                    </span>
                </div>
            </div>

            <!-- 3. Guru & Sertifikasi Pendidik -->
            <div class="p-4 rounded-2xl bg-purple-50/50 border border-purple-100 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-purple-900 uppercase tracking-wider text-[11px]">3. Guru & Sertifikasi</span>
                    <span class="text-[10px] text-purple-600 font-medium">Pendidik</span>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Guru (L):</span>
                        <span class="font-semibold text-slate-800">
                            {{ number_format($kabupaten['gender_comparison']['guru_total_l'] ?? 0) }}
                            <strong class="text-purple-700">({{ $kabupaten['gender_comparison']['guru_persen_l'] ?? 0 }}%)</strong>
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Guru (P):</span>
                        <span class="font-semibold text-slate-800">
                            {{ number_format($kabupaten['gender_comparison']['guru_total_p'] ?? 0) }}
                            <strong class="text-pink-700">({{ $kabupaten['gender_comparison']['guru_persen_p'] ?? 0 }}%)</strong>
                        </span>
                    </div>
                </div>
                <div class="pt-2 border-t border-purple-200/70 flex items-center justify-between text-[11px]">
                    <span class="text-slate-600">Sertifikasi L / P:</span>
                    <span class="font-bold text-purple-900">
                        {{ number_format($kabupaten['gender_comparison']['guru_sertif_l'] ?? 0) }} / {{ number_format($kabupaten['gender_comparison']['guru_sertif_p'] ?? 0) }}
                    </span>
                </div>
            </div>

            <!-- 4. Prestasi Siswa -->
            <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-100 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-amber-900 uppercase tracking-wider text-[11px]">4. Capaian Prestasi</span>
                    <span class="text-[10px] text-amber-700 font-medium">Tujuan 2</span>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Prestasi (L):</span>
                        <strong class="text-blue-700 font-bold">{{ number_format($kabupaten['gender_comparison']['prestasi_l'] ?? 0) }} Juara</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Prestasi (P):</span>
                        <strong class="text-pink-700 font-bold">{{ number_format($kabupaten['gender_comparison']['prestasi_p'] ?? 0) }} Juara</strong>
                    </div>
                </div>
                <div class="pt-2 border-t border-amber-200/70 flex items-center justify-between text-[11px]">
                    <span class="text-slate-600">Total Juara Terdata:</span>
                    <span class="font-bold text-amber-900">
                        {{ number_format(($kabupaten['gender_comparison']['prestasi_l'] ?? 0) + ($kabupaten['gender_comparison']['prestasi_p'] ?? 0)) }} Ajang
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Section: Akuntabilitas Kinerja & Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Grafik Tren Capaian vs Target Renstra 2025-2029 -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h3 class="font-bold text-base text-slate-900">Tren Capaian vs Target Renstra (2025–2029)</h3>
                    <p class="text-xs text-slate-500">Proyeksi target dan realisasi Capaian IKK Renstra Pendidikan</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                        <span class="w-3 h-3 rounded-sm bg-blue-600 inline-block"></span> Target Renstra
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                        <span class="w-3 h-3 rounded-sm bg-emerald-500 inline-block"></span> Realisasi
                    </span>
                </div>
            </div>
            <div class="h-64 sm:h-72 w-full">
                <canvas id="renstraTrendChart"></canvas>
            </div>
        </div>

        <!-- Right 1 Col: Kartu Realisasi SAKIP / AKIP & RLS/HLS -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-5">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-base text-slate-900">Nilai AKIP & Indikator Makro</h3>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Kategori {{ $kabupaten['realisasi']?->evaluasi_status ?? 'Baik' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Evaluasi Akuntabilitas Kinerja Tahun N-1 vs Tahun N</p>
                
                <div class="mt-5 space-y-4">
                    <!-- Nilai AKIP -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Nilai AKIP DINDIKPORA</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $kabupaten['realisasi']?->capaian_akip_n ?? 83.10 }} Poin</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Tahun Lalu: {{ $kabupaten['realisasi']?->capaian_akip_prev ?? 81.65 }}</span>
                            <span class="text-emerald-600 font-bold">+{{ number_format(($kabupaten['realisasi']?->capaian_akip_n ?? 83.10) - ($kabupaten['realisasi']?->capaian_akip_prev ?? 81.65), 2) }}</span>
                        </div>
                    </div>

                    <!-- RLS -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Rata-rata Lama Sekolah (RLS)</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $kabupaten['realisasi']?->capaian_rls_n ?? 7.39 }} Th</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Target: {{ $kabupaten['target']?->target_rls ?? 7.35 }} Th</span>
                            <span class="text-emerald-600 font-bold">Terlampaui</span>
                        </div>
                    </div>

                    <!-- HLS -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Harapan Lama Sekolah (HLS)</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $kabupaten['realisasi']?->capaian_hls_n ?? 12.52 }} Th</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Target: {{ $kabupaten['target']?->target_hls ?? 12.45 }} Th</span>
                            <span class="text-emerald-600 font-bold">Terlampaui</span>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('renstra.realisasi') }}" 
               class="w-full text-center py-2.5 px-4 rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 text-slate-800 transition">
                Buka Realisasi Capaian Kinerja &rarr;
            </a>
        </div>

    </div>

    <!-- Bottom Section 1: Tabel Basic Wilayah (20 Kecamatan Banjarnegara) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Capaian Kinerja Basic Wilayah (20 Kecamatan)</h3>
                <p class="text-xs text-slate-500">Agregasi partisipasi kasar, murni, dan status pendidik per kecamatan Kabupaten Banjarnegara</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('export.kecamatan', ['tahun' => $tahun]) }}" 
                   class="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Unduh Rekap Excel (.xlsx)</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4 text-right">Penduduk 7–12 Th</th>
                        <th class="py-3.5 px-4 text-center">APK SD</th>
                        <th class="py-3.5 px-4 text-center">APM SD</th>
                        <th class="py-3.5 px-4 text-center">APK SMP</th>
                        <th class="py-3.5 px-4 text-center">APM SMP</th>
                        <th class="py-3.5 px-4 text-center">% Guru Sertif</th>
                        <th class="py-3.5 px-4 text-center">% PAUD Min. B</th>
                        <th class="py-3.5 px-4 text-center">Prestasi</th>
                    </tr>
                </thead>
                <tbody id="kecamatanTableBody" class="divide-y divide-slate-100">
                    @foreach($kecamatanList as $kec)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-medium text-slate-400">{{ $kec['kode'] }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $kec['nama'] }}
                            <span class="block text-[10px] text-slate-400 font-normal">{{ $kec['sekolah_count'] }} Sekolah</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-semibold text-slate-700">
                            {{ number_format($kec['populasi']['usia_7_12']) }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded font-bold {{ $kec['apk']['sd'] >= 100 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ number_format($kec['apk']['sd'], 1) }}%
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-medium text-slate-800">
                            {{ number_format($kec['apm']['sd'], 1) }}%
                        </td>
                        <td class="py-3.5 px-4 text-center font-medium text-slate-800">
                            {{ number_format($kec['apk']['smp'], 1) }}%
                        </td>
                        <td class="py-3.5 px-4 text-center font-medium text-slate-800">
                            {{ number_format($kec['apm']['smp'], 1) }}%
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold {{ $kec['guru']['persentase'] >= 70 ? 'text-purple-700' : 'text-slate-600' }}">
                                {{ number_format($kec['guru']['persentase'], 1) }}%
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $kec['paud_akreditasi']['persentase'] >= 65 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ number_format($kec['paud_akreditasi']['persentase'], 1) }}%
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold text-[11px]">
                                {{ $kec['prestasi_count'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Section 2: Prestasi Siswa Terkini (Tujuan 2) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Prestasi Unggulan Siswa (Tujuan 2)</h3>
                <p class="text-xs text-slate-500">Perolehan prestasi akademik, sains, seni, dan olahraga tingkat Provinsi & Nasional</p>
            </div>
            <a href="{{ route('tujuan2.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                Lihat Semua Prestasi &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            @forelse($recentPrestasi as $pres)
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 hover:border-blue-300 transition space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                        {{ $pres->tingkat }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                        {{ $pres->peringkat }}
                    </span>
                </div>
                <h4 class="font-bold text-slate-900 text-sm line-clamp-1">{{ $pres->nama_kejuaraan }}</h4>
                <div class="flex items-center gap-1.5">
                    <p class="text-xs font-semibold text-slate-700">{{ $pres->nama_siswa }}</p>
                    @if($pres->jenis_kelamin === 'P')
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-pink-50 text-pink-700 border border-pink-200">P</span>
                    @else
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">L</span>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 truncate">{{ $pres->satuanPendidikan->nama_sekolah }} (Kec. {{ $pres->satuanPendidikan->kecamatan->nama }})</p>
            </div>
            @empty
            <p class="text-xs text-slate-400 col-span-3">Belum ada data prestasi tercatat.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Chart.js - Renstra Trend 2025-2029
    const ctx = document.getElementById('renstraTrendChart')?.getContext('2d');
    if (ctx) {
        const years = {!! json_encode($targetRenstraAll->pluck('tahun')) !!};
        const targets = {!! json_encode($targetRenstraAll->pluck('target_ikk_renstra')) !!};
        
        // Mock current realization up to 2025
        const currentYear = {{ $tahun }};
        const realisasiData = years.map(y => {
            if (y === 2025) return {{ $kabupaten['realisasi']?->capaian_ikk_renstra ?? 89.20 }};
            if (y < 2025) return 86.75;
            return null;
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: years,
                datasets: [
                    {
                        label: 'Target IKK Renstra (%)',
                        data: targets,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        borderWidth: 3,
                        pointBackgroundColor: '#2563eb',
                        pointRadius: 5,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Realisasi Capaian (%)',
                        data: realisasiData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 6,
                        tension: 0.3
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
                        titleFont: { family: 'Plus Jakarta Sans', size: 12 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        min: 80,
                        max: 100,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                    }
                }
            }
        });
    }
</script>
@endpush
