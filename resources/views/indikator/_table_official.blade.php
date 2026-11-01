{{-- 
    Tabel Indikator Resmi (Format Format File XLSX: APK.xlsx, APM.xlsx, APS.xlsx)
    Mencakup 4 Blok Jenjang: PAUD (usia 5-6), SD (usia 7-12), SMP (usia 13-15), dan SMA/SMK/MA (usia 16-18)
--}}
<div class="space-y-4 w-full max-w-full" x-data="{ viewMode: 'all', downloadDropdown: false }">
    <!-- View Mode Switcher & Compact Download Dropdown Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50 p-2.5 sm:p-3 rounded-2xl border border-slate-200">
        
        <!-- Tab Filter Jenjang -->
        <div class="flex items-center gap-2 min-w-0 flex-1 overflow-x-auto scrollbar-none py-0.5">
            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5 shrink-0 hidden sm:flex">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
                Jenjang:
            </span>
            <div class="inline-flex items-center p-1 bg-white rounded-xl border border-slate-200 text-xs font-semibold shadow-2xs shrink-0">
                <button type="button" 
                        @click="viewMode = 'all'" 
                        :class="viewMode === 'all' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 sm:px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    Semua Jenjang
                </button>
                <button type="button" 
                        @click="viewMode = 'paud'" 
                        :class="viewMode === 'paud' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 sm:px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    PAUD
                </button>
                <button type="button" 
                        @click="viewMode = 'sd'" 
                        :class="viewMode === 'sd' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 sm:px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    SD
                </button>
                <button type="button" 
                        @click="viewMode = 'smp'" 
                        :class="viewMode === 'smp' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 sm:px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    SMP
                </button>
                <button type="button" 
                        @click="viewMode = 'sma'" 
                        :class="viewMode === 'sma' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-2.5 sm:px-3 py-1.5 rounded-lg whitespace-nowrap transition cursor-pointer">
                    SMA / SMK / MA
                </button>
            </div>
        </div>

        <!-- Sejajar: Tombol Dropdown Unduh Data -->
        @php
            $tipeExport = strtolower($officialData['tipe']);
            $routeExport = in_array($tipeExport, ['apk', 'apm', 'aps']) ? route('export.'.$tipeExport, ['tahun' => $tahun]) : route('export.kecamatan', ['tahun' => $tahun]);
            $routeLaporan = route('indikator.laporan', ['tipe' => $tipeExport, 'tahun' => $tahun]);
        @endphp
        <div class="relative shrink-0 self-end md:self-auto" @click.away="downloadDropdown = false">
            <button type="button" 
                    @click="downloadDropdown = !downloadDropdown" 
                    class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-300 font-bold text-xs shadow-2xs hover:shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Data</span>
                <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="downloadDropdown ? 'rotate-180 text-blue-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Menu Dropdown -->
            <div x-show="downloadDropdown" 
                 x-cloak 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-1.5 w-72 rounded-2xl bg-white border border-slate-200 shadow-xl p-1.5 z-40 divide-y divide-slate-100">
                
                {{-- Opsi 1: Unduh Excel (.xlsx) --}}
                <a href="{{ $routeExport }}" 
                   @click="downloadDropdown = false"
                   class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition group">
                    <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <span class="block text-xs font-bold text-slate-800 group-hover:text-emerald-700 transition">
                            Unduh Excel (.xlsx)
                        </span>
                        <span class="block text-[11px] text-slate-500 leading-tight">
                            Format tabel resmi multi-blok (PAUD, SD, SMP, SMA)
                        </span>
                    </div>
                </a>

                {{-- Opsi 2: Unduh Laporan Visual Lengkap (Grafik & Tabel PDF) --}}
                <a href="{{ $routeLaporan }}" 
                   target="_blank"
                   @click="downloadDropdown = false"
                   class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition group">
                    <div class="p-2 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <span class="block text-xs font-bold text-slate-800 group-hover:text-blue-700 transition">
                            Unduh Laporan Visual (PDF)
                        </span>
                        <span class="block text-[11px] text-slate-500 leading-tight">
                            Dokumen resmi A4 rapi & minimalis: grafik & tabel lengkap
                        </span>
                    </div>
                </a>
            </div>
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
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" rowspan="2" class="py-3 px-2 text-center border-r border-slate-200 w-10">No</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" rowspan="2" class="py-3 px-3 border-r border-slate-200 min-w-[140px]">Kecamatan</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['paud_penduduk'] }}</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['paud_siswa'] }}</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200 bg-blue-50/50 text-blue-900">{{ $officialData['labels']['paud_capaian'] }}</th>

                    {{-- Separator --}}
                    <th x-show="viewMode === 'all'" rowspan="2" class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></th>

                    {{-- Blok SD --}}
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" rowspan="2" class="py-3 px-2 text-center border-r border-slate-200 w-10">No</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" rowspan="2" class="py-3 px-3 border-r border-slate-200 min-w-[140px]">Kecamatan</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['sd_penduduk'] }}</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['sd_siswa'] }}</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200 bg-indigo-50/50 text-indigo-900">{{ $officialData['labels']['sd_capaian'] }}</th>

                    {{-- Separator --}}
                    <th x-show="viewMode === 'all'" rowspan="2" class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></th>

                    {{-- Blok SMP --}}
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" rowspan="2" class="py-3 px-2 text-center border-r border-slate-200 w-10">No</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" rowspan="2" class="py-3 px-3 border-r border-slate-200 min-w-[140px]">Kecamatan</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['smp_penduduk'] }}</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['smp_siswa'] }}</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200 bg-sky-50/50 text-sky-900">{{ $officialData['labels']['smp_capaian'] }}</th>

                    {{-- Separator --}}
                    <th x-show="viewMode === 'all'" rowspan="2" class="w-3 bg-slate-100/50 p-0 border-r border-slate-200"></th>

                    {{-- Blok SMA / SMK --}}
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" rowspan="2" class="py-3 px-2 text-center border-r border-slate-200 w-10">No</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" rowspan="2" class="py-3 px-3 border-r border-slate-200 min-w-[140px]">Kecamatan</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['sma_penduduk'] }}</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" colspan="3" class="py-2 px-2 text-center border-r border-slate-200">{{ $officialData['labels']['sma_siswa'] }}</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" colspan="3" class="py-2 px-2 text-center bg-emerald-50/50 text-emerald-900">{{ $officialData['labels']['sma_capaian'] }}</th>
                </tr>

                {{-- Baris 3: Subheader L, P, Jml --}}
                <tr class="bg-slate-100/80 text-slate-600 font-bold text-[10px] text-center border-b border-slate-200">
                    {{-- Subheader PAUD --}}
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" class="py-1.5 px-2 border-r border-slate-200">L</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" class="py-1.5 px-2 border-r border-slate-200">P</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" class="py-1.5 px-2 border-r border-slate-200">L</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" class="py-1.5 px-2 border-r border-slate-200">P</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" class="py-1.5 px-2 border-r border-slate-200 bg-blue-50/50">L (%)</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" class="py-1.5 px-2 border-r border-slate-200 bg-blue-50/50">P (%)</th>
                    <th x-show="viewMode === 'all' || viewMode === 'paud'" class="py-1.5 px-2 border-r border-slate-200 bg-blue-50/50">Jml (%)</th>

                    {{-- Subheader SD --}}
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" class="py-1.5 px-2 border-r border-slate-200">L</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" class="py-1.5 px-2 border-r border-slate-200">P</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" class="py-1.5 px-2 border-r border-slate-200">L</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" class="py-1.5 px-2 border-r border-slate-200">P</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" class="py-1.5 px-2 border-r border-slate-200 bg-indigo-50/50">L (%)</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" class="py-1.5 px-2 border-r border-slate-200 bg-indigo-50/50">P (%)</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sd'" class="py-1.5 px-2 border-r border-slate-200 bg-indigo-50/50">Jml (%)</th>

                    {{-- Subheader SMP --}}
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" class="py-1.5 px-2 border-r border-slate-200">L</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" class="py-1.5 px-2 border-r border-slate-200">P</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" class="py-1.5 px-2 border-r border-slate-200">L</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" class="py-1.5 px-2 border-r border-slate-200">P</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" class="py-1.5 px-2 border-r border-slate-200 bg-sky-50/50">L (%)</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" class="py-1.5 px-2 border-r border-slate-200 bg-sky-50/50">P (%)</th>
                    <th x-show="viewMode === 'all' || viewMode === 'smp'" class="py-1.5 px-2 border-r border-slate-200 bg-sky-50/50">Jml (%)</th>

                    {{-- Subheader SMA / SMK --}}
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" class="py-1.5 px-2 border-r border-slate-200">L</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" class="py-1.5 px-2 border-r border-slate-200">P</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" class="py-1.5 px-2 border-r border-slate-200">L</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" class="py-1.5 px-2 border-r border-slate-200">P</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" class="py-1.5 px-2 border-r border-slate-200">Jml</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" class="py-1.5 px-2 border-r border-slate-200 bg-emerald-50/50">L (%)</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" class="py-1.5 px-2 border-r border-slate-200 bg-emerald-50/50">P (%)</th>
                    <th x-show="viewMode === 'all' || viewMode === 'sma'" class="py-1.5 px-2 bg-emerald-50/50">Jml (%)</th>
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

