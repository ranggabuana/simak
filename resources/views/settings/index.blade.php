@extends('layouts.app', ['title' => 'Pengaturan Aplikasi SIMAK'])

@section('content')
<div class="space-y-6 max-w-6xl mx-auto" x-data="{ currentTab: 'instansi' }">

    <!-- Header Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-blue-50/80 via-slate-50 to-white text-slate-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-xs border border-blue-200/60">
        <div class="space-y-1.5 flex-1 min-w-0 pr-2">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800 tracking-wider">
                    Tata Kelola & Konfigurasi Sistem
                </span>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Pengaturan Aplikasi SIMAK</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kelola identitas instansi untuk kop surat resmi, nama pejabat penandatangan laporan, parameter capaian Standar Pelayanan Minimal (SPM), serta notifikasi pengumuman dashboard.
            </p>
        </div>
        <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Database Aktif
            </span>
        </div>
    </div>

    <!-- Main Settings Form Container -->
    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Horizontal Tab Navigation Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs px-4 sm:px-6">
            <nav class="flex space-x-4 sm:space-x-8 overflow-x-auto no-scrollbar" aria-label="Tabs">
                <button type="button" 
                        @click="currentTab = 'instansi'" 
                        :class="currentTab === 'instansi' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium'" 
                        class="py-3.5 px-1 border-b-2 text-xs sm:text-sm transition-colors flex items-center gap-2 cursor-pointer shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>1. Identitas & Kop Dinas</span>
                </button>

                <button type="button" 
                        @click="currentTab = 'pejabat'" 
                        :class="currentTab === 'pejabat' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium'" 
                        class="py-3.5 px-1 border-b-2 text-xs sm:text-sm transition-colors flex items-center gap-2 cursor-pointer shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>2. Pejabat Penandatangan</span>
                </button>

                <button type="button" 
                        @click="currentTab = 'kinerja'" 
                        :class="currentTab === 'kinerja' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium'" 
                        class="py-3.5 px-1 border-b-2 text-xs sm:text-sm transition-colors flex items-center gap-2 cursor-pointer shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>3. Standar Kinerja & SPM</span>
                </button>
            </nav>
        </div>

        <!-- ==================== TAB 1: IDENTITAS & KOP DINAS ==================== -->
        <div x-show="currentTab === 'instansi'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Identitas Dinas & Format Kop Surat Dokumen Resmi</h3>
                <p class="text-xs text-slate-500">Konfigurasi nama instansi dan kontak resmi yang dicetak pada kop surat laporan PDF (APK/APM/APS) dan LKjIP.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Instansi -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nama Instansi / Dinas <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="nama_instansi" 
                           required 
                           value="{{ old('nama_instansi', $appSettings['nama_instansi'] ?? 'Dinas Pendidikan, Kepemudaan dan Olahraga Kab. Banjarnegara') }}" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <p class="text-[11px] text-slate-400 mt-1.5">Tampil sebagai judul utama instansi pada lembar pengesahan dan kop dokumen resmi.</p>
                </div>

                <!-- Alamat Kantor -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Alamat Lengkap Kantor Dinas
                    </label>
                    <input type="text" 
                           name="alamat_instansi" 
                           value="{{ old('alamat_instansi', $appSettings['alamat_instansi'] ?? 'Jalan Mayjend Soetojo No. 51 Banjarnegara 53412') }}" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <p class="text-[11px] text-slate-400 mt-1.5">Dicetak pada baris alamat kop surat laporan.</p>
                </div>

                <!-- Nomor Telepon -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nomor Telepon Kantor
                    </label>
                    <input type="text" 
                           name="telepon_instansi" 
                           value="{{ old('telepon_instansi', $appSettings['telepon_instansi'] ?? '(0286) 591244') }}" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Pos-el / Email Resmi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Alamat Pos-el / Email Resmi
                    </label>
                    <input type="email" 
                           name="email_instansi" 
                           value="{{ old('email_instansi', $appSettings['email_instansi'] ?? 'dindikpora@banjarnegarakab.go.id') }}" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Website Resmi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Laman / Website Resmi
                    </label>
                    <input type="text" 
                           name="website_instansi" 
                           value="{{ old('website_instansi', $appSettings['website_instansi'] ?? 'dindikpora.banjarnegarakab.go.id') }}" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Kota Penetapan Dokumen -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Kota Penetapan Dokumen <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="kota_dokumen" 
                           required 
                           value="{{ old('kota_dokumen', $appSettings['kota_dokumen'] ?? 'Banjarnegara') }}" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <p class="text-[11px] text-slate-400 mt-1.5">Contoh: Banjarnegara &rarr; menghasilkan teks penanggalan &ldquo;Banjarnegara, [Tanggal Hari Ini]&rdquo;</p>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 2: PEJABAT PENANDATANGAN ==================== -->
        <div x-show="currentTab === 'pejabat'" class="space-y-6">
            <!-- Bagian A: Kepala Dinas -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <span>A. Kepala Dinas (Pimpinan Penandatangan Utama)</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pejabat eksekutif yang membubuhkan tanda tangan persetujuan akhir pada dokumen LKjIP dan laporan indikator kinerja.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase bg-blue-50 text-blue-700 border border-blue-200/60">
                        Pimpinan Eksekutif
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="kepala_dinas_nama" 
                               required 
                               value="{{ old('kepala_dinas_nama', $appSettings['kepala_dinas_nama'] ?? 'Dr. H. Teguh Handoko, S.Sos., M.Si.') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            NIP Pegawai <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="kepala_dinas_nip" 
                               required 
                               value="{{ old('kepala_dinas_nip', $appSettings['kepala_dinas_nip'] ?? '19680715 199203 1 005') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pangkat / Golongan Ruang
                        </label>
                        <input type="text" 
                               name="kepala_dinas_pangkat" 
                               value="{{ old('kepala_dinas_pangkat', $appSettings['kepala_dinas_pangkat'] ?? 'Pembina Utama Muda (IV/c)') }}" 
                               placeholder="Contoh: Pembina Utama Muda (IV/c)" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nama Jabatan Resmi
                        </label>
                        <input type="text" 
                               name="kepala_dinas_jabatan" 
                               value="{{ old('kepala_dinas_jabatan', $appSettings['kepala_dinas_jabatan'] ?? 'Kepala Dinas Pendidikan, Kepemudaan dan Olahraga') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>
            </div>

            <!-- Bagian B: Pejabat Verifikator -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                            <span>B. Pejabat Verifikator / Perencana (Paraf Hierarki)</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pejabat perencana yang melakukan verifikasi data teknis di kolom tanda tangan sebelah kiri laporan.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                        Verifikator Teknis
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nama Pejabat / Unit Kerja
                        </label>
                        <input type="text" 
                               name="perencana_nama" 
                               value="{{ old('perencana_nama', $appSettings['perencana_nama'] ?? 'Subbag Perencanaan & Evaluasi') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            NIP Pejabat
                        </label>
                        <input type="text" 
                               name="perencana_nip" 
                               value="{{ old('perencana_nip', $appSettings['perencana_nip'] ?? '19790512 200501 1 008') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Jabatan Kedinasan
                        </label>
                        <input type="text" 
                               name="perencana_jabatan" 
                               value="{{ old('perencana_jabatan', $appSettings['perencana_jabatan'] ?? 'Kasubbag Program dan Evaluasi Kinerja') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 3: STANDAR KINERJA & SPM ==================== -->
        <div x-show="currentTab === 'kinerja'" class="space-y-6">
            <!-- Standar Kinerja & SPM Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Standar Acuan Kinerja & Ambang Batas SPM</h3>
                <p class="text-xs text-slate-500">Parameter kuantitatif batas tuntas indikator pendidikan dan tahun kinerja default aplikasi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tahun Default -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tahun Anggaran Aktif (Default) <span class="text-rose-500">*</span>
                    </label>
                    <select name="tahun_anggaran_default" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        @foreach(range(2023, 2030) as $th)
                            <option value="{{ $th }}" {{ (int) old('tahun_anggaran_default', $appSettings['tahun_anggaran_default'] ?? 2025) === $th ? 'selected' : '' }}>
                                Tahun Anggaran {{ $th }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1.5">Tahun yang langsung terbuka secara otomatis saat pengguna login tanpa memilih URL ?tahun=.</p>
                </div>

                <!-- Periode Renstra -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Periode Renstra Induk <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="periode_renstra" 
                           required 
                           value="{{ old('periode_renstra', $appSettings['periode_renstra'] ?? '2025–2029') }}" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <p class="text-[11px] text-slate-400 mt-1.5">Jangka waktu rencana strategis lima tahunan DINDIKPORA Banjarnegara.</p>
                </div>

                <!-- Ambang SPM -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Ambang Batas Tuntas SPM (%) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="ambang_batas_spm" 
                               step="0.01" 
                               min="0" 
                               max="100" 
                               required 
                               value="{{ old('ambang_batas_spm', $appSettings['ambang_batas_spm'] ?? 85.00) }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <span class="absolute right-4 top-2.5 text-xs font-bold text-slate-400">%</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5">Capaian rata-rata kecamatan di atas nilai ini otomatis memperoleh badge hijau <strong>Tuntas SPM</strong> pada laporan.</p>
                </div>

                <!-- Toleransi Deviasi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Batas Toleransi Deviasi Kinerja (%)
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="toleransi_deviasi" 
                               step="0.01" 
                               min="0" 
                               max="50" 
                               value="{{ old('toleransi_deviasi', $appSettings['toleransi_deviasi'] ?? 5.00) }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <span class="absolute right-4 top-2.5 text-xs font-bold text-slate-400">%</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5">Ambang batas deviasi kesenjangan antara capaian riil terhadap target Renstra.</p>
                </div>
            </div>
            </div>

            <!-- Pengumuman Banner Dashboard -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                            <span>Banner Pengumuman Sistem Global (Opsional)</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tampilkan pengumuman resmi di bilah atas aplikasi untuk semua operator dan pengguna SIMAK.</p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer bg-sky-50 hover:bg-sky-100 text-sky-800 px-3.5 py-1.5 rounded-xl border border-sky-200 transition">
                        <input type="checkbox" 
                               name="banner_aktif" 
                               value="1" 
                               {{ old('banner_aktif', $appSettings['banner_aktif'] ?? '0') === '1' ? 'checked' : '' }}
                               class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500">
                        <span class="text-xs font-extrabold uppercase tracking-wide">Aktifkan Banner</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Tipe Tampilan Banner
                        </label>
                        <select name="banner_tipe" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="info" {{ old('banner_tipe', $appSettings['banner_tipe'] ?? 'info') === 'info' ? 'selected' : '' }}>Informasi Umum (Biru)</option>
                            <option value="warning" {{ old('banner_tipe', $appSettings['banner_tipe'] ?? 'info') === 'warning' ? 'selected' : '' }}>Peringatan Penting (Kuning)</option>
                            <option value="danger" {{ old('banner_tipe', $appSettings['banner_tipe'] ?? 'info') === 'danger' ? 'selected' : '' }}>Mendesak / Peringatan (Merah)</option>
                            <option value="success" {{ old('banner_tipe', $appSettings['banner_tipe'] ?? 'info') === 'success' ? 'selected' : '' }}>Pemberitahuan Sukses (Hijau)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Teks Pesan Pengumuman
                        </label>
                        <input type="text" 
                               name="banner_pesan" 
                               placeholder="Contoh: Rekonsiliasi data kependudukan dan Dapodik semester 1 berakhir pada 30 November 2026." 
                               value="{{ old('banner_pesan', $appSettings['banner_pesan'] ?? '') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Action Bar -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Perubahan tersimpan permanen di database SIMAK dan otomatis aktif ke seluruh modul.</span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="{{ route('dashboard') }}" 
                   class="px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition text-center cursor-pointer">
                    Kembali ke Dashboard
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Seluruh Pengaturan</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

