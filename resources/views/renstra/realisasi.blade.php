@extends('layouts.app', ['title' => 'Realisasi Capaian Kinerja'])

@section('content')
<div class="space-y-8" x-data="{ 
    infoModal: false,
    createModal: false, 
    editModal: false, 
    deleteModal: false, 
    detailModal: false,
    activeRow: {}, 
    deleteRow: { id: null, tahun: '' },
    rows: @js($realisasiList->keyBy('id')),
    openDetail(id) {
        this.activeRow = this.rows[id] || {};
        this.detailModal = true;
    },
    openEdit(id) {
        this.activeRow = this.rows[id] || {};
        this.editModal = true;
    },
    openDelete(id, tahun) {
        this.deleteRow = { id: id, tahun: tahun };
        this.deleteModal = true;
    }
}">

    <!-- Header Banner (Light Style) -->
    <div class="p-6 sm:p-7 rounded-3xl bg-gradient-to-r from-emerald-50/80 via-slate-50 to-white text-slate-900 shadow-xs border border-emerald-200/70 space-y-4">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 tracking-wider border border-emerald-200">Realisasi SAKIP</span>
                    <h2 class="text-lg font-bold text-slate-900">Realisasi Capaian IKK Renstra, AKIP, RLS & HLS</h2>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pemantauan dan penetapan realisasi capaian kinerja tahun berjalan (Tahun N) dibandingkan dengan capaian tahun sebelumnya (Tahun N-1) untuk dokumen pelaporan LAKIP / LKjIP resmi.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <!-- Tombol Buka Popup Modal Info Kegunaan & Alur Data -->
                <button type="button" 
                        @click="infoModal = true"
                        class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white hover:bg-emerald-50 text-emerald-700 hover:text-emerald-800 border border-emerald-300 font-bold text-xs shadow-2xs hover:shadow-xs transition shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Info Kegunaan & Alur Data</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-[10px] text-emerald-800 font-extrabold">Panduan</span>
                </button>

                <button @click="createModal = true" 
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/25 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Input Realisasi Baru</span>
                </button>
            </div>
        </div>

        <!-- Realisasi Compact Flow Reference Box -->
        <div class="p-3 bg-white/95 rounded-2xl border border-emerald-100 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-2.5 text-xs text-slate-700">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2 py-0.5 rounded-md bg-emerald-100/80 text-emerald-800 text-[10px] font-extrabold uppercase">Pelaporan SAKIP & LKjIP</span>
                <span>Menu ini mengelola <strong>indikator makro dinas</strong> (AKIP, RLS, HLS, IKK %) dan analisis evaluasi kualitatif untuk dokumen resmi LKjIP dan ringkasan eksekutif Dashboard.</span>
            </div>
            <div class="text-[11px] text-slate-500 flex items-center gap-2 shrink-0">
                <span>Data Makro & Evaluasi</span>
                <span class="text-slate-300">&bull;</span>
                <button type="button" @click="infoModal = true" class="text-emerald-600 font-bold hover:underline cursor-pointer">
                    Lihat Rincian &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Realisasi Matrix DataTable Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Matriks Realisasi Capaian Kinerja SAKIP & Renstra</h3>
                <p class="text-xs text-slate-500">Tabel rekapitulasi realisasi capaian indikator kinerja SAKIP, AKIP, RLS, dan HLS per tahun anggaran</p>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200/80">
                    Total: <strong class="text-slate-900">{{ $realisasiList->count() }} Tahun</strong>
                </span>
                <button @click="createModal = true" 
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs border border-emerald-200 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Data Realisasi</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">Tahun</th>
                        <th class="py-3.5 px-4 text-center">IKK Renstra</th>
                        <th class="py-3.5 px-4 text-center">Nilai AKIP (N)</th>
                        <th class="py-3.5 px-4 text-center">Nilai AKIP (N-1)</th>
                        <th class="py-3.5 px-4 text-center">Pertumbuhan AKIP</th>
                        <th class="py-3.5 px-4 text-center">RLS Th. N</th>
                        <th class="py-3.5 px-4 text-center">HLS Th. N</th>
                        <th class="py-3.5 px-4 text-center">Status SAKIP</th>
                        <th class="py-3.5 px-4 text-center" data-sortable="false">Catatan & Saran</th>
                        <th class="py-3.5 px-4 text-center" data-sortable="false">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($realisasiList as $r)
                    @php
                        $deltaAkip = $r->capaian_akip_n - $r->capaian_akip_prev;
                        $statusBg = match($r->evaluasi_status) {
                            'Sangat Baik' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Baik' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'Cukup' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <!-- Tahun -->
                        <td class="py-4 px-4 font-extrabold text-emerald-600 text-sm whitespace-nowrap">
                            {{ $r->tahun }}
                        </td>

                        <!-- Capaian IKK Renstra -->
                        <td class="py-4 px-4 text-center font-bold text-blue-700 whitespace-nowrap">
                            {{ number_format($r->capaian_ikk_renstra, 2) }}%
                        </td>

                        <!-- Nilai AKIP Tahun N -->
                        <td class="py-4 px-4 text-center font-extrabold text-indigo-700 whitespace-nowrap">
                            {{ number_format($r->capaian_akip_n, 2) }}
                        </td>

                        <!-- Nilai AKIP Tahun N-1 -->
                        <td class="py-4 px-4 text-center text-slate-500 font-medium whitespace-nowrap">
                            {{ number_format($r->capaian_akip_prev, 2) }}
                        </td>

                        <!-- Pertumbuhan AKIP (Delta) -->
                        <td class="py-4 px-4 text-center font-bold whitespace-nowrap {{ $deltaAkip >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $deltaAkip >= 0 ? '+' : '' }}{{ number_format($deltaAkip, 2) }}
                        </td>

                        <!-- RLS Tahun N -->
                        <td class="py-4 px-4 text-center font-mono font-medium text-slate-800 whitespace-nowrap">
                            {{ number_format($r->capaian_rls_n, 2) }} Th
                        </td>

                        <!-- HLS Tahun N -->
                        <td class="py-4 px-4 text-center font-mono font-medium text-slate-800 whitespace-nowrap">
                            {{ number_format($r->capaian_hls_n, 2) }} Th
                        </td>

                        <!-- Status Evaluasi SAKIP -->
                        <td class="py-4 px-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $statusBg }}">
                                {{ $r->evaluasi_status }}
                            </span>
                        </td>

                        <!-- Catatan & Rekomendasi (Detail Button) -->
                        <td class="py-4 px-4 text-center whitespace-nowrap">
                            @if($r->catatan_evaluasi || $r->rekomendasi)
                                <button @click="openDetail({{ $r->id }})" 
                                        type="button"
                                        title="Lihat Catatan Evaluasi & Rekomendasi Tahun {{ $r->tahun }}"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Lihat Catatan</span>
                                </button>
                            @else
                                <span class="text-slate-400 text-[11px] italic">Belum ada</span>
                            @endif
                        </td>

                        <!-- Aksi (Edit & Hapus) -->
                        <td class="py-4 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openEdit({{ $r->id }})" 
                                        type="button"
                                        title="Edit Realisasi Tahun {{ $r->tahun }}"
                                        class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="openDelete({{ $r->id }}, '{{ $r->tahun }}')" 
                                        type="button"
                                        title="Hapus Realisasi Tahun {{ $r->tahun }}"
                                        class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <div class="max-w-xs mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800">Belum Ada Data Realisasi</h4>
                                <p class="text-xs text-slate-500">Mulai inputkan realisasi capaian indikator kinerja SAKIP untuk tahun pelaporan yang relevan.</p>
                                <button @click="createModal = true" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-md shadow-emerald-600/20 hover:bg-emerald-700 transition">
                                    Input Realisasi Pertama
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: DETAIL CATATAN & REKOMENDASI -->
    <div x-show="detailModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="detailModal = false">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-7 space-y-6 shadow-2xl border border-slate-200"
             @click.away="detailModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Catatan Analisis & Rekomendasi SAKIP</h3>
                        <p class="text-xs text-slate-500">Tahun Kinerja Anggaran: <span class="font-bold text-emerald-600" x-text="activeRow.tahun"></span></p>
                    </div>
                </div>
                <button type="button" @click="detailModal = false" class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer">&times;</button>
            </div>

            <div class="space-y-4 text-xs">
                <!-- Status Evaluasi -->
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="font-medium text-slate-600">Predikat Evaluasi Akhir:</span>
                    <span class="px-3 py-1 rounded-full font-bold text-xs bg-emerald-100 text-emerald-800" x-text="activeRow.evaluasi_status"></span>
                </div>

                <!-- Catatan Analisis -->
                <div class="space-y-1.5 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="font-bold text-slate-800 uppercase text-[11px] tracking-wide flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        Catatan Analisis Kinerja
                    </span>
                    <p class="text-slate-700 leading-relaxed whitespace-pre-line text-xs" x-text="activeRow.catatan_evaluasi || 'Tidak ada catatan analisis yang dicantumkan.'"></p>
                </div>

                <!-- Rekomendasi Kebijakan -->
                <div class="space-y-1.5 p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100">
                    <span class="font-bold text-emerald-900 uppercase text-[11px] tracking-wide flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        Rekomendasi Kebijakan Tindak Lanjut
                    </span>
                    <p class="text-slate-700 leading-relaxed whitespace-pre-line text-xs" x-text="activeRow.rekomendasi || 'Tidak ada rekomendasi tindak lanjut yang dicantumkan.'"></p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" 
                        @click="detailModal = false" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition cursor-pointer">
                    Tutup
                </button>
                <button type="button" 
                        @click="detailModal = false; editModal = true" 
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/25 transition cursor-pointer">
                    Edit Data Ini
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: INPUT REALISASI BARU -->
    <div x-show="createModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
         @keydown.escape.window="createModal = false">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-2xl w-full shadow-2xl border border-slate-100 space-y-6 my-8 max-h-[90vh] overflow-y-auto"
             @click.away="createModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base sm:text-lg text-slate-900">Input Data Realisasi Capaian Kinerja Baru</h3>
                        <p class="text-xs text-slate-500">Pencatatan resmi indikator capaian Renstra & AKIP untuk pelaporan SAKIP</p>
                    </div>
                </div>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('renstra.realisasi.store') }}" method="POST" class="space-y-5 text-xs">
                @csrf

                <!-- Tahun Kinerja -->
                <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <label class="block font-bold text-slate-800 text-xs">
                            Tahun Kinerja Realisasi <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500">Rentang tahun: 2020 – 2035</span>
                    </div>
                    <div class="max-w-xs">
                        <input type="number" 
                               name="tahun" 
                               value="{{ old('tahun', $suggestedNextTahun ?? 2026) }}" 
                               min="2020" 
                               max="2035" 
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-emerald-700 bg-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <p class="text-[11px] text-slate-500">Pastikan data realisasi untuk tahun tersebut belum pernah diinputkan sebelumnya.</p>
                </div>

                <!-- Bagian 1: IKK Renstra & Status Evaluasi -->
                <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-3">
                    <h4 class="font-bold text-blue-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        Capaian IKK Renstra & Status SAKIP
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Capaian IKK Renstra (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="capaian_ikk_renstra" value="{{ old('capaian_ikk_renstra', '88.50') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Status Penilaian SAKIP <span class="text-rose-500">*</span></label>
                            <select name="evaluasi_status" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white font-medium">
                                <option value="Sangat Baik" {{ old('evaluasi_status') === 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                <option value="Baik" {{ old('evaluasi_status', 'Baik') === 'Baik' ? 'selected' : '' }}>Baik</option>
                                <option value="Cukup" {{ old('evaluasi_status') === 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                <option value="Kurang" {{ old('evaluasi_status') === 'Kurang' ? 'selected' : '' }}>Kurang</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Nilai AKIP Tahun N & N-1 -->
                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-3">
                    <h4 class="font-bold text-indigo-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        Nilai Evaluasi AKIP DINDIKPORA (Poin)
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Nilai AKIP Tahun N (Poin) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="capaian_akip_n" value="{{ old('capaian_akip_n', '78.20') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Nilai AKIP Tahun N-1 (Poin) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="capaian_akip_prev" value="{{ old('capaian_akip_prev', '76.80') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        </div>
                    </div>
                </div>

                <!-- Bagian 3: RLS & HLS -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wide flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                        Indikator Makro IPM: RLS & HLS (BPS)
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">RLS Tahun N (Th) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="capaian_rls_n" value="{{ old('capaian_rls_n', '7.75') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">RLS Tahun N-1 (Th) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="capaian_rls_prev" value="{{ old('capaian_rls_prev', '7.60') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">HLS Tahun N (Th) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="capaian_hls_n" value="{{ old('capaian_hls_n', '13.70') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">HLS Tahun N-1 (Th) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="capaian_hls_prev" value="{{ old('capaian_hls_prev', '13.55') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        </div>
                    </div>
                </div>

                <!-- Bagian 4: Catatan & Rekomendasi -->
                <div class="space-y-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Catatan Analisis Kinerja</label>
                        <textarea name="catatan_evaluasi" rows="2" placeholder="Faktor pendukung keberhasilan atau kendala capaian program..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ old('catatan_evaluasi') }}</textarea>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Rekomendasi Kebijakan Tindak Lanjut</label>
                        <textarea name="rekomendasi" rows="2" placeholder="Arahan alokasi anggaran, strategi dinas, atau intervensi prioritas..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">{{ old('rekomendasi') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="createModal = false" class="px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/30 transition cursor-pointer">
                        Simpan Realisasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: PERBARUI REALISASI KINERJA -->
    <div x-show="editModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
         @keydown.escape.window="editModal = false">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl border border-slate-100 space-y-5 my-8 max-h-[90vh] overflow-y-auto"
             @click.away="editModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-base sm:text-lg text-slate-900">Perbarui Realisasi Kinerja</h3>
                    <p class="text-xs text-slate-500">Tahun Kinerja Anggaran: <span class="font-bold text-blue-600" x-text="activeRow.tahun"></span></p>
                </div>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('/renstra/realisasi') }}/' + activeRow.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Capaian IKK Renstra (%) *</label>
                        <input type="number" step="0.01" name="capaian_ikk_renstra" :value="activeRow.capaian_ikk_renstra" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status Evaluasi *</label>
                        <select name="evaluasi_status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            <option value="Sangat Baik" :selected="activeRow.evaluasi_status === 'Sangat Baik'">Sangat Baik</option>
                            <option value="Baik" :selected="activeRow.evaluasi_status === 'Baik'">Baik</option>
                            <option value="Cukup" :selected="activeRow.evaluasi_status === 'Cukup'">Cukup</option>
                            <option value="Kurang" :selected="activeRow.evaluasi_status === 'Kurang'">Kurang</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nilai AKIP Tahun N (Poin) *</label>
                        <input type="number" step="0.01" name="capaian_akip_n" :value="activeRow.capaian_akip_n" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nilai AKIP Tahun N-1 (Poin) *</label>
                        <input type="number" step="0.01" name="capaian_akip_prev" :value="activeRow.capaian_akip_prev" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">RLS Tahun N</label>
                        <input type="number" step="0.01" name="capaian_rls_n" :value="activeRow.capaian_rls_n" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">RLS Tahun N-1</label>
                        <input type="number" step="0.01" name="capaian_rls_prev" :value="activeRow.capaian_rls_prev" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">HLS Tahun N</label>
                        <input type="number" step="0.01" name="capaian_hls_n" :value="activeRow.capaian_hls_n" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">HLS Tahun N-1</label>
                        <input type="number" step="0.01" name="capaian_hls_prev" :value="activeRow.capaian_hls_prev" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Analisis Kinerja</label>
                    <textarea name="catatan_evaluasi" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs" x-text="activeRow.catatan_evaluasi"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Rekomendasi Kebijakan Tindak Lanjut</label>
                    <textarea name="rekomendasi" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs" x-text="activeRow.rekomendasi"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: KONFIRMASI HAPUS REALISASI -->
    <div x-show="deleteModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="deleteModal = false">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-5 shadow-2xl border border-slate-200 text-center"
             @click.away="deleteModal = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-slate-900">Konfirmasi Hapus Realisasi</h3>
                <p class="text-xs text-slate-500">
                    Apakah Anda yakin ingin menghapus data Realisasi Capaian Kinerja untuk <strong class="text-slate-900">Tahun <span x-text="deleteRow.tahun"></span></strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <form method="POST" :action="'{{ url('/renstra/realisasi') }}/' + deleteRow.id" class="flex items-center justify-center gap-2.5 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" 
                        @click="deleteModal = false" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-500/25 transition cursor-pointer">
                    Ya, Hapus Data
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL: INFO KEGUNAAN & ALUR DATA REALISASI CAPAIAN KINERJA -->
    <div x-show="infoModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="infoModal = false">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto"
             @click.away="infoModal = false">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-slate-100 pb-3 gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-emerald-50 text-emerald-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-emerald-600 text-white tracking-wider">Panduan SAKIP</span>
                            <h3 class="text-base font-bold text-slate-900">Info Kegunaan & Alur Data Realisasi Kinerja</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Penjelasan fungsi pencatatan realisasi capaian SAKIP, evaluasi, dan integrasinya ke sistem SIMAK</p>
                    </div>
                </div>
                <button type="button" 
                        @click="infoModal = false" 
                        class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content Body -->
            <div class="space-y-4 text-xs text-slate-700">
                <!-- Baris 1: Konsep & Fungsi Utama -->
                <div class="p-4 bg-emerald-50/60 rounded-2xl border border-emerald-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-emerald-600 text-white shadow-2xs">
                            1. Fungsi Utama Menu
                        </span>
                        <span class="text-xs font-bold text-slate-900">Pencatatan Realisasi Capaian SAKIP & Evaluasi Kinerja</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed pl-1">
                        Menu ini mencatat capaian kinerja tahunan riil (Tahun N) yang diperbandingkan dengan capaian tahun sebelumnya (Tahun N-1). Menu ini merupakan instrumen inti untuk menyusun pertanggungjawaban Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP) DINDIKPORA Banjarnegara yang diserahkan ke Inspektorat, Bappeda, dan KemenPAN-RB.
                    </p>
                </div>

                <!-- Baris 2: Data Makro & Evaluasi yang Dikelola -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2.5">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-slate-700 text-white shadow-2xs">
                            2. Data yang Dikelola
                        </span>
                        <span class="text-xs font-bold text-slate-900">Indikator Makro SAKIP & Analisis Naratif</span>
                    </div>
                    <ul class="text-[11px] text-slate-600 leading-relaxed pl-1 space-y-1.5 list-disc list-inside">
                        <li><strong>Nilai AKIP (Tahun N & N-1):</strong> Nilai evaluasi akuntabilitas kinerja (skala 0–100) dan predikat resmi (A/BB/B/CC/C).</li>
                        <li><strong>Indikator IPM BPS:</strong> Rata-rata Lama Sekolah (RLS) dan Harapan Lama Sekolah (HLS) hasil survei resmi BPS.</li>
                        <li><strong>IKK Renstra (%):</strong> Rata-rata persentase capaian seluruh Indikator Kinerja Kunci Renstra dinas.</li>
                        <li><strong>Analisis Naratif Evaluasi:</strong> Faktor pendukung keberhasilan, kendala/penghambat, serta rekomendasi strategis pimpinan untuk perbaikan ke depan.</li>
                    </ul>
                </div>

                <!-- Baris 3: Alur Integrasi ke Menu Lain -->
                <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-200/80 space-y-2.5">
                    <div class="flex items-center justify-between gap-2 border-b border-blue-100 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase bg-blue-600 text-white shadow-2xs">
                                3. Alur Integrasi
                            </span>
                            <span class="text-xs font-bold text-slate-900">Bagaimana Data Realisasi Mengalir ke Menu Lain</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-[11px] text-slate-600 leading-relaxed pl-1">
                        <div class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold shrink-0">&bull;</span>
                            <div>
                                <strong class="text-slate-800">Dashboard Utama:</strong> Mengisi metrik nilai AKIP terkini, perbandingan kenaikan RLS/HLS terhadap tahun lalu, dan ringkasan status SAKIP pimpinan.
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold shrink-0">&bull;</span>
                            <div>
                                <strong class="text-slate-800">Dokumen LKjIP (Bab IV):</strong> Mengotomatisasi tabel realisasi capaian, analisis efisiensi anggaran, narasi faktor penghambat, dan rekomendasi tindak lanjut secara langsung ke format siap cetak A4.
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold shrink-0">&bull;</span>
                            <div>
                                <strong class="text-slate-800">Form Evaluasi Kinerja:</strong> Menyediakan data komparasi historis (Tahun N-1) untuk mengukur tren perkembangan capaian tahun ke tahun.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Baris 4: Penegasan Terkait APK, APM, & APS -->
                <div class="p-3.5 bg-amber-50/70 rounded-2xl border border-amber-200 text-[11px] text-slate-700 space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs font-bold shrink-0">ℹ️</span>
                        <strong class="text-slate-900 font-bold">Catatan Penting: Pengaruh terhadap Rumus APK, APM, & APS:</strong>
                    </div>
                    <p class="text-slate-600 leading-relaxed pl-7">
                        Menu Realisasi Capaian Kinerja <strong>TIDAK MENGUBAH ataupun MEMANIPULASI</strong> hasil perhitungan pada menu 
                        <a href="{{ route('indikator.apk') }}" class="font-bold text-amber-800 hover:underline">APK</a>, 
                        <a href="{{ route('indikator.apm') }}" class="font-bold text-amber-800 hover:underline">APM</a>, maupun 
                        <a href="{{ route('indikator.aps') }}" class="font-bold text-amber-800 hover:underline">APS</a>.<br>
                        Perhitungan ketiga indikator tersebut tetap dihitung otomatis dan independen dari data mikro Dapodik (siswa) dan Dukcapil (penduduk). Menu ini khusus untuk indikator makro tingkat dinas serta evaluasi kualitatif dokumen SAKIP.
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                <button type="button" 
                        @click="infoModal = false" 
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                    Tutup Informasi
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
