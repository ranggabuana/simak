{{-- 
    Tabel Indikator Resmi (Format Desain Baru Sesuai Basis Sekolah / format-baru.jpeg)
    Mencakup 4 Blok Jenjang: PAUD (usia 5-6), SD (usia 7-12), SMP (usia 13-15), dan SMA/SMK/MA (usia 16-18)
    Struktur tabel: Kecamatan | Penduduk Usia (L, P, Jml) | Jumlah Siswa Terpilah Bentuk Pendidikan (L, P, Jml) | Capaian Indikator (L, P, Jml)
--}}
<div class="space-y-6 w-full max-w-full" x-data="{ viewMode: 'all', downloadDropdown: false }">
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

    <!-- Tabel Setiap Blok Jenjang Mengikuti Desain format-baru.jpeg -->
    @foreach($officialData['blocks'] as $blockKey => $block)
    <div x-show="viewMode === 'all' || viewMode === '{{ $blockKey }}'" 
         x-cloak
         class="space-y-2.5">
        
        <!-- Judul Tabel Tebal Sesuai Acuan format-baru.jpeg -->
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">
                {{ $block['title'] }}
            </h3>
        </div>

        <!-- Wadah Tabel Grid Spreadsheet Bergaris Tegas -->
        <div class="w-full max-w-full overflow-x-auto rounded-xl border border-slate-300 shadow-2xs bg-white scrollbar-thin">
            <table class="w-full text-left text-xs border-collapse border border-slate-300">
                <thead>
                    {{-- Baris 1: Header Utama --}}
                    <tr class="bg-slate-50 text-slate-900 font-extrabold text-center border-b border-slate-300">
                        <th rowspan="3" class="py-2.5 px-3 border border-slate-300 min-w-[170px] text-center align-middle whitespace-nowrap">
                            Kecamatan
                        </th>
                        <th colspan="3" rowspan="2" class="py-2 px-2 border border-slate-300 text-center align-middle whitespace-nowrap">
                            {{ $block['penduduk_header'] }}
                        </th>
                        <th colspan="{{ count($block['forms']) * 3 }}" class="py-2 px-2 border border-slate-300 text-center align-middle whitespace-nowrap">
                            {{ $block['siswa_header'] }}
                        </th>
                        <th colspan="3" rowspan="2" class="py-2 px-2 border border-slate-300 text-center align-middle whitespace-nowrap bg-blue-50/60 text-blue-950">
                            {{ $block['capaian_header'] }}
                        </th>
                    </tr>

                    {{-- Baris 2: Sub-Header Tiap Bentuk Pendidikan --}}
                    <tr class="bg-slate-50 text-slate-900 font-extrabold text-center border-b border-slate-300">
                        @foreach($block['forms'] as $fKey => $fLabel)
                        <th colspan="3" class="py-1.5 px-2 border border-slate-300 text-center align-middle whitespace-nowrap">
                            {{ $fLabel }}
                        </th>
                        @endforeach
                    </tr>

                    {{-- Baris 3: Sub-Kolom L, P, Jml --}}
                    <tr class="bg-slate-100/90 text-slate-800 font-bold text-[11px] text-center border-b border-slate-300">
                        {{-- Penduduk: L, P, Jml --}}
                        <th class="py-1.5 px-2 border border-slate-300 w-14">L</th>
                        <th class="py-1.5 px-2 border border-slate-300 w-14">P</th>
                        <th class="py-1.5 px-2 border border-slate-300 w-16">Jml</th>

                        {{-- Bentuk Sekolah: L, P, Jml per Bentuk --}}
                        @foreach($block['forms'] as $fKey => $fLabel)
                        <th class="py-1.5 px-1.5 border border-slate-300 w-12">L</th>
                        <th class="py-1.5 px-1.5 border border-slate-300 w-12">P</th>
                        <th class="py-1.5 px-1.5 border border-slate-300 w-14">Jml</th>
                        @endforeach

                        {{-- Capaian Indikator: L, P, Jml --}}
                        <th class="py-1.5 px-2 border border-slate-300 w-20 bg-blue-100/50 text-blue-950">L</th>
                        <th class="py-1.5 px-2 border border-slate-300 w-20 bg-blue-100/50 text-blue-950">P</th>
                        <th class="py-1.5 px-2 border border-slate-300 w-22 bg-blue-100/70 text-blue-950">Jml</th>
                    </tr>
                </thead>

                <tbody class="text-[11px]">
                    @foreach($officialData['rows'] as $r)
                    @php 
                        $bData = $r[$blockKey]; 
                        $namaKec = 'Kec. ' . ucwords(strtolower(preg_replace('/^kec(\.|\s+)/i', '', $r['nama'])));
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition">
                        {{-- Nama Kecamatan --}}
                        <td class="py-1.5 px-3 font-medium text-slate-900 border border-slate-300 whitespace-nowrap">
                            {{ $namaKec }}
                        </td>

                        {{-- Penduduk --}}
                        <td class="py-1.5 px-2 text-right border border-slate-300 font-mono text-slate-800">
                            {{ number_format($bData['penduduk']['l'], 0, ',', '.') }}
                        </td>
                        <td class="py-1.5 px-2 text-right border border-slate-300 font-mono text-slate-800">
                            {{ number_format($bData['penduduk']['p'], 0, ',', '.') }}
                        </td>
                        <td class="py-1.5 px-2 text-right border border-slate-300 font-mono font-bold text-slate-900 bg-slate-50/50">
                            {{ number_format($bData['penduduk']['jml'], 0, ',', '.') }}
                        </td>

                        {{-- Siswa per Bentuk Sekolah --}}
                        @foreach($block['forms'] as $fKey => $fLabel)
                        @php $f = $bData['breakdown'][$fKey] ?? ['l' => 0, 'p' => 0, 'jml' => 0]; @endphp
                        <td class="py-1.5 px-1.5 text-right border border-slate-300 font-mono text-slate-700">
                            {{ number_format($f['l'], 0, ',', '.') }}
                        </td>
                        <td class="py-1.5 px-1.5 text-right border border-slate-300 font-mono text-slate-700">
                            {{ number_format($f['p'], 0, ',', '.') }}
                        </td>
                        <td class="py-1.5 px-1.5 text-right border border-slate-300 font-mono font-semibold text-slate-900 bg-slate-50/40">
                            {{ number_format($f['jml'], 0, ',', '.') }}
                        </td>
                        @endforeach

                        {{-- Capaian Indikator (%) --}}
                        <td class="py-1.5 px-2 text-right border border-slate-300 font-mono text-blue-700 bg-blue-50/20 whitespace-nowrap">
                            {{ number_format($bData['capaian']['l'], 2, ',', '.') }} %
                        </td>
                        <td class="py-1.5 px-2 text-right border border-slate-300 font-mono text-indigo-700 bg-blue-50/20 whitespace-nowrap">
                            {{ number_format($bData['capaian']['p'], 2, ',', '.') }} %
                        </td>
                        <td class="py-1.5 px-2 text-right border border-slate-300 font-mono font-bold text-blue-950 bg-blue-50/40 whitespace-nowrap">
                            {{ number_format($bData['capaian']['jml'], 2, ',', '.') }} %
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- Footer Baris Jumlah / Total Kabupaten --}}
                @php $tot = $officialData['total'][$blockKey]; @endphp
                <tfoot>
                    <tr class="bg-slate-100 font-bold text-slate-900 text-[11px] border-t-2 border-slate-400">
                        <td class="py-2.5 px-3 font-black border border-slate-300 whitespace-nowrap">
                            Jumlah
                        </td>

                        {{-- Total Penduduk --}}
                        <td class="py-2 px-2 text-right border border-slate-300 font-mono font-extrabold">
                            {{ number_format($tot['penduduk']['l'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-2 text-right border border-slate-300 font-mono font-extrabold">
                            {{ number_format($tot['penduduk']['p'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-2 text-right border border-slate-300 font-mono font-black bg-slate-200/50">
                            {{ number_format($tot['penduduk']['jml'], 0, ',', '.') }}
                        </td>

                        {{-- Total Tiap Bentuk Sekolah --}}
                        @foreach($block['forms'] as $fKey => $fLabel)
                        @php $fTot = $tot['breakdown'][$fKey] ?? ['l' => 0, 'p' => 0, 'jml' => 0]; @endphp
                        <td class="py-2 px-1.5 text-right border border-slate-300 font-mono font-bold">
                            {{ number_format($fTot['l'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-1.5 text-right border border-slate-300 font-mono font-bold">
                            {{ number_format($fTot['p'], 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-1.5 text-right border border-slate-300 font-mono font-extrabold bg-slate-200/40">
                            {{ number_format($fTot['jml'], 0, ',', '.') }}
                        </td>
                        @endforeach

                        {{-- Total Capaian Kabupaten (%) --}}
                        <td class="py-2 px-2 text-right border border-slate-300 font-mono font-extrabold text-blue-900 bg-blue-100/50 whitespace-nowrap">
                            {{ number_format($tot['capaian']['l'], 2, ',', '.') }} %
                        </td>
                        <td class="py-2 px-2 text-right border border-slate-300 font-mono font-extrabold text-indigo-900 bg-blue-100/50 whitespace-nowrap">
                            {{ number_format($tot['capaian']['p'], 2, ',', '.') }} %
                        </td>
                        <td class="py-2 px-2 text-right border border-slate-300 font-mono font-black text-blue-950 bg-blue-100/70 whitespace-nowrap">
                            {{ number_format($tot['capaian']['jml'], 2, ',', '.') }} %
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endforeach

    <!-- Footer Catatan Sumber Data Sesuai Format File XLSX -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-500 pt-1 px-1">
        <div class="flex items-center gap-1.5 font-medium italic">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $officialData['sumber'] }}</span>
        </div>
        <div class="text-[11px] text-slate-400 mt-1 sm:mt-0">
            Format tabel telah disinkronkan persis dengan format desain basis sekolah <strong class="text-slate-600">{{ strtoupper($officialData['tipe']) }}</strong>
        </div>
    </div>
</div>
