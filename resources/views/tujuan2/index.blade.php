@extends('layouts.app', ['title' => 'Tujuan 2: Prestasi Siswa'])

@section('content')
<div class="space-y-8" x-data="{ createModal: false, editModal: false, importModal: false, viewMode: 'table', activeRow: {} }">

    <!-- Header Banner (Light Style) -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-amber-50/70 via-slate-50 to-white text-slate-900 flex flex-col xl:flex-row xl:items-center justify-between gap-5 shadow-xs border border-amber-200/70">
        <div class="space-y-1.5 flex-1 min-w-0 pr-2">
            <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Perolehan Prestasi Siswa (Tahun {{ $tahun }})</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Pencatatan rekapitulasi prestasi siswa pada ajang kompetisi sains, riset teknologi, seni budaya, dan olahraga dari tingkat Kecamatan hingga Internasional.
            </p>
        </div>
        
        <!-- Tombol Aksi 1 Row (+ Catat, Import, Unduh CSV) -->
        <div class="flex items-center gap-2.5 shrink-0 flex-nowrap overflow-x-auto pb-1 xl:pb-0">
            <button @click="createModal = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs shadow-sm shadow-amber-500/20 transition whitespace-nowrap shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Catat Prestasi Baru</span>
            </button>

            <button type="button" 
                    @click="importModal = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition whitespace-nowrap shrink-0 cursor-pointer">
                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Import CSV</span>
            </button>

            <a href="{{ route('export.prestasi', ['tahun' => $tahun]) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold transition shadow-2xs whitespace-nowrap shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- Filter & Statistics Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <input type="hidden" name="tahun" value="{{ $tahun }}">

            <div>
                <label class="block text-slate-500 font-semibold mb-1">Cari Siswa / Lomba:</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau lomba..." 
                       class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-slate-500 font-semibold mb-1">Bidang Kejuaraan:</label>
                <select name="bidang" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 outline-none">
                    <option value="">Semua Bidang</option>
                    <option value="Sains & Akademik" {{ $bidang == 'Sains & Akademik' ? 'selected' : '' }}>Sains & Akademik</option>
                    <option value="Olahraga" {{ $bidang == 'Olahraga' ? 'selected' : '' }}>Olahraga</option>
                    <option value="Seni & Budaya" {{ $bidang == 'Seni & Budaya' ? 'selected' : '' }}>Seni & Budaya</option>
                    <option value="Teknologi & Robotik" {{ $bidang == 'Teknologi & Robotik' ? 'selected' : '' }}>Teknologi & Robotik</option>
                    <option value="Keagamaan" {{ $bidang == 'Keagamaan' ? 'selected' : '' }}>Keagamaan</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-500 font-semibold mb-1">Tingkat Kejuaraan:</label>
                <select name="tingkat" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 outline-none">
                    <option value="">Semua Tingkat</option>
                    <option value="Kecamatan" {{ $tingkat == 'Kecamatan' ? 'selected' : '' }}>Kecamatan</option>
                    <option value="Kabupaten" {{ $tingkat == 'Kabupaten' ? 'selected' : '' }}>Kabupaten</option>
                    <option value="Provinsi" {{ $tingkat == 'Provinsi' ? 'selected' : '' }}>Provinsi</option>
                    <option value="Nasional" {{ $tingkat == 'Nasional' ? 'selected' : '' }}>Nasional</option>
                    <option value="Internasional" {{ $tingkat == 'Internasional' ? 'selected' : '' }}>Internasional</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition">
                    Terapkan Filter
                </button>
                <a href="{{ route('tujuan2.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Reset">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Daftar Prestasi (Tabel & Kartu Switcher) -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Rekap Prestasi Siswa</h3>
                <p class="text-xs text-slate-500">Tampilan data kejuaraan dan capaian lomba siswa se-Kabupaten Banjarnegara</p>
            </div>
            <div class="inline-flex items-center p-1 bg-slate-100 rounded-xl text-xs font-semibold self-start sm:self-auto border border-slate-200">
                <button type="button" 
                        @click="viewMode = 'table'; $nextTick(() => window.initSimakDataTables())" 
                        :class="viewMode === 'table' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'" 
                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Tampilan Tabel</span>
                </button>
                <button type="button" 
                        @click="viewMode = 'grid'" 
                        :class="viewMode === 'grid' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'" 
                        class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Tampilan Kartu</span>
                </button>
            </div>
        </div>

        <!-- Mode Tabel Lengkap (dengan Searching, Pagination, dan Sorting) -->
        <div x-show="viewMode === 'table'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs datatable" 
                       data-server-side="true" 
                       data-url="{{ route('tujuan2.index') }}" 
                       data-total="{{ $prestasiList->total() }}" 
                       data-current-page="{{ $prestasiList->currentPage() }}" 
                       data-page-size="10">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                            <th class="py-3.5 px-4" data-column="nama_siswa">Nama Siswa</th>
                            <th class="py-3.5 px-4 font-mono" data-column="nisn">NISN</th>
                            <th class="py-3.5 px-4" data-column="nama_kejuaraan">Nama Kejuaraan</th>
                            <th class="py-3.5 px-4" data-column="bidang">Bidang</th>
                            <th class="py-3.5 px-4 text-center" data-column="tingkat">Tingkat</th>
                            <th class="py-3.5 px-4 text-center" data-column="peringkat">Peringkat</th>
                            <th class="py-3.5 px-4" data-sortable="false">Satuan Pendidikan</th>
                            <th class="py-3.5 px-4 text-center" data-column="tahun">Tahun</th>
                            <th class="py-3.5 px-4 text-center" data-sortable="false">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @include('tujuan2._rows')
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mode Kartu Grid -->
        <div x-show="viewMode === 'grid'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($prestasiList as $pres)
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $pres->tingkat }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                {{ $pres->peringkat }}
                            </span>
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900 text-base leading-snug">{{ $pres->nama_kejuaraan }}</h3>
                            <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ $pres->bidang }}</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5 text-xs">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-bold text-slate-900">{{ $pres->nama_siswa }}</span>
                                @if($pres->nisn)<span class="text-[10px] text-slate-400 font-mono">({{ $pres->nisn }})</span>@endif
                            </div>
                            <p class="text-[11px] text-slate-600 pl-6">
                                {{ $pres->satuanPendidikan->nama_sekolah }} &bull; <span class="font-medium">Kec. {{ $pres->kecamatan->nama }}</span>
                            </p>
                        </div>

                        @if($pres->keterangan)
                        <p class="text-xs text-slate-500 italic line-clamp-2">"{{ $pres->keterangan }}"</p>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                        <span>Tahun {{ $pres->tahun }}</span>
                        @php
                            $rowJsonCard = json_encode([
                                'id' => $pres->id,
                                'nama_siswa' => $pres->nama_siswa,
                                'nisn' => $pres->nisn ?? '',
                                'satuan_pendidikan_id' => $pres->satuan_pendidikan_id,
                                'nama_kejuaraan' => $pres->nama_kejuaraan,
                                'bidang' => $pres->bidang,
                                'tingkat' => $pres->tingkat,
                                'peringkat' => $pres->peringkat,
                                'tahun' => $pres->tahun,
                                'penyelenggara' => $pres->penyelenggara ?? '',
                                'keterangan' => $pres->keterangan ?? '',
                            ]);
                        @endphp
                        <div class="flex items-center gap-1.5">
                            <button type="button" 
                                    @click="activeRow = {{ $rowJsonCard }}; editModal = true" 
                                    class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer" 
                                    title="Edit Data Prestasi">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <form action="{{ route('tujuan2.destroy', $pres->id) }}" method="POST" onsubmit="return confirm('Hapus data prestasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition cursor-pointer" title="Hapus Data">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-12 bg-white rounded-3xl border border-slate-200 p-8 space-y-3">
                    <p class="text-slate-400 text-sm">Tidak ada data prestasi siswa yang sesuai dengan filter.</p>
                </div>
                @endforelse
            </div>

            <div class="pt-2">
                {{ $prestasiList->links() }}
            </div>
        </div>
    </div>

    <!-- Create Modal Form -->
    <div x-show="createModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl border border-slate-100 space-y-5 my-8"
             @click.away="createModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Catat Prestasi Siswa Baru</h3>
                    <p class="text-xs text-slate-500">Input rekaman prestasi kompetisi perwakilan sekolah</p>
                </div>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('tujuan2.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Siswa *</label>
                        <input type="text" name="nama_siswa" required placeholder="Contoh: Budi Santoso" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">NISN (Opsional)</label>
                        <input type="text" name="nisn" placeholder="0012345678" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1">Satuan Pendidikan / Asal Sekolah *</label>
                        <select name="satuan_pendidikan_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Sekolah...</option>
                            @foreach($sekolahList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_sekolah }} ({{ $s->jenjang }} - Kec. {{ $s->kecamatan->nama ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tahun Kejuaraan *</label>
                        <input type="number" name="tahun" value="{{ $tahun }}" required min="2020" max="2035" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Kejuaraan / Kompetisi *</label>
                    <input type="text" name="nama_kejuaraan" required placeholder="Contoh: Olimpiade Sains Nasional Tingkat Provinsi" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Bidang *</label>
                        <select name="bidang" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none">
                            <option value="Sains & Akademik">Sains & Akademik</option>
                            <option value="Olahraga">Olahraga</option>
                            <option value="Seni & Budaya">Seni & Budaya</option>
                            <option value="Teknologi & Robotik">Teknologi & Robotik</option>
                            <option value="Keagamaan">Keagamaan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tingkat *</label>
                        <select name="tingkat" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none">
                            <option value="Kecamatan">Kecamatan</option>
                            <option value="Kabupaten">Kabupaten</option>
                            <option value="Provinsi">Provinsi</option>
                            <option value="Nasional">Nasional</option>
                            <option value="Internasional">Internasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Peringkat *</label>
                        <select name="peringkat" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none">
                            <option value="Juara 1">Juara 1</option>
                            <option value="Juara 2">Juara 2</option>
                            <option value="Juara 3">Juara 3</option>
                            <option value="Harapan 1">Harapan 1</option>
                            <option value="Harapan 2">Harapan 2</option>
                            <option value="Finalis">Finalis</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Penyelenggara (Opsional)</label>
                    <input type="text" name="penyelenggara" placeholder="Contoh: Kemendikbudristek / Dinas Pendidikan" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan / Keterangan</label>
                    <textarea name="keterangan" rows="2" placeholder="Catatan medali atau karya..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-500 shadow-md shadow-blue-600/30 cursor-pointer">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal Form -->
    <div x-show="editModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl border border-slate-100 space-y-5 my-8"
             @click.away="editModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Edit Data Prestasi Siswa</h3>
                    <p class="text-xs text-slate-500">Perbarui rincian capaian prestasi dan kompetisi siswa</p>
                </div>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('tujuan2/prestasi') }}/' + activeRow.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Siswa *</label>
                        <input type="text" name="nama_siswa" x-model="activeRow.nama_siswa" required placeholder="Contoh: Budi Santoso" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">NISN (Opsional)</label>
                        <input type="text" name="nisn" x-model="activeRow.nisn" placeholder="0012345678" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1">Satuan Pendidikan / Asal Sekolah *</label>
                        <select name="satuan_pendidikan_id" x-model="activeRow.satuan_pendidikan_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Sekolah...</option>
                            @foreach($sekolahList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_sekolah }} ({{ $s->jenjang }} - Kec. {{ $s->kecamatan->nama ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tahun Kejuaraan *</label>
                        <input type="number" name="tahun" x-model="activeRow.tahun" required min="2020" max="2035" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Kejuaraan / Kompetisi *</label>
                    <input type="text" name="nama_kejuaraan" x-model="activeRow.nama_kejuaraan" required placeholder="Contoh: Olimpiade Sains Nasional Tingkat Provinsi" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Bidang *</label>
                        <select name="bidang" x-model="activeRow.bidang" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none">
                            <option value="Sains & Akademik">Sains & Akademik</option>
                            <option value="Olahraga">Olahraga</option>
                            <option value="Seni & Budaya">Seni & Budaya</option>
                            <option value="Teknologi & Robotik">Teknologi & Robotik</option>
                            <option value="Keagamaan">Keagamaan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tingkat *</label>
                        <select name="tingkat" x-model="activeRow.tingkat" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none">
                            <option value="Kecamatan">Kecamatan</option>
                            <option value="Kabupaten">Kabupaten</option>
                            <option value="Provinsi">Provinsi</option>
                            <option value="Nasional">Nasional</option>
                            <option value="Internasional">Internasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Peringkat *</label>
                        <select name="peringkat" x-model="activeRow.peringkat" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none">
                            <option value="Juara 1">Juara 1</option>
                            <option value="Juara 2">Juara 2</option>
                            <option value="Juara 3">Juara 3</option>
                            <option value="Harapan 1">Harapan 1</option>
                            <option value="Harapan 2">Harapan 2</option>
                            <option value="Finalis">Finalis</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Penyelenggara (Opsional)</label>
                    <input type="text" name="penyelenggara" x-model="activeRow.penyelenggara" placeholder="Contoh: Kemendikbudristek / Dinas Pendidikan" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan / Keterangan</label>
                    <textarea name="keterangan" x-model="activeRow.keterangan" rows="2" placeholder="Catatan medali atau karya..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-500 shadow-md shadow-blue-600/30 cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Import CSV Prestasi Siswa -->
    <div x-show="importModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-100 space-y-6"
             @click.away="importModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Import Data Prestasi Siswa</h3>
                    <p class="text-xs text-slate-500">Unggah berkas rekapitulasi prestasi siswa berbasis format CSV</p>
                </div>
                <button @click="importModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('import.prestasi') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tahun Kinerja / Kejuaraan <span class="text-rose-500">*</span></label>
                    <input type="number" name="tahun" value="{{ $tahun }}" required min="2020" max="2035" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-xs font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Berkas CSV (.csv) <span class="text-rose-500">*</span></label>
                    <input type="file" name="file_prestasi" required accept=".csv,.txt" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                </div>

                <div class="p-3.5 rounded-2xl bg-indigo-50/50 border border-indigo-100 text-[11px] text-slate-600 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-indigo-900">Belum memiliki format template CSV?</span>
                        <a href="{{ route('import.template', 'prestasi') }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline shrink-0 ml-2 inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Unduh Template
                        </a>
                    </div>
                    <p class="text-[10px] text-slate-500 leading-relaxed">
                        Urutan kolom: <code class="font-mono text-indigo-700 bg-white px-1.5 py-0.5 rounded border border-indigo-200">nama_siswa, nisn, kejuaraan, bidang, tingkat, peringkat, tahun, sekolah</code>
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="importModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-md shadow-indigo-600/30 cursor-pointer">Proses Import Data</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
