@extends('layouts.app', ['title' => 'Data Satuan Pendidikan'])

@section('content')
<div class="space-y-8" 
     x-data="{ 
        createModal: false, 
        editModal: false, 
        deleteModal: false, 
        importModal: false, 
        dapodikModal: false, 
        activeRow: {} 
     }"
     x-init="
        $watch('createModal', val => {
            if (val) {
                setTimeout(() => {
                    if (window.createSchoolPicker) {
                        window.createSchoolPicker.open('', '', document.getElementById('create_kecamatan_id')?.value);
                    }
                }, 150);
            }
        });
        $watch('editModal', val => {
            if (val) {
                setTimeout(() => {
                    if (window.editSchoolPicker) {
                        window.editSchoolPicker.open(activeRow?.latitude, activeRow?.longitude, activeRow?.kecamatan_id);
                    }
                }, 150);
            }
        });
     ">

    <!-- Header Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-blue-50/80 via-slate-50 to-white text-slate-900 flex flex-col xl:flex-row xl:items-center justify-between gap-5 shadow-xs border border-blue-200/60">
        <div class="space-y-1.5 flex-1 min-w-0 pr-2">
            <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Data Basic Satuan Pendidikan (Sekolah)</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kelola data pokok sekolah, NPSN resmi, jenjang (PAUD s.d. SMA/SMK), status kelembagaan, peringkat akreditasi BAN-PDM, dan wilayah kerja kecamatan di Kabupaten Banjarnegara.
            </p>
        </div>
        
        <!-- Action Row (1 Row) -->
        <div class="flex items-center gap-2.5 shrink-0 flex-nowrap overflow-x-auto pb-1 xl:pb-0">
            <button type="button" 
                    @click="dapodikModal = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs shadow-sm shadow-indigo-500/25 transition whitespace-nowrap shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Sinkron Dapodik</span>
            </button>

            <button type="button" 
                    @click="importModal = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm shadow-emerald-500/20 transition whitespace-nowrap shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Import CSV / Excel</span>
            </button>

            <button type="button" 
                    @click="createModal = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/25 transition whitespace-nowrap shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Sekolah Baru</span>
            </button>

            <a href="{{ route('export.sekolah') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold text-xs shadow-2xs transition whitespace-nowrap shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Total Lembaga</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
            <p class="text-[10px] text-slate-500">Semua Jenjang</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Negeri</span>
            <div class="text-xl font-extrabold text-blue-600 mt-1">{{ number_format($stats['negeri']) }}</div>
            <p class="text-[10px] text-slate-500">Sekolah Negeri</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Swasta</span>
            <div class="text-xl font-extrabold text-indigo-600 mt-1">{{ number_format($stats['swasta']) }}</div>
            <p class="text-[10px] text-slate-500">Sekolah Swasta/Yayasan</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Akreditasi A</span>
            <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['akr_a']) }}</div>
            <p class="text-[10px] text-slate-500">Unggul (Sangat Baik)</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Akreditasi B</span>
            <div class="text-xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['akr_b']) }}</div>
            <p class="text-[10px] text-slate-500">Baik</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Minimal B</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">
                {{ $stats['total'] > 0 ? round(($stats['akr_min_b'] / $stats['total']) * 100, 1) : 0 }}%
            </div>
            <p class="text-[10px] text-emerald-600 font-bold">Standar SPM Renstra</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('sekolah.index') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-600 uppercase text-[10px] tracking-wider">Filter Data:</span>
            </div>
            <div>
                <select name="jenjang" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Jenjang</option>
                    @foreach(['PAUD', 'TK', 'SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PKBM'] as $j)
                        <option value="{{ $j }}" {{ $filterJenjang === $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="kecamatan_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kecamatan (20 Wilayah)</option>
                    @foreach($kecamatanList as $k)
                        <option value="{{ $k->id }}" {{ $filterKecamatan == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-blue-500">
                    <option value="">Status (Semua)</option>
                    <option value="Negeri" {{ $filterStatus === 'Negeri' ? 'selected' : '' }}>Negeri</option>
                    <option value="Swasta" {{ $filterStatus === 'Swasta' ? 'selected' : '' }}>Swasta</option>
                </select>
            </div>
            <div>
                <select name="akreditasi" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-blue-500">
                    <option value="">Akreditasi (Semua)</option>
                    <option value="A" {{ $filterAkreditasi === 'A' ? 'selected' : '' }}>Akreditasi A</option>
                    <option value="B" {{ $filterAkreditasi === 'B' ? 'selected' : '' }}>Akreditasi B</option>
                    <option value="C" {{ $filterAkreditasi === 'C' ? 'selected' : '' }}>Akreditasi C</option>
                    <option value="Belum Terakreditasi" {{ $filterAkreditasi === 'Belum Terakreditasi' ? 'selected' : '' }}>Belum Terakreditasi</option>
                </select>
            </div>
            @if($filterJenjang || $filterKecamatan || $filterStatus || $filterAkreditasi)
                <a href="{{ route('sekolah.index') }}" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold transition">
                    &times; Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Satuan Pendidikan di Banjarnegara</h3>
                <p class="text-xs text-slate-500">Menampilkan {{ number_format($sekolahList->total()) }} lembaga pendidikan aktif</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" 
                   data-server-side="true" 
                   data-url="{{ route('sekolah.index') }}" 
                   data-total="{{ $sekolahList->total() }}" 
                   data-current-page="{{ $sekolahList->currentPage() }}" 
                   data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4 font-mono" data-column="npsn">NPSN</th>
                        <th class="py-3.5 px-4" data-column="nama_sekolah">Nama Sekolah</th>
                        <th class="py-3.5 px-4 text-center" data-column="jenjang">Jenjang</th>
                        <th class="py-3.5 px-4 text-center" data-column="status">Status</th>
                        <th class="py-3.5 px-4 text-center" data-column="akreditasi">Akreditasi</th>
                        <th class="py-3.5 px-4" data-sortable="false">Kecamatan</th>
                        <th class="py-3.5 px-4" data-sortable="false">Kepala Sekolah / Alamat</th>
                        <th class="py-3.5 px-4 text-center" data-sortable="false">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @include('sekolah._rows')
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: Tambah Sekolah Baru -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="createModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-xl border border-slate-200 space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tambah Satuan Pendidikan Baru</h3>
                    <p class="text-[11px] text-slate-500">Isi formulir data pokok dan tentukan titik lokasi sekolah di peta</p>
                </div>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer">&times;</button>
            </div>
            
            <form action="{{ route('sekolah.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NPSN (8-10 Digit) *</label>
                        <input type="text" name="npsn" required maxlength="10" placeholder="Contoh: 20300123" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Satuan Pendidikan *</label>
                        <input type="text" name="nama_sekolah" required placeholder="Contoh: SD Negeri 1 Bawang" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenjang *</label>
                        <select name="jenjang" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                            @foreach(['PAUD', 'TK', 'SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PKBM'] as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Kelembagaan *</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="Negeri">Negeri</option>
                            <option value="Swasta">Swasta</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Akreditasi *</label>
                        <select name="akreditasi" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="A">A (Unggul)</option>
                            <option value="B" selected>B (Baik)</option>
                            <option value="C">C (Cukup)</option>
                            <option value="Belum Terakreditasi">Belum Terakreditasi</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Wilayah Kecamatan *</label>
                        <select name="kecamatan_id" id="create_kecamatan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                            @foreach($kecamatanList as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->kode }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kepala Sekolah</label>
                        <input type="text" name="kepala_sekolah" placeholder="Nama & Gelar Kepsek" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Sekolah</label>
                        <input type="text" name="alamat" placeholder="Jl. Raya / Desa..." class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Telepon</label>
                        <input type="text" name="telepon" placeholder="0286-..." class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                </div>

                <!-- Section Peta Koordinat (Leaflet GIS) -->
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <label class="block font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Pilih Titik Lokasi Satuan Pendidikan (Peta Leaflet)</span>
                            </label>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Cari area/nama tempat atau klik pada peta. Koordinat latitude & longitude otomatis terisi.
                            </p>
                        </div>
                        <div id="create_coords_badge" class="shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-200/70 text-slate-600 font-medium text-[10px]">
                                <span>Belum ada titik terpilih</span>
                            </span>
                        </div>
                    </div>

                    <!-- Search Bar & Controls -->
                    <div class="relative">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text" 
                                       id="create_map_search"
                                       placeholder="Cari desa, kecamatan, jalan, atau nama tempat di Banjarnegara..." 
                                       class="w-full pl-8 pr-8 py-2 text-xs rounded-xl bg-white border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-2xs">
                                <button type="button" 
                                        id="create_search_clear" 
                                        style="display: none;"
                                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 text-sm font-bold cursor-pointer">
                                    &times;
                                </button>
                            </div>
                            
                            <button type="button" 
                                    id="create_search_btn" 
                                    class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition cursor-pointer">
                                <svg id="create_search_icon" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <svg id="create_search_spinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Cari</span>
                            </button>

                            <!-- GPS Button -->
                            <button type="button" 
                                    id="create_gps_btn"
                                    title="Gunakan Lokasi GPS Saya Saat Ini"
                                    class="inline-flex items-center gap-1 px-2.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 hover:text-blue-600 font-bold text-xs transition shadow-2xs cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" />
                                </svg>
                                <span class="hidden sm:inline">GPS</span>
                            </button>
                        </div>

                        <!-- Dropdown Search Results -->
                        <div id="create_search_results" 
                             style="display: none;" 
                             class="absolute z-[1001] left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white rounded-2xl shadow-xl border border-slate-200 divide-y divide-slate-100 text-xs">
                        </div>
                    </div>

                    <!-- Map Canvas Container -->
                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100">
                        <div id="mapCreatePicker" class="w-full h-64 z-0"></div>
                        
                        <div class="absolute bottom-2 left-2 z-[400] flex items-center gap-1.5 bg-white/95 backdrop-blur-xs px-2.5 py-1 rounded-xl shadow-xs border border-slate-200 text-[10px] text-slate-600 pointer-events-none">
                            <span>💡 Klik peta / geser pin untuk atur titik</span>
                        </div>

                        <div class="absolute bottom-2 right-2 z-[400]">
                            <button type="button" 
                                    id="create_clear_pin" 
                                    style="display: none;"
                                    class="bg-white/95 hover:bg-rose-50 text-rose-600 hover:text-rose-700 font-bold px-2.5 py-1 rounded-xl shadow-xs border border-rose-200 text-[10px] transition cursor-pointer">
                                &times; Hapus Titik
                            </button>
                        </div>
                    </div>

                    <!-- Latitude & Longitude Inputs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>Latitude (Garis Lintang)</span>
                                <span class="text-[10px] text-emerald-600 font-semibold">Otomatis terisi</span>
                            </label>
                            <input type="number" 
                                   step="0.0000001" 
                                   name="latitude" 
                                   id="create_latitude" 
                                   placeholder="Contoh: -7.382145" 
                                   class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>Longitude (Garis Bujur)</span>
                                <span class="text-[10px] text-emerald-600 font-semibold">Otomatis terisi</span>
                            </label>
                            <input type="number" 
                                   step="0.0000001" 
                                   name="longitude" 
                                   id="create_longitude" 
                                   placeholder="Contoh: 109.682314" 
                                   class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono text-xs">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-slate-600 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition shadow-sm cursor-pointer">Simpan Sekolah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Edit Sekolah -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="editModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-xl border border-slate-200 space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit Data Satuan Pendidikan</h3>
                    <p class="text-[11px] text-slate-500">Perbarui data pokok dan sesuaikan titik lokasi pada peta</p>
                </div>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer">&times;</button>
            </div>
            
            <form :action="'/sekolah/' + activeRow.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NPSN *</label>
                        <input type="text" name="npsn" x-model="activeRow.npsn" required maxlength="10" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Satuan Pendidikan *</label>
                        <input type="text" name="nama_sekolah" x-model="activeRow.nama_sekolah" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenjang *</label>
                        <select name="jenjang" x-model="activeRow.jenjang" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                            @foreach(['PAUD', 'TK', 'SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PKBM'] as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Kelembagaan *</label>
                        <select name="status" x-model="activeRow.status" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="Negeri">Negeri</option>
                            <option value="Swasta">Swasta</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Akreditasi *</label>
                        <select name="akreditasi" x-model="activeRow.akreditasi" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="A">A (Unggul)</option>
                            <option value="B">B (Baik)</option>
                            <option value="C">C (Cukup)</option>
                            <option value="Belum Terakreditasi">Belum Terakreditasi</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Wilayah Kecamatan *</label>
                        <select name="kecamatan_id" id="edit_kecamatan_id" x-model="activeRow.kecamatan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                            @foreach($kecamatanList as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kepala Sekolah</label>
                        <input type="text" name="kepala_sekolah" x-model="activeRow.kepala_sekolah" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat</label>
                        <input type="text" name="alamat" x-model="activeRow.alamat" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Telepon</label>
                        <input type="text" name="telepon" x-model="activeRow.telepon" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                </div>

                <!-- Section Peta Koordinat (Leaflet GIS) -->
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <label class="block font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Pilih Titik Lokasi Satuan Pendidikan (Peta Leaflet)</span>
                            </label>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Cari area/nama tempat atau klik pada peta. Koordinat latitude & longitude otomatis terisi.
                            </p>
                        </div>
                        <div id="edit_coords_badge" class="shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-200/70 text-slate-600 font-medium text-[10px]">
                                <span>Belum ada titik terpilih</span>
                            </span>
                        </div>
                    </div>

                    <!-- Search Bar & Controls -->
                    <div class="relative">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text" 
                                       id="edit_map_search"
                                       placeholder="Cari desa, kecamatan, jalan, atau nama tempat di Banjarnegara..." 
                                       class="w-full pl-8 pr-8 py-2 text-xs rounded-xl bg-white border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-2xs">
                                <button type="button" 
                                        id="edit_search_clear" 
                                        style="display: none;"
                                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 text-sm font-bold cursor-pointer">
                                    &times;
                                </button>
                            </div>
                            
                            <button type="button" 
                                    id="edit_search_btn" 
                                    class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition cursor-pointer">
                                <svg id="edit_search_icon" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <svg id="edit_search_spinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Cari</span>
                            </button>

                            <!-- GPS Button -->
                            <button type="button" 
                                    id="edit_gps_btn"
                                    title="Gunakan Lokasi GPS Saya Saat Ini"
                                    class="inline-flex items-center gap-1 px-2.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 hover:text-blue-600 font-bold text-xs transition shadow-2xs cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" />
                                </svg>
                                <span class="hidden sm:inline">GPS</span>
                            </button>
                        </div>

                        <!-- Dropdown Search Results -->
                        <div id="edit_search_results" 
                             style="display: none;" 
                             class="absolute z-[1001] left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white rounded-2xl shadow-xl border border-slate-200 divide-y divide-slate-100 text-xs">
                        </div>
                    </div>

                    <!-- Map Canvas Container -->
                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100">
                        <div id="mapEditPicker" class="w-full h-64 z-0"></div>
                        
                        <div class="absolute bottom-2 left-2 z-[400] flex items-center gap-1.5 bg-white/95 backdrop-blur-xs px-2.5 py-1 rounded-xl shadow-xs border border-slate-200 text-[10px] text-slate-600 pointer-events-none">
                            <span>💡 Klik peta / geser pin untuk atur titik</span>
                        </div>

                        <div class="absolute bottom-2 right-2 z-[400]">
                            <button type="button" 
                                    id="edit_clear_pin" 
                                    style="display: none;"
                                    class="bg-white/95 hover:bg-rose-50 text-rose-600 hover:text-rose-700 font-bold px-2.5 py-1 rounded-xl shadow-xs border border-rose-200 text-[10px] transition cursor-pointer">
                                &times; Hapus Titik
                            </button>
                        </div>
                    </div>

                    <!-- Latitude & Longitude Inputs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>Latitude (Garis Lintang)</span>
                                <span class="text-[10px] text-emerald-600 font-semibold">Otomatis terisi</span>
                            </label>
                            <input type="number" 
                                   step="0.0000001" 
                                   name="latitude" 
                                   id="edit_latitude" 
                                   x-model="activeRow.latitude" 
                                   placeholder="Contoh: -7.382145" 
                                   class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>Longitude (Garis Bujur)</span>
                                <span class="text-[10px] text-emerald-600 font-semibold">Otomatis terisi</span>
                            </label>
                            <input type="number" 
                                   step="0.0000001" 
                                   name="longitude" 
                                   id="edit_longitude" 
                                   x-model="activeRow.longitude" 
                                   placeholder="Contoh: 109.682314" 
                                   class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono text-xs">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-slate-600 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition shadow-sm cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Hapus Sekolah -->
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="deleteModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center gap-3 text-rose-600">
                <div class="p-3 bg-rose-50 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Konfirmasi Hapus</h3>
            </div>
            
            <p class="text-xs text-slate-600 leading-relaxed">
                Apakah Anda yakin ingin menghapus data sekolah <strong class="text-slate-900" x-text="activeRow.nama_sekolah"></strong>? Data partisipasi murid dan data guru di sekolah ini juga akan terhapus.
            </p>

            <form :action="'/sekolah/' + activeRow.id" method="POST" class="pt-2 flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-xs text-slate-600 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition shadow-sm">Hapus Sekolah</button>
            </form>
        </div>
    </div>

    <!-- MODAL 4: Import Excel / CSV -->
    <div x-show="importModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="importModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-xl border border-slate-200 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Import Satuan Pendidikan (Dapodik/EMIS)</h3>
                        <p class="text-[11px] text-slate-500">Unggah berkas CSV master data sekolah</p>
                    </div>
                </div>
                <button type="button" @click="importModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <!-- Petunjuk & Unduh Template -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 text-xs space-y-2">
                <p class="font-bold text-slate-800">Petunjuk Format Kolom CSV:</p>
                <ul class="list-disc list-inside text-slate-600 space-y-1 text-[11px]">
                    <li>Kolom utama: <code>npsn</code>, <code>nama_sekolah</code>, <code>jenjang</code>, <code>status</code>, <code>akreditasi</code>, <code>nama_kecamatan</code>.</li>
                    <li>Sistem otomatis mencocokkan nama kecamatan dengan 20 kecamatan di Banjarnegara.</li>
                </ul>
                <div class="pt-2">
                    <a href="{{ route('import.template', ['type' => 'sekolah']) }}" class="inline-flex items-center gap-1.5 font-bold text-emerald-700 hover:text-emerald-800 underline text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh Template CSV Sekolah (.csv)</span>
                    </a>
                </div>
            </div>

            <!-- Form Upload -->
            <form action="{{ route('sekolah.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Pilih Berkas CSV *</label>
                    <input type="file" name="file_sekolah" required accept=".csv,.txt" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Ukuran berkas maksimal 20 MB</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="importModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-slate-600 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">Mulai Import Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 5: Sinkronisasi Dapodik Banjarnegara -->
    <x-dapodik-sync-modal defaultTarget="sekolah" />

</div>
@endsection

@push('scripts')
<style>
    /* Styling Custom Pin Marker */
    .simak-school-pin {
        background: transparent !important;
        border: none !important;
    }

    /* Leaflet Tooltip Ultra Modern */
    .simak-map-tooltip {
        background-color: #0f172a !important;
        color: #ffffff !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        padding: 4px 8px !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
    .simak-map-tooltip::before {
        border-top-color: #0f172a !important;
    }

    /* Hilangkan focus outline / bounding box hitam Leaflet */
    .leaflet-container,
    .leaflet-container:focus,
    .leaflet-container :focus,
    .leaflet-interactive:focus {
        outline: none !important;
        -webkit-tap-highlight-color: transparent !important;
    }
</style>

<script>
(() => {
    const BANJARNEGARA_CENTER = [-7.3750, 109.6800];
    window.KECAMATAN_LIST = @json($kecamatanList);
    window.KECAMATAN_LOOKUP = {};

    window.KECAMATAN_LIST.forEach(k => {
        window.KECAMATAN_LOOKUP[k.id] = {
            id: k.id,
            nama: k.nama,
            lat: k.latitude ? parseFloat(k.latitude) : null,
            lng: k.longitude ? parseFloat(k.longitude) : null
        };
    });

    class SimakSchoolMapPicker {
        constructor(config) {
            this.containerId = config.containerId;
            this.latInputId = config.latInputId;
            this.lngInputId = config.lngInputId;
            this.statusBadgeId = config.statusBadgeId;
            this.searchInputId = config.searchInputId;
            this.searchClearId = config.searchClearId;
            this.searchBtnId = config.searchBtnId;
            this.searchSpinnerId = config.searchSpinnerId;
            this.searchIconId = config.searchIconId;
            this.searchResultsId = config.searchResultsId;
            this.gpsBtnId = config.gpsBtnId;
            this.clearBtnId = config.clearBtnId;
            this.kecamatanSelectId = config.kecamatanSelectId;
            this.onCoordChange = config.onCoordChange || null;

            this.map = null;
            this.marker = null;
            this.isSearching = false;
            this.searchDebounceTimer = null;

            this.latInput = document.getElementById(this.latInputId);
            this.lngInput = document.getElementById(this.lngInputId);
            this.statusBadge = document.getElementById(this.statusBadgeId);
            this.searchInput = document.getElementById(this.searchInputId);
            this.searchClear = document.getElementById(this.searchClearId);
            this.searchBtn = document.getElementById(this.searchBtnId);
            this.searchSpinner = document.getElementById(this.searchSpinnerId);
            this.searchIcon = document.getElementById(this.searchIconId);
            this.searchResults = document.getElementById(this.searchResultsId);
            this.gpsBtn = document.getElementById(this.gpsBtnId);
            this.clearBtn = document.getElementById(this.clearBtnId);
            this.kecamatanSelect = document.getElementById(this.kecamatanSelectId);

            this.setupEventListeners();
        }

        setupEventListeners() {
            if (this.searchBtn) {
                this.searchBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.executeSearch(true);
                });
            }

            if (this.searchInput) {
                this.searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.executeSearch(true);
                    }
                });

                this.searchInput.addEventListener('input', () => {
                    const val = this.searchInput.value.trim();
                    if (this.searchClear) {
                        this.searchClear.style.display = val ? 'flex' : 'none';
                    }

                    clearTimeout(this.searchDebounceTimer);
                    if (val.length >= 2) {
                        this.searchDebounceTimer = setTimeout(() => {
                            this.executeSearch(false);
                        }, 350);
                    } else if (val.length === 0) {
                        this.hideSearchResults();
                    }
                });
            }

            if (this.searchClear) {
                this.searchClear.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (this.searchInput) this.searchInput.value = '';
                    this.searchClear.style.display = 'none';
                    this.hideSearchResults();
                });
            }

            if (this.gpsBtn) {
                this.gpsBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.useCurrentLocation();
                });
            }

            if (this.clearBtn) {
                this.clearBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.clearMarker();
                });
            }

            if (this.kecamatanSelect) {
                this.kecamatanSelect.addEventListener('change', (e) => {
                    this.onKecamatanChanged(e.target.value);
                });
            }

            if (this.latInput) {
                this.latInput.addEventListener('input', () => this.handleManualCoordInput());
            }
            if (this.lngInput) {
                this.lngInput.addEventListener('input', () => this.handleManualCoordInput());
            }

            document.addEventListener('click', (e) => {
                if (this.searchResults && 
                    !this.searchResults.contains(e.target) && 
                    !this.searchInput.contains(e.target) && 
                    (!this.searchBtn || !this.searchBtn.contains(e.target))) {
                    this.hideSearchResults();
                }
            });
        }

        initMap() {
            if (this.map) {
                this.map.invalidateSize();
                return;
            }

            const container = document.getElementById(this.containerId);
            if (!container) return;

            this.map = L.map(this.containerId, {
                center: BANJARNEGARA_CENTER,
                zoom: 11,
                zoomControl: true,
                scrollWheelZoom: true
            });

            const osmLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19
            }).addTo(this.map);

            const esriLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri',
                maxZoom: 19
            });

            L.control.layers({
                'Peta Jalan': osmLayer,
                'Citra Satelit': esriLayer
            }, null, { position: 'topright' }).addTo(this.map);

            this.map.on('click', (e) => {
                const lat = Number(e.latlng.lat.toFixed(7));
                const lng = Number(e.latlng.lng.toFixed(7));
                this.setMarker(lat, lng, false);
            });

            this.map.getContainer().addEventListener('mousedown', () => {
                if (document.activeElement && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }
            });
        }

        createPinIcon() {
            return L.divIcon({
                className: 'simak-school-pin',
                html: `
                    <div style="position: relative; width: 34px; height: 42px; transform: translate(-50%, -100%); filter: drop-shadow(0 4px 6px rgba(0,0,0,0.35));">
                        <svg viewBox="0 0 24 24" fill="none" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C7.58 2 4 5.58 4 10C4 15.25 12 22 12 22C12 22 20 15.25 20 10C20 5.58 16.42 2 12 2Z" fill="#2563EB" stroke="#FFFFFF" stroke-width="2"/>
                            <circle cx="12" cy="10" r="3.5" fill="#FFFFFF"/>
                        </svg>
                    </div>
                `,
                iconSize: [0, 0],
                iconAnchor: [0, 0],
                popupAnchor: [0, -38]
            });
        }

        setMarker(lat, lng, panTo = false, zoomLevel = 15) {
            if (isNaN(lat) || isNaN(lng) || lat === '' || lng === '' || lat === null || lng === null) {
                this.clearMarker();
                return;
            }

            lat = Number(parseFloat(lat).toFixed(7));
            lng = Number(parseFloat(lng).toFixed(7));

            if (!this.marker) {
                this.marker = L.marker([lat, lng], {
                    icon: this.createPinIcon(),
                    draggable: true
                }).addTo(this.map);

                this.marker.bindTooltip("📍 Lokasi Sekolah (Geser untuk sesuaikan posisi)", {
                    direction: 'top',
                    offset: [0, -38],
                    className: 'simak-map-tooltip'
                });

                this.marker.on('dragend', () => {
                    const pos = this.marker.getLatLng();
                    const newLat = Number(pos.lat.toFixed(7));
                    const newLng = Number(pos.lng.toFixed(7));
                    this.updateInputsAndBadge(newLat, newLng);
                });
            } else {
                this.marker.setLatLng([lat, lng]);
            }

            if (panTo && this.map) {
                const targetZoom = Math.max(this.map.getZoom(), zoomLevel);
                this.map.flyTo([lat, lng], targetZoom, {
                    animate: true,
                    duration: 1.0
                });
            }

            this.updateInputsAndBadge(lat, lng);
        }

        clearMarker() {
            if (this.marker && this.map) {
                this.map.removeLayer(this.marker);
                this.marker = null;
            }
            if (this.latInput) {
                this.latInput.value = '';
                this.latInput.dispatchEvent(new Event('input'));
            }
            if (this.lngInput) {
                this.lngInput.value = '';
                this.lngInput.dispatchEvent(new Event('input'));
            }
            if (this.statusBadge) {
                this.statusBadge.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-200/70 text-slate-600 font-medium text-[10px]">
                        <span>Belum ada titik terpilih</span>
                    </span>
                `;
            }
            if (this.clearBtn) {
                this.clearBtn.style.display = 'none';
            }
            if (typeof this.onCoordChange === 'function') {
                this.onCoordChange('', '');
            }
        }

        updateInputsAndBadge(lat, lng) {
            if (this.latInput && this.latInput.value !== String(lat)) {
                this.latInput.value = lat;
                this.latInput.dispatchEvent(new Event('input'));
            }
            if (this.lngInput && this.lngInput.value !== String(lng)) {
                this.lngInput.value = lng;
                this.lngInput.dispatchEvent(new Event('input'));
            }
            if (this.statusBadge) {
                this.statusBadge.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold font-mono text-[11px] border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>${lat}, ${lng}</span>
                    </span>
                `;
            }
            if (this.clearBtn) {
                this.clearBtn.style.display = 'inline-block';
            }
            if (typeof this.onCoordChange === 'function') {
                this.onCoordChange(lat, lng);
            }
        }

        handleManualCoordInput() {
            const rawLat = parseFloat(this.latInput?.value);
            const rawLng = parseFloat(this.lngInput?.value);
            if (!isNaN(rawLat) && !isNaN(rawLng) && rawLat >= -90 && rawLat <= 90 && rawLng >= -180 && rawLng <= 180) {
                this.setMarker(rawLat, rawLng, true);
            }
        }

        onKecamatanChanged(kecId) {
            const kec = (window.KECAMATAN_LOOKUP || {})[kecId];
            if (kec && kec.lat && kec.lng && this.map) {
                if (!this.marker) {
                    this.map.flyTo([kec.lat, kec.lng], 13, { duration: 0.8 });
                }
            }
        }

        formatTitle(str) {
            if (!str) return '';
            return str.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
        }

        selectLocation(item) {
            if (!item) return;
            const itemLng = item.lng !== undefined ? item.lng : item.lon;
            this.setMarker(item.lat, itemLng, true, item.zoom || 15);
            if (this.searchInput) {
                this.searchInput.value = item.title;
                if (this.searchClear) {
                    this.searchClear.style.display = 'flex';
                }
            }
            this.hideSearchResults();
        }

        async executeSearch(autoSelectFirst = false) {
            if (!this.searchInput) return;
            const rawQuery = this.searchInput.value.trim();
            if (!rawQuery) return;

            clearTimeout(this.searchDebounceTimer);
            this.searchSeq = (this.searchSeq || 0) + 1;
            const currentSeq = this.searchSeq;

            if (!this.map) {
                this.initMap();
            }

            this.setSearchingState(true);
            const results = [];
            const seenKeys = new Set();

            const addResult = (res) => {
                const itemLng = res.lng !== undefined ? res.lng : res.lon;
                const key = `${res.lat.toFixed(4)},${itemLng.toFixed(4)}`;
                if (!seenKeys.has(key)) {
                    seenKeys.add(key);
                    results.push(res);
                }
            };

            const qLower = rawQuery.toLowerCase();
            // Bersihkan awalan kata umum (kecamatan, desa, kelurahan, dsb)
            const clean = qLower
                .replace(/\b(kecamatan|kec\.?|desa|kelurahan|kel\.?|kabupaten|kab\.?)\b/gi, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            // 1. Pencarian lokal 20 kecamatan di Banjarnegara (0ms latency, akurasi tinggi)
            (window.KECAMATAN_LIST || []).forEach(k => {
                const kName = (k.nama || '').toLowerCase();
                const matchExact = clean && (kName === clean);
                const matchContains = clean && (kName.includes(clean) || clean.includes(kName));
                const matchRaw = kName.includes(qLower) || qLower.includes(kName);

                if ((matchExact || matchContains || matchRaw) && k.latitude && k.longitude) {
                    addResult({
                        title: `Kecamatan ${this.formatTitle(k.nama)}`,
                        subtitle: `Wilayah Kabupaten Banjarnegara`,
                        lat: parseFloat(k.latitude),
                        lon: parseFloat(k.longitude),
                        lng: parseFloat(k.longitude),
                        source: 'Kecamatan',
                        zoom: 14,
                        isExact: matchExact
                    });
                }
            });

            // 2. Query Photon Geocoder (OSM Based, difokuskan/bias pada koordinat Banjarnegara)
            try {
                const searchQ = clean.length >= 2 ? clean : rawQuery;
                const photonUrl = `https://photon.komoot.io/api/?q=${encodeURIComponent(searchQ)}&lat=-7.375&lon=109.68&limit=6`;
                const pRes = await fetch(photonUrl);
                if (pRes.ok) {
                    const pData = await pRes.json();
                    if (pData && pData.features && pData.features.length > 0) {
                        pData.features.forEach(f => {
                            const geom = f.geometry;
                            const p = f.properties || {};
                            if (geom && geom.coordinates && geom.coordinates.length >= 2) {
                                const lon = geom.coordinates[0];
                                const lat = geom.coordinates[1];
                                
                                const subParts = [p.district, p.city, p.county, p.state].filter(Boolean);
                                const subtitle = subParts.length > 0 ? subParts.join(', ') : 'Kabupaten Banjarnegara';
                                const typeLabel = p.osm_value === 'village' ? 'Desa' : (p.osm_value === 'town' ? 'Kota' : (p.type || 'Tempat'));

                                addResult({
                                    title: p.name || 'Lokasi',
                                    subtitle: subtitle,
                                    lat: parseFloat(lat),
                                    lon: parseFloat(lon),
                                    lng: parseFloat(lon),
                                    source: typeLabel,
                                    zoom: 15
                                });
                            }
                        });
                    }
                }
            } catch (err) {
                console.warn('Photon geocoding warning:', err);
            }

            // 3. Fallback ke OSM Nominatim jika hasil masih belum ada
            if (results.length === 0) {
                try {
                    const hasBanjarnegara = qLower.includes('banjarnegara');
                    const osmQuery = hasBanjarnegara ? rawQuery : `${rawQuery} Banjarnegara`;
                    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(osmQuery)}&countrycodes=id&limit=4`;
                    
                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        (data || []).forEach(item => {
                            addResult({
                                title: item.name || item.display_name.split(',')[0],
                                subtitle: item.display_name,
                                lat: parseFloat(item.lat),
                                lon: parseFloat(item.lon),
                                lng: parseFloat(item.lon),
                                source: 'OSM',
                                zoom: 15
                            });
                        });
                    }
                } catch (err) {
                    console.warn('Nominatim geocoding warning:', err);
                }
            }

            // Batalkan pemrosesan jika ada request pencarian baru yang lebih mutakhir
            if (currentSeq !== this.searchSeq) {
                return;
            }

            this.setSearchingState(false);

            // Jika dipicu tombol 'Cari' atau 'Enter', langsung arahkan peta ke lokasi hasil teratas!
            if (autoSelectFirst && results.length > 0) {
                this.selectLocation(results[0]);
            } else {
                this.renderSearchResults(results, rawQuery);
            }
        }

        setSearchingState(isLoading) {
            this.isSearching = isLoading;
            if (this.searchSpinner) {
                this.searchSpinner.classList.toggle('hidden', !isLoading);
            }
            if (this.searchIcon) {
                this.searchIcon.classList.toggle('hidden', isLoading);
            }
            if (this.searchBtn) {
                this.searchBtn.disabled = isLoading;
            }
        }

        renderSearchResults(results, query) {
            if (!this.searchResults) return;

            if (results.length === 0) {
                this.searchResults.innerHTML = `
                    <div class="p-3 text-center text-slate-500 text-xs">
                        Tidak ditemukan lokasi untuk "<strong>${this.escapeHtml(query)}</strong>".<br>
                        <span class="text-[10px] text-slate-400">Silakan klik langsung pada peta atau coba nama kecamatan/desa lain.</span>
                    </div>
                `;
                this.searchResults.style.display = 'block';
                return;
            }

            let html = `
                <div class="px-3 py-1.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[10px] text-slate-500 font-medium">
                    <span>${results.length} lokasi ditemukan</span>
                    <span>Klik untuk mengarahkan peta</span>
                </div>
            `;
            results.forEach((res, index) => {
                const badgeClass = res.source === 'Kecamatan' 
                    ? 'bg-blue-100 text-blue-700' 
                    : (res.source === 'Desa' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
                
                html += `
                    <button type="button" 
                            class="simak-search-item w-full text-left px-3.5 py-2.5 hover:bg-blue-50 transition flex items-start gap-2.5 cursor-pointer"
                            data-index="${index}">
                        <span class="text-blue-600 mt-0.5 shrink-0">📍</span>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-slate-800 text-xs truncate">${this.escapeHtml(res.title)}</div>
                            <div class="text-[10px] text-slate-400 truncate">${this.escapeHtml(res.subtitle)}</div>
                        </div>
                        <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-bold shrink-0 ${badgeClass}">${res.source}</span>
                    </button>
                `;
            });

            this.searchResults.innerHTML = html;
            this.searchResults.style.display = 'block';

            this.searchResults.querySelectorAll('.simak-search-item').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.dataset.index, 10);
                    const selected = results[idx];
                    if (selected) {
                        this.selectLocation(selected);
                    }
                });
            });
        }

        hideSearchResults() {
            if (this.searchResults) {
                this.searchResults.style.display = 'none';
            }
        }

        useCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung deteksi lokasi GPS.');
                return;
            }

            if (this.gpsBtn) {
                this.gpsBtn.classList.add('animate-pulse', 'text-blue-600');
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    if (this.gpsBtn) {
                        this.gpsBtn.classList.remove('animate-pulse', 'text-blue-600');
                    }
                    const lat = Number(pos.coords.latitude.toFixed(7));
                    const lng = Number(pos.coords.longitude.toFixed(7));
                    this.setMarker(lat, lng, true, 16);
                },
                (err) => {
                    if (this.gpsBtn) {
                        this.gpsBtn.classList.remove('animate-pulse', 'text-blue-600');
                    }
                    console.warn('Geolocation error:', err);
                    alert('Gagal mengambil lokasi GPS. Pastikan izin akses lokasi pada browser telah diizinkan.');
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        }

        open(existingLat, existingLng, kecamatanId) {
            this.initMap();
            this.hideSearchResults();
            if (this.searchInput) this.searchInput.value = '';
            if (this.searchClear) this.searchClear.style.display = 'none';

            setTimeout(() => {
                if (this.map) {
                    this.map.invalidateSize();
                }

                const latNum = parseFloat(existingLat);
                const lngNum = parseFloat(existingLng);

                if (!isNaN(latNum) && !isNaN(lngNum) && latNum !== 0 && lngNum !== 0) {
                    this.setMarker(latNum, lngNum, true, 15);
                } else {
                    this.clearMarker();
                    const kec = (window.KECAMATAN_LOOKUP || {})[kecamatanId];
                    if (kec && kec.lat && kec.lng && this.map) {
                        this.map.setView([kec.lat, kec.lng], 13);
                    } else if (this.map) {
                        this.map.setView(BANJARNEGARA_CENTER, 11);
                    }
                }
            }, 180);
        }

        escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
    }

    function initSchoolPickers() {
        if (window.L) {
            window.createSchoolPicker = new SimakSchoolMapPicker({
                containerId: 'mapCreatePicker',
                latInputId: 'create_latitude',
                lngInputId: 'create_longitude',
                statusBadgeId: 'create_coords_badge',
                searchInputId: 'create_map_search',
                searchClearId: 'create_search_clear',
                searchBtnId: 'create_search_btn',
                searchSpinnerId: 'create_search_spinner',
                searchIconId: 'create_search_icon',
                searchResultsId: 'create_search_results',
                gpsBtnId: 'create_gps_btn',
                clearBtnId: 'create_clear_pin',
                kecamatanSelectId: 'create_kecamatan_id'
            });

            window.editSchoolPicker = new SimakSchoolMapPicker({
                containerId: 'mapEditPicker',
                latInputId: 'edit_latitude',
                lngInputId: 'edit_longitude',
                statusBadgeId: 'edit_coords_badge',
                searchInputId: 'edit_map_search',
                searchClearId: 'edit_search_clear',
                searchBtnId: 'edit_search_btn',
                searchSpinnerId: 'edit_search_spinner',
                searchIconId: 'edit_search_icon',
                searchResultsId: 'edit_search_results',
                gpsBtnId: 'edit_gps_btn',
                clearBtnId: 'edit_clear_pin',
                kecamatanSelectId: 'edit_kecamatan_id',
                onCoordChange: (lat, lng) => {
                    const alpineEl = document.querySelector('[x-data]');
                    if (alpineEl && alpineEl._x_dataStack && alpineEl._x_dataStack[0] && alpineEl._x_dataStack[0].activeRow) {
                        alpineEl._x_dataStack[0].activeRow.latitude = lat;
                        alpineEl._x_dataStack[0].activeRow.longitude = lng;
                    }
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSchoolPickers);
    } else {
        initSchoolPickers();
    }
})();
</script>
@endpush
