@extends('layouts.app', ['title' => 'Peta Sebaran 20 Kecamatan'])

@section('content')
<style>
    /* Leaflet Tooltip Ultra Modern */
    .leaflet-tooltip-custom {
        background: rgba(15, 23, 42, 0.94) !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        border-radius: 14px !important;
        color: #ffffff !important;
        padding: 8px 14px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.35) !important;
        backdrop-filter: blur(8px) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }
    .leaflet-tooltip-custom::before {
        border-top-color: rgba(15, 23, 42, 0.94) !important;
    }

    /* Leaflet Custom Popup */
    .leaflet-popup-content-wrapper {
        border-radius: 18px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
        border: 1px solid #e2e8f0 !important;
        padding: 4px !important;
    }
    .leaflet-popup-tip {
        background: #ffffff !important;
    }

    /* Smooth polygon transition */
    path.leaflet-interactive {
        transition: stroke-width 0.15s ease, fill-opacity 0.2s ease, stroke 0.15s ease;
        cursor: pointer;
    }

    /* Hilangkan kotak persegi panjang hitam / focus bounding box pada path SVG & Leaflet */
    .leaflet-container,
    .leaflet-container:focus,
    .leaflet-container :focus,
    .leaflet-container svg:focus,
    .leaflet-container path:focus,
    path.leaflet-interactive,
    path.leaflet-interactive:focus,
    path.leaflet-interactive:active,
    .leaflet-interactive,
    .leaflet-interactive:focus,
    .leaflet-interactive:active,
    svg:focus,
    path:focus {
        outline: none !important;
        -webkit-tap-highlight-color: transparent !important;
        box-shadow: none !important;
    }
</style>

<div class="space-y-6" x-data="{ rightTab: 'stats', schoolSearch: '' }">

    <!-- Header Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-50/80 via-slate-50 to-white text-slate-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-xs border border-emerald-200/70">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 tracking-wider border border-emerald-200">GIS Spasial Interaktif</span>
                <h2 class="text-lg font-bold text-slate-900">Peta Sebaran Kinerja Pendidikan 20 Kecamatan</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Visualisasi geospasial batas wilayah resmi (GeoJSON) Kabupaten Banjarnegara dengan fitur <strong>Drilldown Wilayah</strong> untuk menelaah sebaran titik sekolah per kecamatan secara mendalam.
            </p>
        </div>
        
        <!-- Indikator Layer Switcher -->
        <form method="GET" class="flex items-center gap-2.5 bg-white p-1.5 rounded-2xl border border-slate-200 shadow-2xs">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <label class="text-xs font-bold text-slate-600 whitespace-nowrap pl-2">Indikator:</label>
            <select name="indikator" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="apk_sd" {{ $indikator === 'apk_sd' ? 'selected' : '' }}>APK SD / MI</option>
                <option value="apm_sd" {{ $indikator === 'apm_sd' ? 'selected' : '' }}>APM SD / MI</option>
                <option value="aps_sd" {{ $indikator === 'aps_sd' ? 'selected' : '' }}>APS SD / MI</option>
                <option value="guru" {{ $indikator === 'guru' ? 'selected' : '' }}>% Guru Bersertifikasi</option>
                <option value="paud" {{ $indikator === 'paud' ? 'selected' : '' }}>% PAUD Min. B</option>
            </select>
        </form>
    </div>

    <!-- Map & Detail Panel Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Interactive Leaflet Map Container -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-sm space-y-4">
            
            <!-- Map Controls Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-3 px-1 text-xs">
                <!-- Dropdown Pilih / Drilldown Kecamatan -->
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-600 text-[11px]">Pilih Kecamatan:</span>
                    <select id="selectKecamatanZoom" class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-bold text-xs focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="">-- Seluruh Kabupaten (20 Wilayah) --</option>
                        @foreach($kecamatanList as $k)
                            <option value="{{ $k['nama'] }}">{{ $k['nama'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Kontrol Titik Sekolah & Filter Jenjang -->
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1.5 rounded-xl border border-slate-200">
                        <span class="text-[11px] font-bold text-slate-600">Filter Jenjang:</span>
                        <select id="selectSchoolJenjang" class="bg-transparent text-slate-800 font-bold text-[11px] outline-none cursor-pointer">
                            <option value="ALL">Semua Jenjang</option>
                            <option value="PAUD">PAUD / TK</option>
                            <option value="SD">SD / MI</option>
                            <option value="SMP">SMP / MTs</option>
                            <option value="SMA">SMA / SMK</option>
                        </select>
                    </div>

                    <button type="button" 
                            id="btnResetView" 
                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer"
                            title="Reset Tampilan Peta">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Reset Zoom</span>
                    </button>
                </div>
            </div>
            
            <!-- Map Canvas Wrapper with Overlays -->
            <div class="relative w-full h-[560px] rounded-2xl overflow-hidden border border-slate-200 shadow-inner">
                
                <!-- Floating Drilldown Breadcrumb (Appears when drilled down into a kecamatan) -->
                <div id="drilldownBreadcrumb" class="absolute top-3.5 left-14 z-[450] hidden items-center gap-2 bg-white/95 backdrop-blur-md px-3.5 py-2 rounded-2xl shadow-lg border border-slate-200/90 text-xs transition">
                    <button type="button" 
                            id="btnBackToKabupaten" 
                            class="flex items-center gap-1.5 font-bold text-blue-600 hover:text-blue-800 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kabupaten</span>
                    </button>
                    <span class="text-slate-300 font-bold">&rsaquo;</span>
                    <span class="font-extrabold text-slate-900" id="drilldownKecName">Kecamatan</span>
                    <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-black text-[10px] border border-blue-200" id="drilldownSchoolCount">0 Titik Sekolah</span>
                </div>

                <!-- Floating Live Hover HUD (Shows on hover over polygon) -->
                <div id="hoverInfoHud" class="absolute top-3.5 right-3.5 z-[450] pointer-events-none transition-all duration-200 opacity-0 translate-y-[-4px] bg-slate-900/90 text-white backdrop-blur-md px-4 py-2.5 rounded-2xl shadow-xl border border-slate-700/60 text-xs min-w-[200px] max-w-xs">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-extrabold text-sm tracking-tight text-white" id="hudKecName">Kecamatan</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-500/30 text-blue-300 border border-blue-400/40" id="hudCapaianVal">0%</span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-300 mt-1">
                        <span id="hudSekolahCount">0 Satuan Pendidikan</span>
                        <span class="text-amber-300 font-bold text-[10px]">Klik untuk Drilldown &rarr;</span>
                    </div>
                </div>

                <!-- The Leaflet Map Canvas -->
                <div id="mapBanjarnegara" class="w-full h-full z-10"></div>
            </div>

            <!-- Legend & Interactive Hint Bar -->
            <div class="flex flex-wrap items-center justify-between text-[11px] text-slate-500 px-2 pt-2 border-t border-slate-100 gap-3">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700">Skala Capaian Wilayah:</span>
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-emerald-500 shadow-2xs"></span> Capaian Tinggi (&ge; 90%)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-blue-500 shadow-2xs"></span> Capaian Sedang (75%–89%)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-amber-500 shadow-2xs"></span> Perlu Akselerasi (&lt; 75%)</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700">Titik Satuan Pendidikan:</span>
                    <div class="flex items-center gap-2.5">
                        <span class="flex items-center gap-1 text-[10px]"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-white"></span> PAUD/TK</span>
                        <span class="flex items-center gap-1 text-[10px]"><span class="w-2.5 h-2.5 rounded-full bg-blue-600 ring-2 ring-white"></span> SD/MI</span>
                        <span class="flex items-center gap-1 text-[10px]"><span class="w-2.5 h-2.5 rounded-full bg-indigo-600 ring-2 ring-white"></span> SMP/MTs</span>
                        <span class="flex items-center gap-1 text-[10px]"><span class="w-2.5 h-2.5 rounded-full bg-purple-600 ring-2 ring-white"></span> SMA/SMK</span>
                    </div>
                </div>
            </div>

            <div class="p-2.5 bg-blue-50/60 rounded-xl border border-blue-100 text-[11px] text-blue-900 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><strong>Panduan Interaktif:</strong> Klik pada polygon kecamatan mana pun untuk langsung <strong>drilldown</strong> masuk ke area wilayah tersebut dan memuat titik-titik satuan pendidikan di dalamnya.</span>
                </div>
                <span class="font-bold text-blue-700 shrink-0 text-[10px] uppercase tracking-wider">Mode Drilldown Cerdas</span>
            </div>
        </div>

        <!-- Right Side: Interactive Adaptive Detail Panel -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between space-y-5">
            <div>
                <!-- Header with Title & Mode Badge -->
                <div class="border-b border-slate-100 pb-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400" id="panelCategoryBadge">Wilayah Terpilih</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200" id="infoStatusKecamatan">Mode Kabupaten</span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-900 mt-1 tracking-tight" id="infoNamaKecamatan">
                        {{ $kecamatanList->first()['nama'] }}
                    </h3>
                    <p class="text-xs text-slate-500 font-mono" id="infoKodeKecamatan">
                        Kode BPS: {{ $kecamatanList->first()['kode'] }}
                    </p>
                </div>

                <!-- Navigation Tabs in Right Panel -->
                <div class="flex items-center border-b border-slate-200 mt-3 text-xs font-bold">
                    <button type="button" 
                            @click="rightTab = 'stats'" 
                            :class="rightTab === 'stats' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500 hover:text-slate-800'"
                            class="pb-2.5 px-3 transition cursor-pointer">
                        Statistik Kinerja
                    </button>
                    <button type="button" 
                            @click="rightTab = 'schools'" 
                            :class="rightTab === 'schools' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500 hover:text-slate-800'"
                            class="pb-2.5 px-3 transition flex items-center gap-1.5 cursor-pointer">
                        <span>Daftar Sekolah</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700" id="badgeTabSchoolCount">0</span>
                    </button>
                </div>

                <!-- Tab 1: Key Statistics Indicators -->
                <div x-show="rightTab === 'stats'" class="mt-4 space-y-3 text-xs">
                    <!-- Populasi 7-12 Th -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 flex items-center justify-between border border-slate-100">
                        <span class="text-slate-600 font-medium">Penduduk 7–12 Th (SD):</span>
                        <strong class="text-slate-900 text-sm" id="infoPop7_12">{{ number_format($kecamatanList->first()['populasi']['usia_7_12']) }}</strong>
                    </div>

                    <!-- APK & APM SD -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100">
                            <span class="text-[11px] text-blue-900 font-bold block">APK SD</span>
                            <span class="text-lg font-black text-blue-700" id="infoApkSd">{{ number_format($kecamatanList->first()['apk']['sd'], 1) }}%</span>
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-50/60 border border-indigo-100">
                            <span class="text-[11px] text-indigo-900 font-bold block">APM SD</span>
                            <span class="text-lg font-black text-indigo-700" id="infoApmSd">{{ number_format($kecamatanList->first()['apm']['sd'], 1) }}%</span>
                        </div>
                    </div>

                    <!-- APK & APM SMP -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[11px] text-slate-600 font-bold block">APK SMP</span>
                            <span class="text-base font-extrabold text-slate-800" id="infoApkSmp">{{ number_format($kecamatanList->first()['apk']['smp'], 1) }}%</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                            <span class="text-[11px] text-slate-600 font-bold block">APM SMP</span>
                            <span class="text-base font-extrabold text-slate-800" id="infoApmSmp">{{ number_format($kecamatanList->first()['apm']['smp'], 1) }}%</span>
                        </div>
                    </div>

                    <!-- Guru Sertifikasi -->
                    <div class="p-3 rounded-xl bg-purple-50/50 border border-purple-100 flex items-center justify-between">
                        <div>
                            <span class="text-purple-900 font-bold block">Sertifikasi Guru</span>
                            <span class="text-[11px] text-purple-700 font-medium" id="infoGuruCount">{{ $kecamatanList->first()['guru']['sertifikasi'] }} / {{ $kecamatanList->first()['guru']['total'] }} Guru</span>
                        </div>
                        <strong class="text-base font-black text-purple-700" id="infoGuruPersen">{{ number_format($kecamatanList->first()['guru']['persentase'], 1) }}%</strong>
                    </div>

                    <!-- PAUD Terakreditasi B -->
                    <div class="p-3 rounded-xl bg-amber-50/50 border border-amber-100 flex items-center justify-between">
                        <div>
                            <span class="text-amber-900 font-bold block">PAUD Terakreditasi Min. B</span>
                            <span class="text-[11px] text-amber-700 font-medium" id="infoPaudCount">{{ $kecamatanList->first()['paud_akreditasi']['min_b'] }} / {{ $kecamatanList->first()['paud_akreditasi']['total'] }} PAUD</span>
                        </div>
                        <strong class="text-base font-black text-amber-700" id="infoPaudPersen">{{ number_format($kecamatanList->first()['paud_akreditasi']['persentase'], 1) }}%</strong>
                    </div>
                </div>

                <!-- Tab 2: School Directory in Selected Region -->
                <div x-show="rightTab === 'schools'" class="mt-4 space-y-3">
                    <!-- Quick Search in Active Region -->
                    <div class="relative">
                        <input type="text" 
                               id="inputFilterSchoolList" 
                               placeholder="Cari sekolah di wilayah ini..." 
                               class="w-full pl-8 pr-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Schools Scrollable Container -->
                    <div id="schoolListContainer" class="space-y-2 max-h-[300px] overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-slate-200 text-xs">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Footer Action Links -->
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <button type="button" 
                        id="btnPanelBackToKabupaten" 
                        class="w-full text-center py-2.5 px-4 rounded-xl font-bold text-xs bg-slate-800 hover:bg-slate-900 text-white shadow-sm transition hidden items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Seluruh Kabupaten</span>
                </button>

                <a href="{{ route('sekolah.index') }}" 
                   class="w-full text-center py-2.5 px-4 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition block">
                    Kelola Master Satuan Pendidikan &rarr;
                </a>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kecamatanData = {!! json_encode($kecamatanList) !!};
        const sekolahData = {!! json_encode($sekolahList) !!};
        const activeIndikator = '{{ $indikator }}';

        // Helper string normalizer for kecamatan names
        function normalizeName(str) {
            if (!str) return '';
            let s = str.toLowerCase();
            s = s.replace(/purworejo|purworeja|purwarejo/g, 'purwareja');
            s = s.replace(/klempok/g, 'klampok');
            return s.replace(/[^a-z0-9]/g, '');
        }

        // Map dictionary keyed by normalized name
        const kecMap = {};
        kecamatanData.forEach(k => {
            kecMap[normalizeName(k.nama)] = k;
        });

        // Initialize Leaflet Map centered at Banjarnegara
        const map = L.map('mapBanjarnegara', {
            center: [-7.3750, 109.6800],
            zoom: 11,
            zoomControl: true
        });

        // Ensure no focus bounding box outline is retained on map interaction
        map.getContainer().addEventListener('mousedown', function() {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
        });

        // Create Custom Panes for Perfect Layer Separation
        // Polygons on bottom pane, school markers on top pane so hovering polygon never glitches dots!
        map.createPane('polygonsPane');
        map.getPane('polygonsPane').style.zIndex = 400;

        map.createPane('schoolsPane');
        map.getPane('schoolsPane').style.zIndex = 650;

        // Base Map Layers
        const osmLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap kontributor',
            maxZoom: 19,
        }).addTo(map);

        const esriLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 19,
        });

        L.control.layers({
            'Peta Standar (OSM)': osmLayer,
            'Esri Street Map': esriLayer
        }, null, { position: 'topright' }).addTo(map);

        // Get indicator value & color for each kecamatan
        function getKecValue(kec) {
            if (!kec) return 0;
            if (activeIndikator === 'apk_sd') return Number(kec.apk.sd);
            if (activeIndikator === 'apm_sd') return Number(kec.apm.sd);
            if (activeIndikator === 'aps_sd') return Number(kec.aps.sd);
            if (activeIndikator === 'guru') return Number(kec.guru.persentase);
            if (activeIndikator === 'paud') return Number(kec.paud_akreditasi.persentase);
            return 0;
        }

        function getColor(val) {
            if (val >= 90) return '#10b981'; // emerald-500
            if (val >= 75) return '#3b82f6'; // blue-500
            return '#f59e0b'; // amber-500
        }

        function getSchoolColor(jenjang) {
            if (jenjang === 'PAUD' || jenjang === 'TK') return '#f59e0b'; // Amber
            if (jenjang === 'SD' || jenjang === 'MI') return '#2563eb'; // Blue
            if (jenjang === 'SMP' || jenjang === 'MTS') return '#4f46e5'; // Indigo
            return '#9333ea'; // Purple for SMA/SMK
        }

        // State variables for Drilldown
        let currentMode = 'kabupaten'; // 'kabupaten' | 'drilldown'
        let activeKec = null;
        let activeKecLayer = null;
        let geojsonLayer = null;
        const layerByKecName = {};
        const schoolMarkersMap = {}; // schoolId => Leaflet Marker
        const schoolLayerGroup = L.layerGroup().addTo(map);

        // Update Right Info Panel
        function updateRightPanel(kec, isDrilldown = false) {
            if (!kec) return;
            document.getElementById('infoNamaKecamatan').innerText = kec.nama;
            document.getElementById('infoKodeKecamatan').innerText = 'Kode BPS: ' + kec.kode;
            document.getElementById('infoPop7_12').innerText = Number(kec.populasi.usia_7_12).toLocaleString();
            document.getElementById('infoApkSd').innerText = Number(kec.apk.sd).toFixed(1) + '%';
            document.getElementById('infoApmSd').innerText = Number(kec.apm.sd).toFixed(1) + '%';
            document.getElementById('infoApkSmp').innerText = Number(kec.apk.smp).toFixed(1) + '%';
            document.getElementById('infoApmSmp').innerText = Number(kec.apm.smp).toFixed(1) + '%';
            document.getElementById('infoGuruCount').innerText = kec.guru.sertifikasi + ' / ' + kec.guru.total + ' Guru';
            document.getElementById('infoGuruPersen').innerText = Number(kec.guru.persentase).toFixed(1) + '%';
            document.getElementById('infoPaudCount').innerText = kec.paud_akreditasi.min_b + ' / ' + kec.paud_akreditasi.total + ' PAUD';
            document.getElementById('infoPaudPersen').innerText = Number(kec.paud_akreditasi.persentase).toFixed(1) + '%';

            const statusBadge = document.getElementById('infoStatusKecamatan');
            const btnPanelBack = document.getElementById('btnPanelBackToKabupaten');

            if (isDrilldown) {
                statusBadge.innerText = 'Drilldown Aktif';
                statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300';
                if (btnPanelBack) {
                    btnPanelBack.classList.remove('hidden');
                    btnPanelBack.classList.add('flex');
                }
            } else {
                statusBadge.innerText = 'Mode Kabupaten';
                statusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200';
                if (btnPanelBack) {
                    btnPanelBack.classList.remove('flex');
                    btnPanelBack.classList.add('hidden');
                }
            }

            renderSchoolListInPanel(kec);
        }

        // Render School List Cards in Right Panel for the active Kecamatan
        function renderSchoolListInPanel(kec, searchQuery = '') {
            const container = document.getElementById('schoolListContainer');
            const badgeCount = document.getElementById('badgeTabSchoolCount');
            if (!container) return;

            let filtered = sekolahData.filter(s => {
                const matchKec = s.kecamatan_id == kec.id || normalizeName(s.kecamatan?.nama) === normalizeName(kec.nama);
                if (!matchKec) return false;
                if (!searchQuery) return true;
                return s.nama_sekolah.toLowerCase().includes(searchQuery.toLowerCase()) || 
                       s.npsn.includes(searchQuery);
            });

            if (badgeCount) {
                badgeCount.innerText = filtered.length;
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-slate-500 text-xs">
                        Tidak ada satuan pendidikan ditemukan${searchQuery ? ' dengan kata kunci tersebut' : ''}.
                    </div>
                `;
                return;
            }

            let html = '';
            filtered.forEach(s => {
                const color = getSchoolColor(s.jenjang);
                const part = s.partisipasi_siswa?.[0] || null;
                const totalMurid = part ? part.total_siswa_semua_usia : 0;

                html += `
                    <div class="school-list-card p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-blue-50/50 hover:border-blue-300 transition cursor-pointer flex items-center justify-between gap-2 shadow-2xs" 
                         data-school-id="${s.id}" 
                         data-lat="${s.latitude}" 
                         data-lng="${s.longitude}">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase" style="background: ${color}15; color: ${color};">
                                    ${s.jenjang}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">NPSN: ${s.npsn}</span>
                            </div>
                            <strong class="text-slate-900 block truncate text-xs font-bold">${s.nama_sekolah}</strong>
                            <div class="text-[10px] text-slate-500 flex items-center gap-2 mt-0.5">
                                <span>Akr: <strong>${s.akreditasi}</strong></span>
                                <span>&bull;</span>
                                <span>${totalMurid} Siswa</span>
                            </div>
                        </div>
                        <span class="p-1 rounded-lg bg-slate-100 text-slate-500 hover:bg-blue-600 hover:text-white transition shrink-0" title="Fokus ke Marker">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                    </div>
                `;
            });

            container.innerHTML = html;

            // Bind click to focus marker on map
            container.querySelectorAll('.school-list-card').forEach(card => {
                card.addEventListener('click', function() {
                    const lat = parseFloat(this.getAttribute('data-lat'));
                    const lng = parseFloat(this.getAttribute('data-lng'));
                    const sId = this.getAttribute('data-school-id');

                    if (!isNaN(lat) && !isNaN(lng)) {
                        map.flyTo([lat, lng], 16, { animate: true, duration: 0.8 });
                        const marker = schoolMarkersMap[sId];
                        if (marker) {
                            marker.openPopup();
                        }
                    }
                });
            });
        }

        // Search input binding
        const searchInput = document.getElementById('inputFilterSchoolList');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                if (activeKec) {
                    renderSchoolListInPanel(activeKec, e.target.value);
                }
            });
        }

        // Render School Markers on Map (Filtered by Jenjang and optionally scoped to active kecamatan)
        function renderSchoolMarkers(targetKec = null, filterJenjang = 'ALL') {
            schoolLayerGroup.clearLayers();
            Object.keys(schoolMarkersMap).forEach(k => delete schoolMarkersMap[k]);

            let listToRender = sekolahData;
            if (targetKec) {
                listToRender = sekolahData.filter(s => {
                    return s.kecamatan_id == targetKec.id || 
                           normalizeName(s.kecamatan?.nama) === normalizeName(targetKec.nama);
                });
            }

            listToRender.forEach(s => {
                if (!s.latitude || !s.longitude) return;

                if (filterJenjang !== 'ALL') {
                    if (filterJenjang === 'PAUD' && !['PAUD', 'TK'].includes(s.jenjang)) return;
                    if (filterJenjang === 'SD' && !['SD', 'MI'].includes(s.jenjang)) return;
                    if (filterJenjang === 'SMP' && !['SMP', 'MTS'].includes(s.jenjang)) return;
                    if (filterJenjang === 'SMA' && !['SMA', 'SMK', 'MA'].includes(s.jenjang)) return;
                }

                const color = getSchoolColor(s.jenjang);
                const part = s.partisipasi_siswa?.[0] || null;
                const totalMurid = part ? part.total_siswa_semua_usia : '-';

                // Cohesive circle marker with soft white outline
                const marker = L.circleMarker([parseFloat(s.latitude), parseFloat(s.longitude)], {
                    pane: 'schoolsPane',
                    radius: targetKec ? 6.5 : 5,
                    fillColor: color,
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.95
                });

                marker.bindPopup(`
                    <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; padding: 4px; min-width: 200px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; margin-bottom: 4px;">
                            <span style="font-size: 9px; font-weight: 800; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; background: ${color}20; color: ${color};">
                                ${s.jenjang} &bull; ${s.status}
                            </span>
                            <span style="font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; background: #f1f5f9; color: #475569;">
                                Akr: ${s.akreditasi}
                            </span>
                        </div>
                        <strong style="display: block; font-size: 12px; color: #0f172a; line-height: 1.3;">${s.nama_sekolah}</strong>
                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">NPSN: <span style="font-family: monospace; font-weight: bold;">${s.npsn}</span></div>
                        <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid #e2e8f0; font-size: 11px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Peserta Didik:</span>
                            <strong style="color: #0f172a;">${totalMurid} siswa</strong>
                        </div>
                        <div style="margin-top: 2px; font-size: 10px; color: #94a3b8; font-style: italic;">
                            Kecamatan ${s.kecamatan?.nama || targetKec?.nama || '-'}
                        </div>
                    </div>
                `);

                marker.on('mouseover', function() {
                    this.setRadius(8.5);
                    this.setStyle({ weight: 3 });
                });
                marker.on('mouseout', function() {
                    this.setRadius(targetKec ? 6.5 : 5);
                    this.setStyle({ weight: 2 });
                });

                schoolMarkersMap[s.id] = marker;
                schoolLayerGroup.addLayer(marker);
            });

            // Update badge count in breadcrumb
            const bcCount = document.getElementById('drilldownSchoolCount');
            if (bcCount) {
                bcCount.innerText = `${listToRender.length} Titik Sekolah`;
            }
        }

        // Live Floating Hover HUD Handlers
        const hoverHud = document.getElementById('hoverInfoHud');
        const hudKecName = document.getElementById('hudKecName');
        const hudCapaianVal = document.getElementById('hudCapaianVal');
        const hudSekolahCount = document.getElementById('hudSekolahCount');

        function showHoverHud(kec) {
            if (!hoverHud || !kec) return;
            const val = getKecValue(kec);
            hudKecName.innerText = kec.nama;
            hudCapaianVal.innerText = val.toFixed(1) + '%';
            hudSekolahCount.innerText = `${kec.sekolah_count || 0} Satuan Pendidikan`;
            hoverHud.classList.remove('opacity-0', 'translate-y-[-4px]');
            hoverHud.classList.add('opacity-100', 'translate-y-0');
        }

        function hideHoverHud() {
            if (!hoverHud) return;
            hoverHud.classList.remove('opacity-100', 'translate-y-0');
            hoverHud.classList.add('opacity-0', 'translate-y-[-4px]');
        }

        // DRILLDOWN TO KECAMATAN ACTION
        function drilldownToKecamatan(kec, layer) {
            currentMode = 'drilldown';
            activeKec = kec;
            activeKecLayer = layer;

            // 1. Smooth animated camera flyTo bounding box with padding
            map.fitBounds(layer.getBounds(), {
                padding: [45, 45],
                maxZoom: 14,
                animate: true,
                duration: 0.9
            });

            // 2. Visual Spotlight: Highlight active polygon with subtle fill & dim other 19 kecamatan
            geojsonLayer.eachLayer(function(l) {
                if (l === layer) {
                    l.setStyle({
                        fillColor: getColor(getKecValue(kec)),
                        weight: 3.5,
                        color: '#1d4ed8', // vibrant cobalt outline
                        fillOpacity: 0.32 // lighter fill so school pins inside pop!
                    });
                } else {
                    l.setStyle({
                        fillColor: '#94a3b8',
                        weight: 1,
                        color: '#cbd5e1',
                        fillOpacity: 0.12 // dimmed out of focus
                    });
                }
            });

            // 3. Render only the schools that belong to this active kecamatan
            const currentJenjang = document.getElementById('selectSchoolJenjang')?.value || 'ALL';
            renderSchoolMarkers(kec, currentJenjang);

            // 4. Update Floating Breadcrumb
            const bc = document.getElementById('drilldownBreadcrumb');
            const bcName = document.getElementById('drilldownKecName');
            if (bc && bcName) {
                bcName.innerText = `Kecamatan ${kec.nama}`;
                bc.classList.remove('hidden');
                bc.classList.add('flex');
            }

            // 5. Update Dropdown and Right Panel
            const selectZoom = document.getElementById('selectKecamatanZoom');
            if (selectZoom) {
                selectZoom.value = kec.nama;
            }
            updateRightPanel(kec, true);
        }

        // RESET TO KABUPATEN ACTION
        function resetToKabupaten() {
            currentMode = 'kabupaten';
            activeKec = null;
            activeKecLayer = null;

            // 1. Zoom out back to Kabupaten bounds
            if (geojsonLayer) {
                map.fitBounds(geojsonLayer.getBounds(), {
                    animate: true,
                    duration: 0.8
                });

                // 2. Restore all 20 polygons to original choropleth
                geojsonLayer.eachLayer(function(l) {
                    const rawName = l.feature?.properties?.WADMKC || '';
                    const kec = kecMap[normalizeName(rawName)];
                    const val = getKecValue(kec);

                    l.setStyle({
                        fillColor: getColor(val),
                        weight: 1.8,
                        opacity: 1,
                        color: '#ffffff',
                        fillOpacity: 0.72
                    });
                });
            }

            // 3. Clear school markers in overview mode (keeps county view clean and pristine)
            schoolLayerGroup.clearLayers();

            // 4. Hide Breadcrumb
            const bc = document.getElementById('drilldownBreadcrumb');
            if (bc) {
                bc.classList.remove('flex');
                bc.classList.add('hidden');
            }

            // 5. Reset Dropdown & Right Panel
            const selectZoom = document.getElementById('selectKecamatanZoom');
            if (selectZoom) {
                selectZoom.value = '';
            }

            const firstKec = kecamatanData[0];
            if (firstKec) {
                updateRightPanel(firstKec, false);
            }
        }

        // Load GeoJSON Boundaries
        fetch('/geojson/peta_kecamatan.geojson')
            .then(res => res.json())
            .then(geojsonData => {
                geojsonLayer = L.geoJSON(geojsonData, {
                    pane: 'polygonsPane',
                    style: function(feature) {
                        const rawName = feature.properties?.WADMKC || '';
                        const kec = kecMap[normalizeName(rawName)];
                        const val = getKecValue(kec);

                        return {
                            fillColor: getColor(val),
                            weight: 1.8,
                            opacity: 1,
                            color: '#ffffff',
                            fillOpacity: 0.72
                        };
                    },
                    onEachFeature: function(feature, layer) {
                        const rawName = feature.properties?.WADMKC || 'Kecamatan';
                        const kec = kecMap[normalizeName(rawName)];
                        const val = getKecValue(kec);

                        layerByKecName[normalizeName(rawName)] = layer;

                        // Modern Custom Tooltip on Hover
                        layer.bindTooltip(`
                            <div style="font-size: 12px; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;">
                                ${rawName}
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 3px; font-size: 11px; color: #94a3b8;">
                                <span>Capaian:</span>
                                <strong style="color: #38bdf8;">${val.toFixed(1)}%</strong>
                            </div>
                            <div style="font-size: 10px; color: #cbd5e1; margin-top: 4px; padding-top: 4px; border-top: 1px solid rgba(255,255,255,0.15); font-style: italic;">
                                🎯 Klik untuk drilldown ke wilayah ini
                            </div>
                        `, {
                            sticky: true,
                            className: 'leaflet-tooltip-custom'
                        });

                        // Interactive Hover & Click Events
                        layer.on({
                            mouseover: function(e) {
                                const l = e.target;
                                if (currentMode === 'kabupaten') {
                                    l.setStyle({
                                        weight: 3.2,
                                        color: '#2563eb', // bright vibrant blue hover
                                        fillOpacity: 0.88
                                    });
                                    if (kec) {
                                        showHoverHud(kec);
                                        updateRightPanel(kec, false);
                                    }
                                } else if (l !== activeKecLayer) {
                                    // Hovering on another dimmed polygon while in drilldown
                                    l.setStyle({
                                        weight: 2,
                                        color: '#3b82f6',
                                        fillOpacity: 0.35
                                    });
                                    if (kec) showHoverHud(kec);
                                }
                            },
                            mouseout: function(e) {
                                const l = e.target;
                                hideHoverHud();

                                if (currentMode === 'kabupaten') {
                                    const rawN = feature.properties?.WADMKC || '';
                                    const k = kecMap[normalizeName(rawN)];
                                    const v = getKecValue(k);
                                    l.setStyle({
                                        fillColor: getColor(v),
                                        weight: 1.8,
                                        color: '#ffffff',
                                        fillOpacity: 0.72
                                    });
                                } else if (l !== activeKecLayer) {
                                    l.setStyle({
                                        fillColor: '#94a3b8',
                                        weight: 1,
                                        color: '#cbd5e1',
                                        fillOpacity: 0.12
                                    });
                                }
                            },
                            click: function(e) {
                                if (document.activeElement && typeof document.activeElement.blur === 'function') {
                                    document.activeElement.blur();
                                }
                                if (e.originalEvent && e.originalEvent.target && typeof e.originalEvent.target.blur === 'function') {
                                    e.originalEvent.target.blur();
                                }
                                if (kec) {
                                    drilldownToKecamatan(kec, e.target);
                                }
                            }
                        });
                    }
                }).addTo(map);

                // Initial view bounds to all 20 kecamatan
                map.fitBounds(geojsonLayer.getBounds());

                // Set initial right panel to first kecamatan
                if (kecamatanData.length > 0) {
                    updateRightPanel(kecamatanData[0], false);
                }
            })
            .catch(err => {
                console.error('Gagal memuat GeoJSON batas kecamatan:', err);
            });

        // Event Listeners for Back to Kabupaten Buttons
        const btnBackBreadcrumb = document.getElementById('btnBackToKabupaten');
        if (btnBackBreadcrumb) {
            btnBackBreadcrumb.addEventListener('click', resetToKabupaten);
        }

        const btnPanelBack = document.getElementById('btnPanelBackToKabupaten');
        if (btnPanelBack) {
            btnPanelBack.addEventListener('click', resetToKabupaten);
        }

        const btnResetZoom = document.getElementById('btnResetView');
        if (btnResetZoom) {
            btnResetZoom.addEventListener('click', resetToKabupaten);
        }

        // Focus / Drilldown via Dropdown
        const selectZoom = document.getElementById('selectKecamatanZoom');
        if (selectZoom) {
            selectZoom.addEventListener('change', function(e) {
                const name = e.target.value;
                if (!name) {
                    resetToKabupaten();
                    return;
                }
                const norm = normalizeName(name);
                const layer = layerByKecName[norm];
                const kec = kecMap[norm];
                if (layer && kec) {
                    drilldownToKecamatan(kec, layer);
                }
            });
        }

        // Filter Jenjang School Marker
        const selectJenjang = document.getElementById('selectSchoolJenjang');
        if (selectJenjang) {
            selectJenjang.addEventListener('change', function(e) {
                const jenjang = e.target.value;
                if (currentMode === 'drilldown' && activeKec) {
                    renderSchoolMarkers(activeKec, jenjang);
                }
            });
        }
    });
</script>
@endpush
