{{-- 
    Tabel Indikator Resmi (Format Format File XLSX: APK.xlsx, APM.xlsx, APS.xlsx)
    Mencakup 4 Blok Jenjang: PAUD (usia 5-6), SD (usia 7-12), SMP (usia 13-15), dan SMA/SMK (usia 16-18)
--}}
<div class="space-y-4 w-full max-w-full overflow-hidden" x-data="{ viewMode: 'all' }">
    <!-- View Mode Switcher & Quick Information Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 p-3 sm:p-4 rounded-2xl border border-slate-200">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5 shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
                Tampilan Jenjang:
            </span>
            <div class="inline-flex items-center p-1 bg-white rounded-xl border border-slate-200 text-xs font-semibold shadow-2xs w-full sm:w-auto overflow-x-auto scrollbar-none">
                <button type="button" 
                        @click="viewMode = 'all'" 
                        :class="viewMode === 'all' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    Semua Jenjang (Format Lengkap XLSX)
                </button>
                <button type="button" 
                        @click="viewMode = 'paud'" 
                        :class="viewMode === 'paud' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    PAUD Saja
                </button>
                <button type="button" 
                        @click="viewMode = 'sd'" 
                        :class="viewMode === 'sd' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    SD / Sederajat Saja
                </button>
                <button type="button" 
                        @click="viewMode = 'smp'" 
                        :class="viewMode === 'smp' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    SMP / Sederajat Saja
                </button>
                <button type="button" 
                        @click="viewMode = 'sma'" 
                        :class="viewMode === 'sma' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    SMA / SMK / Sederajat Saja
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @php
                $tipeExport = strtolower($officialData['tipe']);
                $routeExport = in_array($tipeExport, ['apk', 'apm', 'aps']) ? route('export.'.$tipeExport, ['tahun' => $tahun]) : route('export.kecamatan', ['tahun' => $tahun]);
            @endphp
            <a href="{{ $routeExport }}" 
               class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs hover:shadow-xs transition w-full sm:w-auto">
                <svg class="w-4 h-4 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh File Excel {{ strtoupper($officialData['tipe']) }} (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- Responsive Scrollable Table Container -->
    <div class="w-full max-w-full overflow-x-auto rounded-2xl border border-slate-200/90 shadow-2xs bg-white scrollbar-thin">
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                {{-- Baris 1: Banner Header Tiap Jenjang (Hanya saat Semua Jenjang Aktif) --}}
                <tr x-show="viewMode === 'all'" class="text-xs font-extrabold text-center tracking-wide border-b border-slate-200">
                    <th colspan="11" class="py-2.5 px-3 bg-blue-100/80 text-blue-900 border-r border-blue-200">
                        {{ $officialData['labels']['paud_title'] }}
                    </th>
                    <th class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></th>
                    <th colspan="11" class="py-2.5 px-3 bg-indigo-100/80 text-indigo-900 border-r border-indigo-200">
                        {{ $officialData['labels']['sd_title'] }}
                    </th>
                    <th class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></th>
                    <th colspan="11" class="py-2.5 px-3 bg-sky-100/80 text-sky-900 border-r border-sky-200">
                        {{ $officialData['labels']['smp_title'] }}
                    </th>
                    <th class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></th>
                    <th colspan="11" class="py-2.5 px-3 bg-emerald-100/80 text-emerald-900">
                        {{ $officialData['labels']['sma_title'] }}
                    </th>
                </tr>

                {{-- Baris 2: Header Kolom Utama --}}
                <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px] border-b border-slate-200">
                    {{-- Blok PAUD --}}
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th rowspan="2" class="py-3 px-2 text-center border-r border-slate-200 w-10">No</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th rowspan="2" class="py-3 px-3 border-r border-slate-200 min-w-[140px]">Kecamatan</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['paud_penduduk'] }}</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['paud_siswa'] }}</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200 bg-blue-50/50 text-blue-900">{{ $officialData['labels']['paud_capaian'] }}</th>
                    </template>

                    {{-- Separator --}}
                    <th x-show="viewMode === 'all'" rowspan="2" class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></th>

                    {{-- Blok SD --}}
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th rowspan="2" class="py-3 px-2 text-center border-r border-slate-200 w-10">No</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th rowspan="2" class="py-3 px-3 border-r border-slate-200 min-w-[140px]">Kecamatan</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['sd_penduduk'] }}</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['sd_siswa'] }}</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200 bg-indigo-50/50 text-indigo-900">{{ $officialData['labels']['sd_capaian'] }}</th>
                    </template>

                    {{-- Separator --}}
                    <th x-show="viewMode === 'all'" rowspan="2" class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></th>

                    {{-- Blok SMP --}}
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th rowspan="2" class="py-3 px-2 text-center border-r border-slate-200 w-10">No</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th rowspan="2" class="py-3 px-3 border-r border-slate-200 min-w-[140px]">Kecamatan</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['smp_penduduk'] }}</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['smp_siswa'] }}</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200 bg-sky-50/50 text-sky-900">{{ $officialData['labels']['smp_capaian'] }}</th>
                    </template>

                    {{-- Separator --}}
                    <th x-show="viewMode === 'all'" rowspan="2" class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></th>

                    {{-- Blok SMA / SMK --}}
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th rowspan="2" class="py-3 px-2 text-center border-r border-slate-200 w-10">No</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th rowspan="2" class="py-3 px-3 border-r border-slate-200 min-w-[140px]">Kecamatan</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['sma_penduduk'] }}</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['sma_siswa'] }}</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th colspan="3" class="py-2 px-2 text-center bg-emerald-50/50 text-emerald-900">{{ $officialData['labels']['sma_capaian'] }}</th>
                    </template>
                </tr>

                {{-- Baris 3: Subheader L, P, Jml --}}
                <tr class="bg-slate-100/80 text-slate-600 font-bold text-[10px] text-center border-b border-slate-200">
                    {{-- Subheader PAUD --}}
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th class="py-1.5 px-2 border-r border-slate-200">L</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th class="py-1.5 px-2 border-r border-slate-200">P</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th class="py-1.5 px-2 border-r border-slate-200">L</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th class="py-1.5 px-2 border-r border-slate-200">P</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-blue-50/50">L (%)</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-blue-50/50">P (%)</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'paud'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-blue-50/50">Jml (%)</th>
                    </template>

                    {{-- Subheader SD --}}
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th class="py-1.5 px-2 border-r border-slate-200">L</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th class="py-1.5 px-2 border-r border-slate-200">P</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th class="py-1.5 px-2 border-r border-slate-200">L</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th class="py-1.5 px-2 border-r border-slate-200">P</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-indigo-50/50">L (%)</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-indigo-50/50">P (%)</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sd'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-indigo-50/50">Jml (%)</th>
                    </template>

                    {{-- Subheader SMP --}}
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th class="py-1.5 px-2 border-r border-slate-200">L</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th class="py-1.5 px-2 border-r border-slate-200">P</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th class="py-1.5 px-2 border-r border-slate-200">L</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th class="py-1.5 px-2 border-r border-slate-200">P</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-sky-50/50">L (%)</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-sky-50/50">P (%)</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'smp'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-sky-50/50">Jml (%)</th>
                    </template>

                    {{-- Subheader SMA / SMK --}}
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th class="py-1.5 px-2 border-r border-slate-200">L</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th class="py-1.5 px-2 border-r border-slate-200">P</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th class="py-1.5 px-2 border-r border-slate-200">L</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th class="py-1.5 px-2 border-r border-slate-200">P</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-emerald-50/50">L (%)</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th class="py-1.5 px-2 border-r border-slate-200 bg-emerald-50/50">P (%)</th>
                    </template>
                    <template x-if="viewMode === 'all' || viewMode === 'sma'">
                        <th class="py-1.5 px-2 bg-emerald-50/50">Jml (%)</th>
                    </template>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-[11px]">
                @foreach($officialData['rows'] as $r)
                <tr class="hover:bg-blue-50/40 transition">
                    {{-- Data PAUD --}}
                    @if(true)
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-center text-slate-400 font-mono border-r border-slate-100">{{ $r['no'] }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-3 font-semibold text-slate-900 border-r border-slate-100 whitespace-nowrap">{{ $r['nama'] }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['paud']['penduduk']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['paud']['penduduk']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-semibold">{{ number_format($r['paud']['penduduk']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['paud']['siswa']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['paud']['siswa']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-semibold">{{ number_format($r['paud']['siswa']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono text-blue-700 bg-blue-50/20">{{ number_format($r['paud']['capaian']['l'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono text-pink-700 bg-blue-50/20">{{ number_format($r['paud']['capaian']['p'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-bold text-blue-900 bg-blue-50/30">{{ number_format($r['paud']['capaian']['jml'], 2) }}%</td>
                    @endif

                    {{-- Spacer --}}
                    <td x-show="viewMode === 'all'" class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></td>

                    {{-- Data SD --}}
                    @if(true)
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-center text-slate-400 font-mono border-r border-slate-100">{{ $r['no'] }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-3 font-semibold text-slate-900 border-r border-slate-100 whitespace-nowrap">{{ $r['nama'] }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['sd']['penduduk']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['sd']['penduduk']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-semibold">{{ number_format($r['sd']['penduduk']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['sd']['siswa']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['sd']['siswa']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-semibold">{{ number_format($r['sd']['siswa']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono text-indigo-700 bg-indigo-50/20">{{ number_format($r['sd']['capaian']['l'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono text-pink-700 bg-indigo-50/20">{{ number_format($r['sd']['capaian']['p'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-bold text-indigo-900 bg-indigo-50/30">{{ number_format($r['sd']['capaian']['jml'], 2) }}%</td>
                    @endif

                    {{-- Spacer --}}
                    <td x-show="viewMode === 'all'" class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></td>

                    {{-- Data SMP --}}
                    @if(true)
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-center text-slate-400 font-mono border-r border-slate-100">{{ $r['no'] }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-3 font-semibold text-slate-900 border-r border-slate-100 whitespace-nowrap">{{ $r['nama'] }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['smp']['penduduk']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['smp']['penduduk']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-semibold">{{ number_format($r['smp']['penduduk']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['smp']['siswa']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['smp']['siswa']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-semibold">{{ number_format($r['smp']['siswa']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono text-sky-700 bg-sky-50/20">{{ number_format($r['smp']['capaian']['l'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono text-pink-700 bg-sky-50/20">{{ number_format($r['smp']['capaian']['p'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-bold text-sky-900 bg-sky-50/30">{{ number_format($r['smp']['capaian']['jml'], 2) }}%</td>
                    @endif

                    {{-- Spacer --}}
                    <td x-show="viewMode === 'all'" class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></td>

                    {{-- Data SMA / SMK --}}
                    @if(isset($r['sma']))
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-center text-slate-400 font-mono border-r border-slate-100">{{ $r['no'] }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-3 font-semibold text-slate-900 border-r border-slate-100 whitespace-nowrap">{{ $r['nama'] }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['sma']['penduduk']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['sma']['penduduk']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-semibold">{{ number_format($r['sma']['penduduk']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['sma']['siswa']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono">{{ number_format($r['sma']['siswa']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono font-semibold">{{ number_format($r['sma']['siswa']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono text-emerald-700 bg-emerald-50/20">{{ number_format($r['sma']['capaian']['l'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-right border-r border-slate-100 font-mono text-pink-700 bg-emerald-50/20">{{ number_format($r['sma']['capaian']['p'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-2.5 px-2 text-right font-mono font-bold text-emerald-900 bg-emerald-50/30">{{ number_format($r['sma']['capaian']['jml'], 2) }}%</td>
                    @endif
                </tr>
                @endforeach
            </tbody>

            {{-- Footer Tabel (Baris Total / Jumlah Kabupaten) --}}
            <tfoot>
                <tr class="bg-slate-200/90 text-slate-900 font-bold text-[11px] border-t-2 border-slate-300">
                    {{-- Total PAUD --}}
                    @php $tot = $officialData['total']; @endphp
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 border-r border-slate-300"></td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-3 font-extrabold border-r border-slate-300">Jumlah</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['penduduk']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['penduduk']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['penduduk']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['siswa']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['siswa']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['paud']['siswa']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold text-blue-800 bg-blue-100/50">{{ number_format($tot['paud']['capaian']['l'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold text-pink-800 bg-blue-100/50">{{ number_format($tot['paud']['capaian']['p'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'paud'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-black text-blue-950 bg-blue-100/70">{{ number_format($tot['paud']['capaian']['jml'], 2) }}%</td>

                    {{-- Spacer --}}
                    <td x-show="viewMode === 'all'" class="w-3 bg-slate-200 p-0 border-r border-slate-300"></td>

                    {{-- Total SD --}}
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 border-r border-slate-300"></td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-3 font-extrabold border-r border-slate-300">Jumlah</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['penduduk']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['penduduk']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['penduduk']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['siswa']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['siswa']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sd']['siswa']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold text-indigo-800 bg-indigo-100/50">{{ number_format($tot['sd']['capaian']['l'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold text-pink-800 bg-indigo-100/50">{{ number_format($tot['sd']['capaian']['p'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sd'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-black text-indigo-950 bg-indigo-100/70">{{ number_format($tot['sd']['capaian']['jml'], 2) }}%</td>

                    {{-- Spacer --}}
                    <td x-show="viewMode === 'all'" class="w-3 bg-slate-200 p-0 border-r border-slate-300"></td>

                    {{-- Total SMP --}}
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 border-r border-slate-300"></td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-3 font-extrabold border-r border-slate-300">Jumlah</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['penduduk']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['penduduk']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['penduduk']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['siswa']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['siswa']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['smp']['siswa']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold text-sky-800 bg-sky-100/50">{{ number_format($tot['smp']['capaian']['l'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold text-pink-800 bg-sky-100/50">{{ number_format($tot['smp']['capaian']['p'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'smp'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-black text-sky-950 bg-sky-100/70">{{ number_format($tot['smp']['capaian']['jml'], 2) }}%</td>

                    {{-- Spacer --}}
                    <td x-show="viewMode === 'all'" class="w-3 bg-slate-200 p-0 border-r border-slate-300"></td>

                    {{-- Total SMA / SMK --}}
                    @if(isset($tot['sma']))
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 border-r border-slate-300"></td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-3 font-extrabold border-r border-slate-300">Jumlah</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['penduduk']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['penduduk']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['penduduk']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['siswa']['l']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['siswa']['p']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold">{{ number_format($tot['sma']['siswa']['jml']) }}</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold text-emerald-800 bg-emerald-100/50">{{ number_format($tot['sma']['capaian']['l'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 text-right border-r border-slate-300 font-mono font-extrabold text-pink-800 bg-emerald-100/50">{{ number_format($tot['sma']['capaian']['p'], 2) }}%</td>
                    <td x-show="viewMode === 'all' || viewMode === 'sma'" class="py-3 px-2 text-right font-mono font-black text-emerald-950 bg-emerald-100/70">{{ number_format($tot['sma']['capaian']['jml'], 2) }}%</td>
                    @endif
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Footer Catatan Sumber Data Sesuai Format File XLSX -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-500 pt-1 px-1">
        <div class="flex items-center gap-1.5 font-medium italic">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $officialData['sumber'] }}</span>
        </div>
        <div class="text-[11px] text-slate-400 mt-1 sm:mt-0">
            Format tabel telah disinkronkan persis dengan standar pelaporan resmi <strong class="text-slate-600">{{ strtoupper($officialData['tipe']) }}.xlsx</strong>
        </div>
    </div>
</div>

