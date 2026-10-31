<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - SIMAK DINDIKPORA Banjarnegara</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-banjarnegara.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS for GIS Peta Sebaran -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Alpine.js for lightweight UI interactivity -->
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    
    <!-- Chart.js for beautiful analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full antialiased text-slate-800 flex flex-col lg:flex-row bg-slate-50/80 w-full max-w-full overflow-x-hidden" x-data="{ sidebarOpen: false, profileOpen: false, profileModal: false, settingsModal: false, exportModal: false, dapodikModal: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-cloak
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 lg:hidden" 
         @click="sidebarOpen = false"></div>

    <!-- Light Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-white text-slate-700 flex flex-col transition-transform duration-300 ease-in-out border-r border-slate-200 shadow-sm">
        
        <!-- Brand Header (Proportionate 72px) -->
        <a href="{{ route('dashboard') }}" class="h-[72px] flex items-center px-5 border-b border-slate-100 bg-white gap-3.5 hover:bg-slate-50/60 transition group">
            <div class="w-11 h-11 rounded-xl bg-white border border-slate-200/90 p-1 flex items-center justify-center shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                <img src="{{ asset('images/logo-banjarnegara.png') }}" alt="Logo Kab. Banjarnegara" class="w-full h-full object-contain">
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="font-extrabold text-lg tracking-tight text-slate-900 group-hover:text-blue-700 transition">SIMAK</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-200">v2.5</span>
                </div>
                <p class="text-[11px] text-slate-400 truncate max-w-[155px] font-medium leading-tight">DINDIKPORA Banjarnegara</p>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-5 space-y-6 text-sm scrollbar-thin scrollbar-thumb-slate-200">
            <!-- Utama & Master Data -->
            @if(auth()->user()->canAccessMenu('dashboard') || auth()->user()->canAccessAnyMenu(['sekolah', 'siswa', 'guru', 'tujuan1', 'tujuan2']))
            <div>
                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Ringkasan & Master Data</p>
                <div class="mt-2 space-y-1">
                    @if(auth()->user()->canAccessMenu('dashboard'))
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard Kinerja</span>
                    </a>
                    @endif

                    <!-- Submenu: Master Data -->
                    @if(auth()->user()->canAccessAnyMenu(['sekolah', 'siswa', 'guru', 'tujuan1', 'tujuan2']))
                    <div x-data="{ masterDataOpen: {{ request()->routeIs('tujuan1.*', 'tujuan2.*', 'sekolah.*', 'siswa.*', 'guru.*') ? 'true' : 'false' }} }" class="pt-0.5">
                        <button type="button" 
                                @click="masterDataOpen = !masterDataOpen"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium transition cursor-pointer {{ request()->routeIs('tujuan1.*', 'tujuan2.*', 'sekolah.*', 'siswa.*', 'guru.*') ? 'bg-blue-50 text-blue-800 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 {{ request()->routeIs('tujuan1.*', 'tujuan2.*', 'sekolah.*', 'siswa.*', 'guru.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7" />
                                </svg>
                                <span>Master Data</span>
                            </div>
                            <svg :class="masterDataOpen ? 'rotate-180' : ''" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Submenu Items -->
                        <div x-show="masterDataOpen" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="mt-1 pl-3.5 ml-4 border-l-2 border-slate-200/80 space-y-1 py-1">
                            
                            <!-- 1. Satuan Pendidikan -->
                            @if(auth()->user()->canAccessMenu('sekolah'))
                            <a href="{{ route('sekolah.index') }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('sekolah.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sekolah.*') ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-slate-300' }}"></span>
                                <span>Satuan Pendidikan (Sekolah)</span>
                            </a>
                            @endif

                            <!-- 2. Partisipasi Siswa -->
                            @if(auth()->user()->canAccessMenu('siswa'))
                            <a href="{{ route('siswa.index') }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('siswa.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('siswa.*') ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-slate-300' }}"></span>
                                <span>Partisipasi Siswa (Dapodik)</span>
                            </a>
                            @endif

                            <!-- 3. Guru & Pendidik -->
                            @if(auth()->user()->canAccessMenu('guru'))
                            <a href="{{ route('guru.index') }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('guru.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('guru.*') ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-slate-300' }}"></span>
                                <span>Data Guru & Sertifikasi</span>
                            </a>
                            @endif

                            <!-- 4. Tujuan 1: Penduduk Usia Sekolah -->
                            @if(auth()->user()->canAccessMenu('tujuan1'))
                            <a href="{{ route('tujuan1.index') }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('tujuan1.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('tujuan1.*') ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-slate-300' }}"></span>
                                <span>Tujuan 1: Penduduk Usia Sekolah</span>
                            </a>
                            @endif

                            <!-- 5. Tujuan 2: Prestasi Siswa -->
                            @if(auth()->user()->canAccessMenu('tujuan2'))
                            <a href="{{ route('tujuan2.index') }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('tujuan2.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('tujuan2.*') ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-slate-300' }}"></span>
                                <span>Tujuan 2: Prestasi Siswa</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Hasil Indikator Kinerja -->
            @if(auth()->user()->canAccessAnyMenu(['indikator_apk', 'indikator_apm', 'indikator_aps', 'indikator_guru', 'indikator_paud']))
            <div>
                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Hasil & Indikator Kinerja</p>
                <div class="mt-2 space-y-1">
                    @if(auth()->user()->canAccessMenu('indikator_apk'))
                    <a href="{{ route('indikator.apk') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('indikator.apk') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('indikator.apk') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span>APK (Partisipasi Kasar)</span>
                    </a>
                    @endif

                    @if(auth()->user()->canAccessMenu('indikator_apm'))
                    <a href="{{ route('indikator.apm') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('indikator.apm') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('indikator.apm') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span>APM (Partisipasi Murni)</span>
                    </a>
                    @endif

                    @if(auth()->user()->canAccessMenu('indikator_aps'))
                    <a href="{{ route('indikator.aps') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('indikator.aps') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('indikator.aps') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>APS (Partisipasi Sekolah)</span>
                    </a>
                    @endif

                    @if(auth()->user()->canAccessMenu('indikator_guru'))
                    <a href="{{ route('indikator.guru') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('indikator.guru') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('indikator.guru') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Sertifikasi Guru</span>
                    </a>
                    @endif

                    @if(auth()->user()->canAccessMenu('indikator_paud'))
                    <a href="{{ route('indikator.paud') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('indikator.paud') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('indikator.paud') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        <span>Akreditasi PAUD (Min. B)</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Perencanaan & Akuntabilitas -->
            @if(auth()->user()->canAccessAnyMenu(['renstra_target', 'renstra_realisasi', 'laporan_lkjip']))
            <div>
                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Renstra & Akuntabilitas</p>
                <div class="mt-2 space-y-1">
                    @if(auth()->user()->canAccessMenu('renstra_target'))
                    <a href="{{ route('renstra.target') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('renstra.target') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('renstra.target') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Target Renstra (2025–2029)</span>
                    </a>
                    @endif

                    @if(auth()->user()->canAccessMenu('renstra_realisasi'))
                    <a href="{{ route('renstra.realisasi') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('renstra.realisasi') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('renstra.realisasi') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Realisasi Capaian Kinerja</span>
                    </a>
                    @endif

                    @if(auth()->user()->canAccessMenu('laporan_lkjip'))
                    <a href="{{ route('laporan.lkjip') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('laporan.lkjip') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('laporan.lkjip') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Dokumen LKjIP Resmi (A4)</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Peta & Ekspor Data -->
            @if(auth()->user()->canAccessAnyMenu(['peta', 'export']))
            <div>
                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Peta & Ekspor</p>
                <div class="mt-2 space-y-1">
                    <!-- Submenu: Peta & Ekspor -->
                    <div x-data="{ petaEksporOpen: {{ request()->routeIs('peta.*') ? 'true' : 'false' }} }" class="pt-0.5">
                        <button type="button" 
                                @click="petaEksporOpen = !petaEksporOpen"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium transition cursor-pointer {{ request()->routeIs('peta.*') ? 'bg-blue-50 text-blue-800 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 {{ request()->routeIs('peta.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Peta & Ekspor</span>
                            </div>
                            <svg :class="petaEksporOpen ? 'rotate-180' : ''" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Submenu Items -->
                        <div x-show="petaEksporOpen" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="mt-1 pl-3.5 ml-4 border-l-2 border-slate-200/80 space-y-1 py-1">
                            
                            <!-- 1. Peta Sebaran 20 Kecamatan -->
                            @if(auth()->user()->canAccessMenu('peta'))
                            <a href="{{ route('peta.index') }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('peta.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('peta.*') ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-slate-300' }}"></span>
                                <span>Peta Sebaran 20 Kecamatan</span>
                            </a>
                            @endif

                            <!-- 2. Unduh Data Excel (.xlsx) -->
                            @if(auth()->user()->canAccessMenu('export'))
                            <button type="button" 
                                    @click="exportModal = true" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100/70 hover:text-slate-900 transition text-left cursor-pointer group">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-emerald-500 transition-colors"></span>
                                    <span>Unduh Data Excel</span>
                                </div>
                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">.XLSX</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Pengaturan & Tata Kelola (Urutan Paling Bawah) -->
            @if(auth()->user()->canAccessAnyMenu(['pengaturan', 'manajemen_pengguna', 'audit', 'panduan']))
            <div>
                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pengaturan & Sistem</p>
                <div class="mt-2 space-y-1">
                    <!-- Submenu: Pengaturan Aplikasi -->
                    <div x-data="{ pengaturanOpen: {{ request()->routeIs('audit.*', 'panduan.*', 'users.*') ? 'true' : 'false' }} }" class="pt-0.5">
                        <button type="button" 
                                @click="pengaturanOpen = !pengaturanOpen"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium transition cursor-pointer {{ request()->routeIs('audit.*', 'panduan.*', 'users.*') ? 'bg-blue-50 text-blue-800 font-semibold border border-blue-100/80 shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 {{ request()->routeIs('audit.*', 'panduan.*', 'users.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Pengaturan Aplikasi</span>
                            </div>
                            <svg :class="pengaturanOpen ? 'rotate-180' : ''" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Submenu Items -->
                        <div x-show="pengaturanOpen" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="mt-1 pl-3.5 ml-4 border-l-2 border-slate-200/80 space-y-1 py-1">
                            
                            <!-- 1. Pengaturan Aplikasi Modal -->
                            @if(auth()->user()->canAccessMenu('pengaturan'))
                            <button type="button" 
                                    @click="settingsModal = true" 
                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100/70 hover:text-slate-900 transition text-left cursor-pointer group">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-blue-600 transition-colors"></span>
                                <span>Pengaturan Aplikasi</span>
                            </button>
                            @endif

                            <!-- 2. Manajemen Pengguna -->
                            @if(auth()->user()->canAccessMenu('manajemen_pengguna'))
                            <a href="{{ route('users.index') }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('users.*') ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-slate-300' }}"></span>
                                <span>Manajemen Pengguna</span>
                            </a>
                            @endif

                            <!-- 3. Log System -->
                            @if(auth()->user()->canAccessMenu('audit'))
                            <a href="{{ route('audit.index') }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('audit.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('audit.*') ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-slate-300' }}"></span>
                                <span>Log System</span>
                            </a>
                            @endif

                            <!-- 4. Panduan & SOP SAKIP -->
                            @if(auth()->user()->canAccessMenu('panduan'))
                            <a href="{{ route('panduan.index') }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('panduan.*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/60 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/70 hover:text-slate-900' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('panduan.*') ? 'bg-blue-600 ring-2 ring-blue-300' : 'bg-slate-300' }}"></span>
                                <span>Panduan & SOP SAKIP</span>
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Tombol Tunggal Sinkronisasi Dapodik (Di Bawah Menu Pengaturan Aplikasi) -->
                    @if(auth()->user()->canAccessAnyMenu(['sekolah', 'siswa', 'guru', 'pengaturan']))
                    <div class="pt-2">
                        <button type="button"
                                @click="dapodikModal = true; sidebarOpen = false"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-bold text-xs bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition cursor-pointer group">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-white group-hover:rotate-180 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Sinkronisasi Dapodik</span>
                            </div>
                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-white/20 text-white">SYNC</span>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </nav>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/70">
            <div class="flex items-center justify-between gap-3">
                <button type="button" @click="profileModal = true" class="flex items-center gap-3 overflow-hidden text-left hover:opacity-80 transition cursor-pointer group">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0 border border-blue-200 group-hover:ring-2 group-hover:ring-blue-300 transition">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-slate-900 truncate group-hover:text-blue-700 transition">{{ auth()->user()->name ?? 'User SIMAK' }}</p>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ auth()->user()->role_badge ?? 'bg-blue-100 text-blue-700' }}">
                            {{ auth()->user()->role_label ?? 'Internal' }}
                        </span>
                    </div>
                </button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            title="Keluar Sistem"
                            class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-72 flex flex-col min-h-screen w-full max-w-full min-w-0 overflow-x-hidden">
        
        <!-- Top Navbar Header (Proportionate 72px) -->
        <header class="h-[72px] bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 px-3 sm:px-6 lg:px-8 flex items-center justify-between transition-all w-full max-w-full min-w-0 gap-2">
            <div class="flex items-center gap-2 sm:gap-3.5 min-w-0 flex-1">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition cursor-pointer shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="min-w-0 flex-1">
                    <h1 class="text-sm sm:text-base md:text-lg font-bold text-slate-900 tracking-tight leading-tight truncate">{{ $title ?? 'Sistem Informasi Akuntabilitas Kinerja Pendidikan' }}</h1>
                    <p class="text-[11px] text-slate-500 hidden sm:block font-medium truncate">Dinas Pendidikan, Kepemudaan dan Olahraga Kab. Banjarnegara</p>
                </div>
            </div>

            <!-- Header Quick Actions & User Profile Dropdown -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <!-- Tahun Anggaran Selector -->
                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    @foreach(request()->except('tahun') as $key => $val)
                        @if(!is_array($val))
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <label for="filter-tahun" class="text-xs font-semibold text-slate-400 hidden xl:inline">Tahun:</label>
                    <select id="filter-tahun" 
                            name="tahun" 
                            onchange="this.form.submit()" 
                            class="bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-800 text-xs font-semibold rounded-xl px-3 py-1.5 outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer transition shadow-2xs">
                        @php
                            $currentSelectedTahun = (int) ($activeTahunGlobal ?? $tahun ?? request('tahun', session('tahun_anggaran', date('Y'))));
                            $tahunOptionsList = $availableTahunOptions ?? \App\Http\Controllers\Controller::availableTahunOptions();
                        @endphp
                        @foreach($tahunOptionsList as $opt)
                            <option value="{{ $opt['tahun'] }}" {{ $currentSelectedTahun === (int) $opt['tahun'] ? 'selected' : '' }}>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- Divider -->
                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                <!-- User Profile Dropdown (Pojok Kanan Atas Top Nav) -->
                <div class="relative" @click.away="profileOpen = false">
                    <!-- Dropdown Trigger Button -->
                    <button type="button" 
                            @click="profileOpen = !profileOpen" 
                            id="profile-dropdown-button"
                            aria-haspopup="true"
                            :aria-expanded="profileOpen"
                            class="flex items-center gap-2.5 pl-1.5 pr-2.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition shadow-2xs group cursor-pointer">
                        
                        <!-- User Avatar Circle with Initial -->
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 via-indigo-600 to-sky-500 text-white flex items-center justify-center font-bold text-xs shadow-xs ring-1 ring-blue-100 group-hover:scale-105 transition-transform shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>

                        <!-- User Info (Hidden on small mobile) -->
                        <div class="hidden md:flex flex-col text-left">
                            <span class="text-xs font-bold text-slate-800 truncate max-w-[130px] leading-tight">
                                {{ auth()->user()->name ?? 'Pengguna SIMAK' }}
                            </span>
                            <span class="text-[10px] font-medium text-blue-600">
                                {{ auth()->user()->role_label ?? 'Internal' }}
                            </span>
                        </div>

                        <!-- Chevron Down Icon -->
                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 shrink-0 ml-0.5" 
                             :class="profileOpen ? 'rotate-180 text-blue-600' : ''" 
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu Card -->
                    <div x-show="profileOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         class="absolute right-0 mt-2 w-76 bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/90 py-2 z-50 divide-y divide-slate-100">
                        
                        <!-- Header Detail Akun -->
                        <div class="px-4 py-3 bg-slate-50/70">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-base shadow-sm ring-2 ring-white shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="overflow-hidden min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-900 truncate leading-snug">
                                        {{ auth()->user()->name ?? 'User SIMAK' }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 truncate font-medium">
                                        {{ auth()->user()->email ?? 'user@dindikpora.banjarnegarakab.go.id' }}
                                    </p>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold {{ auth()->user()->role_badge ?? 'bg-blue-100 text-blue-700' }}">
                                            {{ auth()->user()->role_label ?? 'Internal' }}
                                        </span>
                                        @if(auth()->user()->nip)
                                            <span class="text-[10px] text-slate-500 font-mono truncate">NIP. {{ auth()->user()->nip }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Items: Profil -->
                        <div class="py-1.5">
                            <!-- Menu 1: Profil -->
                            <button type="button" 
                                    @click="profileModal = true; profileOpen = false"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-left text-slate-700 hover:text-blue-700 hover:bg-blue-50/70 transition group cursor-pointer">
                                <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-blue-100 group-hover:text-blue-700 flex items-center justify-center text-slate-500 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 group-hover:text-blue-700">Profil</p>
                                    <p class="text-[11px] text-slate-400 group-hover:text-slate-500 truncate">Identitas, NIP & kata sandi</p>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 group-hover:translate-x-0.5 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Menu 3: Logout -->
                        <div class="p-1.5">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-left text-rose-600 hover:bg-rose-50 transition group font-medium text-xs cursor-pointer">
                                    <div class="w-8 h-8 rounded-xl bg-rose-50 group-hover:bg-rose-100 flex items-center justify-center text-rose-600 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <span class="font-bold text-rose-600">Logout</span>
                                        <p class="text-[10px] text-rose-400">Keluar dari sesi SIMAK</p>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mx-6 sm:mx-8 mt-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold text-sm">&times;</button>
            </div>
        @endif

        @if(session('info'))
            <div class="mx-6 sm:mx-8 mt-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-blue-600 hover:text-blue-900 font-bold text-sm">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-6 sm:mx-8 mt-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
                <p class="font-bold mb-1">Terdapat beberapa kesalahan pengisian:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page Dynamic Content -->
        <main class="flex-1 p-3 sm:p-6 lg:p-8 w-full max-w-full min-w-0 overflow-x-hidden">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="px-4 sm:px-6 lg:px-8 py-5 border-t border-slate-200 bg-white text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-3 w-full max-w-full min-w-0 overflow-x-hidden">
            <p>&copy; {{ date('Y') }} <strong class="text-slate-700">SIMAK Pendidikan</strong> &mdash; Dinas Pendidikan, Kepemudaan dan Olahraga Kab. Banjarnegara. Dibuat oleh <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Exadata</a></p>
            <div class="flex items-center gap-4">
                <span>Standar SAKIP & Permendikbudristek SPM</span>
                <span class="text-slate-300">&bull;</span>
                <span>Basis 20 Kecamatan</span>
            </div>
        </footer>
    </div>

    <!-- Modal: Profil Pengguna -->
    <div x-show="profileModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <!-- Backdrop -->
        <div x-show="profileModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"
             @click="profileModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="profileModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-600 px-6 py-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Profil Pengguna SIMAK</h3>
                            <p class="text-xs text-blue-100">Perbarui identitas kedinasan dan keamanan akun</p>
                        </div>
                    </div>
                    <button type="button" @click="profileModal = false" class="text-white/80 hover:text-white rounded-xl p-1.5 hover:bg-white/10 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form action="{{ route('profile.update') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama Lengkap -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   required 
                                   value="{{ old('name', auth()->user()->name ?? '') }}" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <!-- Email Kedinasan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Kedinasan <span class="text-rose-500">*</span></label>
                            <input type="email" 
                                   name="email" 
                                   required 
                                   value="{{ old('email', auth()->user()->email ?? '') }}" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <!-- NIP Pegawai -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIP Pegawai</label>
                            <input type="text" 
                                   name="nip" 
                                   value="{{ old('nip', auth()->user()->nip ?? '') }}" 
                                   placeholder="Contoh: 198503202010012014" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <!-- Jabatan / Tugas -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jabatan Kedinasan</label>
                            <input type="text" 
                                   name="jabatan" 
                                   value="{{ old('jabatan', auth()->user()->jabatan ?? '') }}" 
                                   placeholder="Contoh: Analis SAKIP / Perencana" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <!-- Role Sistem (Readonly) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hak Akses Role</label>
                            <input type="text" 
                                   readonly 
                                   value="{{ strtoupper(auth()->user()->role ?? 'INTERNAL') }}" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-600 text-sm font-bold cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Separator Keamanan Sandi -->
                    <div class="pt-3 border-t border-slate-100">
                        <p class="text-xs font-bold text-slate-700 mb-2">Ganti Kata Sandi (Kosongkan jika tidak ingin mengubah):</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Password Baru</label>
                                <input type="password" 
                                       name="password" 
                                       placeholder="Minimal 8 karakter" 
                                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Konfirmasi Password Baru</label>
                                <input type="password" 
                                       name="password_confirmation" 
                                       placeholder="Ulangi password baru" 
                                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="profileModal = false" 
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Pengaturan Aplikasi -->
    <div x-show="settingsModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <!-- Backdrop -->
        <div x-show="settingsModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"
             @click="settingsModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="settingsModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-sky-600 px-6 py-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Pengaturan Aplikasi SIMAK</h3>
                            <p class="text-xs text-indigo-100">Konfigurasi parameter umum sistem akuntabilitas kinerja</p>
                        </div>
                    </div>
                    <button type="button" @click="settingsModal = false" class="text-white/80 hover:text-white rounded-xl p-1.5 hover:bg-white/10 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form action="{{ route('settings.update') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Instansi / Dinas <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="nama_instansi" 
                               required 
                               value="{{ session('app_settings.nama_instansi', 'Dinas Pendidikan, Kepemudaan dan Olahraga Kab. Banjarnegara') }}" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Periode Renstra <span class="text-rose-500">*</span></label>
                            <input type="text" 
                                   name="periode_renstra" 
                                   required 
                                   value="{{ session('app_settings.periode_renstra', '2025–2029') }}" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tahun Kinerja Acuan <span class="text-rose-500">*</span></label>
                            <input type="number" 
                                   name="tahun_evaluasi" 
                                   required 
                                   min="2020" 
                                   max="2035" 
                                   value="{{ session('app_settings.tahun_evaluasi', 2025) }}" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Metode Perhitungan Indikator APK <span class="text-rose-500">*</span></label>
                        <select name="rumus_apk_mode" 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            <option value="resmi_kemendikbud" {{ session('app_settings.rumus_apk_mode') === 'resmi_kemendikbud' ? 'selected' : '' }}>
                                Resmi Kemendikbudristek (Siswa Jenjang / Penduduk Usia Resmi × 100%)
                            </option>
                            <option value="agregat_total" {{ session('app_settings.rumus_apk_mode') === 'agregat_total' ? 'selected' : '' }}>
                                Agregat Total Wilayah (Total Partisipasi Semua Wilayah)
                            </option>
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Standar resmi memperhitungkan siswa dari usia di luar kelompok resmi (inklusi APK).</p>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="settingsModal = false" 
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Pengaturan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pusat Unduh Data Excel (.XLSX) Modal -->
    <div x-show="exportModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-2xl w-full shadow-2xl border border-slate-100 space-y-6 my-8"
             @click.away="exportModal = false">
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-xs shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-lg text-slate-900">Pusat Unduh Data Excel (.xlsx)</h3>
                        <p class="text-xs text-slate-500">Seluruh data diformat rapi dengan judul resmi, styling tabel, dan auto-kolom Microsoft Excel.</p>
                    </div>
                </div>
                <button type="button" @click="exportModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1">&times;</button>
            </div>

            <!-- List of Excel Download Options -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <!-- 1. Rekap Kinerja Kecamatan -->
                <a href="{{ route('export.kecamatan') }}" 
                   class="p-4 rounded-2xl border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/40 transition group flex flex-col justify-between space-y-3">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 group-hover:text-emerald-700">Rekap Kinerja 20 Kecamatan</span>
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">.XLSX</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Capaian APK, APM, APS per jenjang, sertifikasi guru, dan PAUD per kecamatan.</p>
                    </div>
                    <div class="flex items-center text-emerald-700 font-bold text-[11px] gap-1 group-hover:translate-x-1 transition-transform">
                        <span>Unduh File Excel</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </a>

                <!-- 2. Satuan Pendidikan -->
                <a href="{{ route('export.sekolah') }}" 
                   class="p-4 rounded-2xl border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/40 transition group flex flex-col justify-between space-y-3">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 group-hover:text-blue-700">Data Satuan Pendidikan</span>
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">.XLSX</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Master data sekolah se-Banjarnegara, NPSN, akreditasi, status, alamat, & kepala sekolah.</p>
                    </div>
                    <div class="flex items-center text-blue-700 font-bold text-[11px] gap-1 group-hover:translate-x-1 transition-transform">
                        <span>Unduh File Excel</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </a>

                <!-- 3. Partisipasi Siswa -->
                <a href="{{ route('export.siswa') }}" 
                   class="p-4 rounded-2xl border border-slate-200/80 hover:border-indigo-300 hover:bg-indigo-50/40 transition group flex flex-col justify-between space-y-3">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 group-hover:text-indigo-700">Partisipasi Siswa (Dapodik)</span>
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800">.XLSX</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Rombongan belajar, murid total per sekolah, serta distribusi kelompok usia 5-18 tahun.</p>
                    </div>
                    <div class="flex items-center text-indigo-700 font-bold text-[11px] gap-1 group-hover:translate-x-1 transition-transform">
                        <span>Unduh File Excel</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </a>

                <!-- 4. Data Guru & Pendidik -->
                <a href="{{ route('export.guru') }}" 
                   class="p-4 rounded-2xl border border-slate-200/80 hover:border-purple-300 hover:bg-purple-50/40 transition group flex flex-col justify-between space-y-3">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 group-hover:text-purple-700">Data Guru & Tenaga Pendidik</span>
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-purple-100 text-purple-800">.XLSX</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Daftar 5.000+ pendidik, NUPTK, NIP, status kepegawaian (PNS/PPPK/GTT), & sertifikasi.</p>
                    </div>
                    <div class="flex items-center text-purple-700 font-bold text-[11px] gap-1 group-hover:translate-x-1 transition-transform">
                        <span>Unduh File Excel</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </a>

                <!-- 5. Penduduk Usia Sekolah -->
                <a href="{{ route('export.penduduk') }}" 
                   class="p-4 rounded-2xl border border-slate-200/80 hover:border-sky-300 hover:bg-sky-50/40 transition group flex flex-col justify-between space-y-3">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 group-hover:text-sky-700">Penduduk Usia Sekolah</span>
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-sky-100 text-sky-800">.XLSX</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Data demografi kependudukan per kelompok usia (PAUD, SD, SMP, SMA) 20 kecamatan.</p>
                    </div>
                    <div class="flex items-center text-sky-700 font-bold text-[11px] gap-1 group-hover:translate-x-1 transition-transform">
                        <span>Unduh File Excel</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </a>

                <!-- 6. Prestasi Siswa -->
                <a href="{{ route('export.prestasi') }}" 
                   class="p-4 rounded-2xl border border-slate-200/80 hover:border-amber-300 hover:bg-amber-50/40 transition group flex flex-col justify-between space-y-3">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 group-hover:text-amber-700">Rekap Prestasi Siswa</span>
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">.XLSX</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">Rekam jejak kejuaraan lomba sains, olahraga, seni tingkat kabupaten hingga internasional.</p>
                    </div>
                    <div class="flex items-center text-amber-700 font-bold text-[11px] gap-1 group-hover:translate-x-1 transition-transform">
                        <span>Unduh File Excel</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                </a>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-[11px] text-slate-400">Format file resmi standar Microsoft Office (.xlsx)</p>
                <button type="button" 
                        @click="exportModal = false" 
                        class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Global Sinkronisasi Dapodik Banjarnegara -->
    @if(auth()->check() && auth()->user()->canAccessAnyMenu(['sekolah', 'siswa', 'guru', 'pengaturan']))
    <x-dapodik-sync-modal defaultTarget="semua" :tahun="$activeTahunGlobal ?? $tahun ?? (int) date('Y')" />
    @endif

    <!-- Back to Top Floating Action Button -->
    <div x-data="{ 
            showBackToTop: false,
            scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
         }"
         x-init="showBackToTop = (window.pageYOffset || document.documentElement.scrollTop) > 280"
         @scroll.window="showBackToTop = (window.pageYOffset || document.documentElement.scrollTop) > 280"
         class="fixed bottom-6 right-6 z-40 pointer-events-none">
        <button type="button"
                x-show="showBackToTop"
                x-cloak
                @click="scrollToTop()"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-90"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-90"
                class="pointer-events-auto group relative flex items-center gap-2 p-3 sm:px-4 sm:py-2.5 rounded-full bg-white/95 hover:bg-blue-600 text-slate-700 hover:text-white border border-slate-200/90 hover:border-blue-600 shadow-lg shadow-slate-900/10 hover:shadow-xl hover:shadow-blue-600/25 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 active:translate-y-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:ring-offset-2"
                title="Kembali ke atas"
                aria-label="Kembali ke atas">
            <svg class="w-5 h-5 transition-transform duration-300 group-hover:-translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            <span class="hidden sm:inline text-xs font-bold tracking-tight">Ke Atas</span>
        </button>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @stack('scripts')
</body>
</html>
