@extends('layouts.app', ['title' => 'Angka Partisipasi Sekolah (APS)'])

@section('content')
<div class="space-y-8">

    <!-- Formula Reference Banner (From Flowchart.pdf) -->
    <div class="p-6 rounded-3xl bg-emerald-50/70 border border-emerald-200 text-emerald-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-sm">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-600 text-white tracking-wider">Formula Flowchart</span>
                <h2 class="text-base font-bold text-slate-900">Rumus Angka Partisipasi Sekolah (APS)</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-mono bg-white/80 p-2.5 rounded-xl border border-emerald-100">
                APS = (Jumlah anak usia jenjang tertentu yang bersekolah di semua jenjang pendidikan / Jumlah anak usia jenjang tertentu di kabupaten/kota) &times; 100
            </p>
            <p class="text-[11px] text-slate-500 italic">
                *Contoh: APS SD/MI = (Jumlah anak usia 7 - 12 tahun yang bersekolah di semua jenjang pendidikan / Jumlah anak usia SD&MI (7-12 Tahun) di Kabupaten Banjarnegara) &times; 100
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('export.kecamatan', ['tahun' => $tahun]) }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-emerald-700 border border-emerald-200 font-bold text-xs shadow-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- 4 Age Breakdown Cards (5-6, 7-12, 13-15, 16-18) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 5 - 6 Tahun (PAUD) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APS 5 – 6 TAHUN</span>
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
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APS 7 – 12 TAHUN</span>
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
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APS 13 – 15 TAHUN</span>
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
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APS 16 – 18 TAHUN</span>
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
                        <th class="py-3.5 px-4 text-center">APS 5–6 Th</th>
                        <th class="py-3.5 px-4 text-center">APS 7–12 Th</th>
                        <th class="py-3.5 px-4 text-center">APS 13–15 Th</th>
                        <th class="py-3.5 px-4 text-center">APS 16–18 Th</th>
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
