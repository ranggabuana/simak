@extends('layouts.app', ['title' => 'Data Partisipasi Siswa (Dapodik)'])

@section('content')
<div class="space-y-8" x-data="{ createModal: false, editModal: false, deleteModal: false, importModal: false, activeRow: {} }">

    <!-- Header Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-blue-50/80 via-slate-50 to-white text-slate-900 flex flex-col xl:flex-row xl:items-center justify-between gap-5 shadow-xs border border-blue-200/60">
        <div class="space-y-1.5 flex-1 min-w-0 pr-2">
            <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Data Partisipasi Siswa Dapodik / EMIS (Tahun {{ $tahun }})</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kelola data jumlah peserta didik aktif dan siswa usia sekolah resmi per satuan pendidikan. Data ini menjadi pembilang (*numerator*) utama dalam perhitungan rumus APK, APM, dan APS Kabupaten Banjarnegara.
            </p>
        </div>
        
        <!-- Action Row (1 Row) -->
        <div class="flex items-center gap-2.5 shrink-0 flex-nowrap overflow-x-auto pb-1 xl:pb-0">
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
                <span>Tambah Data Siswa</span>
            </button>

            <a href="{{ route('export.siswa', array_merge(request()->query(), ['tahun' => $tahun])) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold text-xs shadow-2xs transition whitespace-nowrap shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Total Siswa Aktif</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_siswa']) }}</div>
            <p class="text-[10px] text-slate-500">Semua Jenjang Terdata</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Siswa Usia Sesuai</span>
            <div class="text-xl font-extrabold text-blue-600 mt-1">{{ number_format($stats['total_sesuai']) }}</div>
            <p class="text-[10px] text-slate-500">Sesuai Usia Resmi Jenjang</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Rasio Usia Sesuai</span>
            <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ $stats['persen_sesuai'] }}%</div>
            <p class="text-[10px] text-emerald-600 font-bold">Kualitas Penyerapan Siswa</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Total Rombel</span>
            <div class="text-xl font-extrabold text-indigo-600 mt-1">{{ number_format($stats['total_rombel']) }}</div>
            <p class="text-[10px] text-slate-500">Rombongan Belajar</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold uppercase text-slate-400">Lembaga Terdata</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_sekolah_terdata']) }}</div>
            <p class="text-[10px] text-slate-500">Sekolah Pelapor Dapodik</p>
        </div>
    </div>

    <!-- Rekapitulasi Gender Peserta Didik -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Rekapitulasi Partisipasi Berdasarkan Gender (Laki-laki & Perempuan)</h4>
            </div>
            <span class="text-[11px] text-slate-400">Tahun Kinerja {{ $tahun }}</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="p-3.5 rounded-2xl bg-blue-50/60 border border-blue-100 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-blue-900 uppercase tracking-wider block">Siswa Laki-laki (L)</span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="text-lg font-extrabold text-blue-700">{{ number_format($stats['total_siswa_l']) }}</span>
                        <span class="text-xs font-semibold text-blue-600">({{ $stats['persen_l'] }}%)</span>
                    </div>
                    <span class="text-[10px] text-blue-600/70 block mt-0.5">Usia sesuai: {{ number_format($stats['total_sesuai_l']) }}</span>
                </div>
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                    ♂
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-pink-50/60 border border-pink-100 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-pink-900 uppercase tracking-wider block">Siswi Perempuan (P)</span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="text-lg font-extrabold text-pink-700">{{ number_format($stats['total_siswa_p']) }}</span>
                        <span class="text-xs font-semibold text-pink-600">({{ $stats['persen_p'] }}%)</span>
                    </div>
                    <span class="text-[10px] text-pink-600/70 block mt-0.5">Usia sesuai: {{ number_format($stats['total_sesuai_p']) }}</span>
                </div>
                <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center font-bold text-xs">
                    ♀
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">Rasio Gender Siswa</span>
                    @php
                        $ratioL_P = $stats['total_siswa_p'] > 0 ? round($stats['total_siswa_l'] / $stats['total_siswa_p'], 2) : 1;
                    @endphp
                    <div class="text-lg font-extrabold text-slate-800 mt-0.5">{{ $ratioL_P }} : 1</div>
                    <span class="text-[10px] text-slate-400 block">Perbandingan L : P</span>
                </div>
                <div class="w-8 h-8 rounded-xl bg-slate-200/70 text-slate-700 flex items-center justify-center font-bold text-xs">
                    ⚖️
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-100 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-emerald-900 uppercase tracking-wider block">Paritas Gender Usia Sesuai</span>
                    @php
                        $gpiSiswa = $stats['total_sesuai_l'] > 0 ? round($stats['total_sesuai_p'] / $stats['total_sesuai_l'], 3) : 1;
                    @endphp
                    <div class="text-lg font-extrabold text-emerald-700 mt-0.5">{{ $gpiSiswa }}</div>
                    <span class="text-[10px] text-emerald-600/70 block">Indeks GPI (P/L)</span>
                </div>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                    ✓
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('siswa.index') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-600 uppercase text-[10px] tracking-wider">Tahun & Filter:</span>
            </div>
            <div>
                <select name="tahun" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-bold focus:ring-2 focus:ring-blue-500">
                    @foreach([2024, 2025, 2026, 2027, 2028, 2029] as $t)
                        <option value="{{ $t }}" {{ $tahun === $t ? 'selected' : '' }}>Tahun {{ $t }}</option>
                    @endforeach
                </select>
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
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatanList as $k)
                        <option value="{{ $k->id }}" {{ $filterKecamatan == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="gender" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-blue-500">
                    <option value="all" {{ ($filterGender ?? 'all') === 'all' ? 'selected' : '' }}>Semua Gender</option>
                    <option value="L" {{ ($filterGender ?? '') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ ($filterGender ?? '') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                </select>
            </div>
            @if($filterJenjang || $filterKecamatan || ($filterGender && $filterGender !== 'all'))
                <a href="{{ route('siswa.index', ['tahun' => $tahun]) }}" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold transition">
                    &times; Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Rincian Partisipasi Siswa per Satuan Pendidikan</h3>
                <p class="text-xs text-slate-500">Menampilkan {{ number_format($siswaList->total()) }} data partisipasi satuan pendidikan</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" 
                   data-server-side="true" 
                   data-url="{{ route('siswa.index') }}" 
                   data-total="{{ $siswaList->total() }}" 
                   data-current-page="{{ $siswaList->currentPage() }}" 
                   data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4 font-mono" data-sortable="false">NPSN</th>
                        <th class="py-3.5 px-4" data-sortable="false">Nama Sekolah</th>
                        <th class="py-3.5 px-4 text-center" data-column="jenjang">Jenjang</th>
                        <th class="py-3.5 px-4" data-sortable="false">Kecamatan</th>
                        <th class="py-3.5 px-4 text-center" data-column="tahun_ajaran">Tahun Ajaran</th>
                        <th class="py-3.5 px-4 text-right" data-column="total_siswa_semua_usia">Total Siswa</th>
                        <th class="py-3.5 px-4 text-right" data-column="siswa_usia_sesuai">Usia Sesuai</th>
                        <th class="py-3.5 px-4 text-right" data-sortable="false">Rasio (%)</th>
                        <th class="py-3.5 px-4 text-center" data-column="jumlah_rombel">Rombel</th>
                        <th class="py-3.5 px-4 text-center" data-sortable="false">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @include('siswa._rows')
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: Tambah Partisipasi Siswa -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="createModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-xl border border-slate-200 space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Tambah Data Partisipasi Siswa Dapodik</h3>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>
            
            <form action="{{ route('siswa.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Satuan Pendidikan *</label>
                    <select name="satuan_pendidikan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Sekolah --</option>
                        @foreach($sekolahList as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_sekolah }} (NPSN: {{ $s->npsn }}) [{{ $s->jenjang }}]</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Ajaran *</label>
                        <input type="text" name="tahun_ajaran" required value="2024/2025" placeholder="Contoh: 2024/2025" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Anggaran Kinerja *</label>
                        <input type="number" name="tahun" required value="{{ $tahun }}" min="2020" max="2035" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Total Siswa (Semua Usia) *</label>
                        <input type="number" name="total_siswa_semua_usia" required min="0" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Siswa Usia Sesuai *</label>
                        <input type="number" name="siswa_usia_sesuai" required min="0" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                        <p class="text-[9px] text-slate-400 mt-0.5">Untuk rumus APM</p>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jumlah Rombel</label>
                        <input type="number" name="jumlah_rombel" min="0" placeholder="Otomatis" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <div>
                        <label class="block font-semibold text-blue-900 mb-1">Jumlah Siswa Laki-laki (L)</label>
                        <input type="number" name="siswa_l" min="0" placeholder="Otomatis jika kosong" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:ring-2 focus:ring-blue-500 font-mono">
                        <p class="text-[9px] text-slate-400 mt-0.5">Opsional: Estimasi otomatis 51%</p>
                    </div>
                    <div>
                        <label class="block font-semibold text-pink-900 mb-1">Jumlah Siswi Perempuan (P)</label>
                        <input type="number" name="siswa_p" min="0" placeholder="Otomatis jika kosong" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:ring-2 focus:ring-pink-500 font-mono">
                        <p class="text-[9px] text-slate-400 mt-0.5">Opsional: Estimasi otomatis 49%</p>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-slate-600 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition shadow-sm">Simpan Data Siswa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Edit Partisipasi Siswa -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="editModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-xl border border-slate-200 space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit Data Partisipasi Siswa</h3>
                    <p class="text-[11px] text-blue-600 font-bold" x-text="activeRow.satuan_pendidikan?.nama_sekolah"></p>
                </div>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>
            
            <form :action="'/siswa/' + activeRow.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Ajaran *</label>
                        <input type="text" name="tahun_ajaran" x-model="activeRow.tahun_ajaran" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Kinerja *</label>
                        <input type="number" name="tahun" x-model="activeRow.tahun" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Total Siswa *</label>
                        <input type="number" name="total_siswa_semua_usia" x-model="activeRow.total_siswa_semua_usia" required min="0" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Siswa Usia Sesuai *</label>
                        <input type="number" name="siswa_usia_sesuai" x-model="activeRow.siswa_usia_sesuai" required min="0" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jumlah Rombel</label>
                        <input type="number" name="jumlah_rombel" x-model="activeRow.jumlah_rombel" min="0" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <div>
                        <label class="block font-semibold text-blue-900 mb-1">Siswa Laki-laki (L)</label>
                        <input type="number" name="siswa_l" x-model="activeRow.siswa_l" min="0" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-pink-900 mb-1">Siswi Perempuan (P)</label>
                        <input type="number" name="siswa_p" x-model="activeRow.siswa_p" min="0" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:ring-2 focus:ring-pink-500 font-mono">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-slate-600 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Hapus Partisipasi Siswa -->
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
                Apakah Anda yakin ingin menghapus data partisipasi murid pada sekolah <strong class="text-slate-900" x-text="activeRow.satuan_pendidikan?.nama_sekolah"></strong> (T.A <span x-text="activeRow.tahun_ajaran"></span>)?
            </p>

            <form :action="'/siswa/' + activeRow.id" method="POST" class="pt-2 flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-xs text-slate-600 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition shadow-sm">Hapus Data</button>
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
                        <h3 class="text-base font-bold text-slate-900">Import Data Partisipasi Siswa (Dapodik)</h3>
                        <p class="text-[11px] text-slate-500">Unggah berkas CSV rekapitulasi peserta didik</p>
                    </div>
                </div>
                <button type="button" @click="importModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <!-- Petunjuk & Unduh Template -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 text-xs space-y-2">
                <p class="font-bold text-slate-800">Petunjuk Format Kolom CSV:</p>
                <ul class="list-disc list-inside text-slate-600 space-y-1 text-[11px]">
                    <li>Kolom utama: <code>npsn</code>, <code>nama_sekolah</code>, <code>tahun_ajaran</code>, <code>tahun</code>, <code>total_siswa_semua_usia</code>, <code>siswa_usia_sesuai</code>, <code>jumlah_rombel</code>.</li>
                    <li>Sistem mencocokkan sekolah berdasarkan nomor <strong>NPSN</strong> atau nama sekolah terdaftar.</li>
                </ul>
                <div class="pt-2">
                    <a href="{{ route('import.template', ['type' => 'siswa']) }}" class="inline-flex items-center gap-1.5 font-bold text-emerald-700 hover:text-emerald-800 underline text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh Template CSV Partisipasi Siswa (.csv)</span>
                    </a>
                </div>
            </div>

            <!-- Form Upload -->
            <form action="{{ route('siswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tahun Anggaran Kinerja *</label>
                    <input type="number" name="tahun" required value="{{ $tahun }}" min="2020" max="2035" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Pilih Berkas CSV *</label>
                    <input type="file" name="file_siswa" required accept=".csv,.txt" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Ukuran berkas maksimal 20 MB</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="importModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-slate-600 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-sm">Mulai Import Data</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
