@extends('layouts.app', ['title' => 'Data Guru & Sertifikasi Pendidik'])

@section('content')
<div class="space-y-8" x-data="{ createModal: false, editModal: false, deleteModal: false, importModal: false, activeRow: {} }">

    <!-- Header Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-purple-50/80 via-slate-50 to-white text-slate-900 flex flex-col xl:flex-row xl:items-center justify-between gap-5 shadow-xs border border-purple-200/60">
        <div class="space-y-1.5 flex-1 min-w-0 pr-2">
            <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Data Tenaga Pendidik & Sertifikasi Guru</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Manajemen data pokok guru dan tenaga kependidikan (GTK), nomor registrasi NUPTK, status kepegawaian (PNS, PPPK, Honor/GTY), serta kepemilikan Sertifikat Pendidik di Kabupaten Banjarnegara.
            </p>
        </div>
        
        <!-- Action Row (1 Row) -->
        <div class="flex items-center gap-2.5 shrink-0 flex-nowrap overflow-x-auto pb-1 xl:pb-0">
            {{-- Tombol Tambah & Import disembunyikan karena data dialirkan otomatis melalui Sinkronisasi Dapodik --}}
            @if(false)
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
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm shadow-purple-500/25 transition whitespace-nowrap shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Guru Baru</span>
            </button>
            @endif

            <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Sumber: Sinkronisasi Dapodik
            </span>

            <a href="{{ route('export.guru', request()->query()) }}" 
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
            <span class="text-[11px] font-bold uppercase text-slate-400">Total Guru Aktif</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
            <p class="text-[10px] text-slate-500">Semua Satuan Pendidikan</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Sudah Sertifikasi</span>
            <div class="text-xl font-extrabold text-purple-600 mt-1">{{ number_format($stats['sertifikasi']) }}</div>
            <p class="text-[10px] text-slate-500">Sertifikat Profesional</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Belum Sertifikasi</span>
            <div class="text-xl font-extrabold text-slate-700 mt-1">{{ number_format($stats['belum_sertifikasi']) }}</div>
            <p class="text-[10px] text-slate-500">Sasaran Program PPG</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Capaian Daerah</span>
            <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ $stats['persentase'] }}%</div>
            <p class="text-[10px] text-emerald-600 font-bold">Target Renstra: 72%</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Guru ASN (PNS)</span>
            <div class="text-xl font-extrabold text-blue-600 mt-1">{{ number_format($stats['pns']) }}</div>
            <p class="text-[10px] text-slate-500">Pegawai Negeri Sipil</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">PPPK & Non-ASN</span>
            <div class="text-xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['pppk'] + $stats['non_asn']) }}</div>
            <p class="text-[10px] text-slate-500">PPPK: {{ $stats['pppk'] }} | Honor: {{ $stats['non_asn'] }}</p>
        </div>
    </div>

    <!-- Rekapitulasi Menurut Gender -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200/80 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    L
                </div>
                <div>
                    <span class="text-xs font-bold text-blue-950 block">Guru Laki-laki</span>
                    <span class="text-[11px] text-blue-700">Bersertifikasi: <strong>{{ number_format($stats['sertifikasi_l'] ?? 0) }}</strong> ({{ $stats['persen_l'] ?? 0 }}%)</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xl font-black text-blue-900">{{ number_format($stats['total_l'] ?? 0) }}</span>
                <span class="text-[10px] text-blue-600 block">Guru ({{ $stats['total'] > 0 ? round((($stats['total_l'] ?? 0) / $stats['total']) * 100, 1) : 0 }}%)</span>
            </div>
        </div>
        <div class="p-4 rounded-2xl bg-pink-50/60 border border-pink-200/80 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-pink-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    P
                </div>
                <div>
                    <span class="text-xs font-bold text-pink-950 block">Guru Perempuan</span>
                    <span class="text-[11px] text-pink-700">Bersertifikasi: <strong>{{ number_format($stats['sertifikasi_p'] ?? 0) }}</strong> ({{ $stats['persen_p'] ?? 0 }}%)</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xl font-black text-pink-900">{{ number_format($stats['total_p'] ?? 0) }}</span>
                <span class="text-[10px] text-pink-600 block">Guru ({{ $stats['total'] > 0 ? round((($stats['total_p'] ?? 0) / $stats['total']) * 100, 1) : 0 }}%)</span>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('guru.index') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-600 uppercase text-[10px] tracking-wider">Filter Data Guru:</span>
            </div>
            <div>
                <select name="jenis_kelamin" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-bold focus:ring-2 focus:ring-purple-500">
                    <option value="">Jenis Kelamin (Semua)</option>
                    <option value="L" {{ ($filterJenisKelamin ?? '') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ ($filterJenisKelamin ?? '') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                </select>
            </div>
            <div>
                <select name="kecamatan_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatanList as $k)
                        <option value="{{ $k->id }}" {{ $filterKecamatan == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="jenjang" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Jenjang Sekolah</option>
                    @foreach(['PAUD', 'TK', 'SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PKBM'] as $j)
                        <option value="{{ $j }}" {{ $filterJenjang === $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status_kepegawaian" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-purple-500">
                    <option value="">Status Kepegawaian (Semua)</option>
                    @foreach(['PNS', 'PPPK', 'GTT', 'GTY', 'Honor'] as $st)
                        <option value="{{ $st }}" {{ $filterStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="sertifikasi" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-purple-500">
                    <option value="">Status Sertifikasi (Semua)</option>
                    <option value="1" {{ $filterSertifikasi === '1' ? 'selected' : '' }}>Sudah Sertifikasi</option>
                    <option value="0" {{ $filterSertifikasi === '0' ? 'selected' : '' }}>Belum Sertifikasi</option>
                </select>
            </div>
            @if($filterKecamatan || $filterJenjang || $filterStatus || ($filterSertifikasi !== null && $filterSertifikasi !== '') || ($filterJenisKelamin ?? ''))
                <a href="{{ route('guru.index') }}" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold transition">
                    &times; Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Tenaga Pendidik & Sertifikasi</h3>
                <p class="text-xs text-slate-500">Menampilkan {{ number_format($guruList->total()) }} guru dan tenaga kependidikan</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" 
                   data-server-side="true" 
                   data-url="{{ route('guru.index') }}" 
                   data-total="{{ $guruList->total() }}" 
                   data-current-page="{{ $guruList->currentPage() }}" 
                   data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4" data-column="nama">Nama Lengkap</th>
                        <th class="py-3.5 px-4 font-mono" data-column="nuptk">NUPTK / NIP</th>
                        <th class="py-3.5 px-4" data-sortable="false">Satuan Pendidikan</th>
                        <th class="py-3.5 px-4" data-sortable="false">Kecamatan</th>
                        <th class="py-3.5 px-4 text-center" data-column="status_kepegawaian">Status</th>
                        <th class="py-3.5 px-4" data-column="bidang_studi">Bidang Studi</th>
                        <th class="py-3.5 px-4 text-center" data-column="sudah_sertifikasi">Sertifikasi</th>
                        @if(false)
                        <th class="py-3.5 px-4 text-center" data-sortable="false">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @include('guru._rows')
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: Tambah Guru Baru -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="createModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-xl border border-slate-200 space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Tambah Data Guru / Tenaga Pendidik</h3>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>
            
            <form action="{{ route('guru.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Satuan Pendidikan Induk *</label>
                    <select name="satuan_pendidikan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                        <option value="">-- Pilih Sekolah Asal --</option>
                        @foreach($sekolahList as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_sekolah }} (NPSN: {{ $s->npsn }}) [{{ $s->jenjang }}]</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap & Gelar *</label>
                    <input type="text" name="nama" required placeholder="Contoh: Budi Santoso, S.Pd., M.Pd." class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NUPTK (16 Digit)</label>
                        <input type="text" name="nuptk" maxlength="30" placeholder="Contoh: 8451763665200023" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NIP (Jika ASN)</label>
                        <input type="text" name="nip" maxlength="30" placeholder="198504122010011012" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Kepegawaian *</label>
                        <select name="status_kepegawaian" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                            <option value="PNS">PNS</option>
                            <option value="PPPK">PPPK</option>
                            <option value="GTT">GTT (Honorer)</option>
                            <option value="GTY">GTY (Yayasan)</option>
                            <option value="Honor">Honor Sekolah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Sertifikasi *</label>
                        <select name="sudah_sertifikasi" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500 font-bold">
                            <option value="1">Sudah Sertifikasi</option>
                            <option value="0" selected>Belum Sertifikasi</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bidang Studi / Mata Pelajaran</label>
                    <input type="text" name="bidang_studi" placeholder="Contoh: Guru Kelas / Matematika / Bahasa Inggris" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-slate-600 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold transition shadow-sm">Simpan Guru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Edit Guru -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="editModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-xl border border-slate-200 space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Edit Data Guru</h3>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>
            
            <form :action="'/guru-pendidik/' + activeRow.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Satuan Pendidikan Induk *</label>
                    <select name="satuan_pendidikan_id" x-model="activeRow.satuan_pendidikan_id" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                        @foreach($sekolahList as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_sekolah }} [{{ $s->jenjang }}]</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lengkap & Gelar *</label>
                    <input type="text" name="nama" x-model="activeRow.nama" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NUPTK</label>
                        <input type="text" name="nuptk" x-model="activeRow.nuptk" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NIP</label>
                        <input type="text" name="nip" x-model="activeRow.nip" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" x-model="activeRow.jenis_kelamin" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Kepegawaian *</label>
                        <select name="status_kepegawaian" x-model="activeRow.status_kepegawaian" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                            <option value="PNS">PNS</option>
                            <option value="PPPK">PPPK</option>
                            <option value="GTT">GTT</option>
                            <option value="GTY">GTY</option>
                            <option value="Honor">Honor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Sertifikasi *</label>
                        <select name="sudah_sertifikasi" :value="activeRow.sudah_sertifikasi ? '1' : '0'" @change="activeRow.sudah_sertifikasi = ($event.target.value === '1')" required class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500 font-bold">
                            <option value="1">Sudah Sertifikasi</option>
                            <option value="0">Belum Sertifikasi</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bidang Studi</label>
                    <input type="text" name="bidang_studi" x-model="activeRow.bidang_studi" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-slate-600 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Hapus Guru -->
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
                Apakah Anda yakin ingin menghapus data tenaga pendidik <strong class="text-slate-900" x-text="activeRow.nama"></strong>?
            </p>

            <form :action="'/guru-pendidik/' + activeRow.id" method="POST" class="pt-2 flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-bold text-xs text-slate-600 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition shadow-sm">Hapus Guru</button>
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
                        <h3 class="text-base font-bold text-slate-900">Import Data Guru & Tendik</h3>
                        <p class="text-[11px] text-slate-500">Unggah berkas CSV data pendidik</p>
                    </div>
                </div>
                <button type="button" @click="importModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <!-- Petunjuk & Unduh Template -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 text-xs space-y-2">
                <p class="font-bold text-slate-800">Petunjuk Format Kolom CSV:</p>
                <ul class="list-disc list-inside text-slate-600 space-y-1 text-[11px]">
                    <li>Kolom utama: <code>nama</code>, <code>npsn</code>, <code>nama_sekolah</code>, <code>nip</code>, <code>nuptk</code>, <code>jenis_kelamin</code>, <code>status_kepegawaian</code>, <code>sudah_sertifikasi</code>, <code>bidang_studi</code>.</li>
                    <li>Status sertifikasi diisi: <strong>Ya / Tidak</strong> atau <strong>1 / 0</strong>.</li>
                </ul>
                <div class="pt-2">
                    <a href="{{ route('import.template', ['type' => 'guru']) }}" class="inline-flex items-center gap-1.5 font-bold text-emerald-700 hover:text-emerald-800 underline text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh Template CSV Guru & Tendik (.csv)</span>
                    </a>
                </div>
            </div>

            <!-- Form Upload -->
            <form action="{{ route('guru.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Pilih Berkas CSV *</label>
                    <input type="file" name="file_guru" required accept=".csv,.txt" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
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
