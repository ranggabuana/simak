@extends('layouts.app', ['title' => 'Tujuan 1: Penduduk Usia Sekolah'])

@section('content')
<div class="space-y-8" x-data="{ editModal: false, createModal: {{ $errors->any() ? 'true' : 'false' }}, importModal: false, activeRow: {} }">

    <!-- Header Banner (Light Style) -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-blue-50/80 via-slate-50 to-white text-slate-900 flex flex-col xl:flex-row xl:items-center justify-between gap-5 shadow-xs border border-blue-200/60">
        <div class="space-y-1.5 flex-1 min-w-0 pr-2">
            <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Data Penduduk Usia Sekolah per Kecamatan (Tahun {{ $tahun }})</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kelompok umur usia sekolah: 5–6 tahun (PAUD), 7–12 tahun (SD/MI), 13–15 tahun (SMP/MTs), 16–18 tahun (SMA/SMK), dan 18 tahun ke atas per kecamatan se-Kabupaten Banjarnegara.
            </p>
        </div>
        
        <!-- Tombol Aksi 1 Row (Import hingga Unduh CSV) -->
        <div class="flex items-center gap-2.5 shrink-0 flex-nowrap overflow-x-auto pb-1 xl:pb-0">
            <button type="button" 
                    @click="importModal = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm shadow-emerald-500/20 transition whitespace-nowrap shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Import Excel / CSV</span>
            </button>

            <button type="button" 
                    @click="createModal = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/25 transition whitespace-nowrap shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Input Manual Baru</span>
            </button>

            <a href="{{ route('export.penduduk', ['tahun' => $tahun, 'gender' => $gender]) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold text-xs shadow-2xs transition whitespace-nowrap shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- Filter Gender Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 flex-wrap">
            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter Menurut Gender:
            </span>
            <div class="inline-flex items-center p-1 bg-slate-100 rounded-2xl border border-slate-200 text-xs font-semibold shadow-2xs">
                <a href="{{ route('tujuan1.index', ['tahun' => $tahun, 'gender' => 'all']) }}" 
                   class="px-3.5 py-1.5 rounded-xl transition {{ ($gender ?? 'all') === 'all' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('tujuan1.index', ['tahun' => $tahun, 'gender' => 'L']) }}" 
                   class="px-3.5 py-1.5 rounded-xl transition {{ ($gender ?? '') === 'L' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    ♂ Laki-laki (L)
                </a>
                <a href="{{ route('tujuan1.index', ['tahun' => $tahun, 'gender' => 'P']) }}" 
                   class="px-3.5 py-1.5 rounded-xl transition {{ ($gender ?? '') === 'P' ? 'bg-pink-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    ♀ Perempuan (P)
                </a>
            </div>
        </div>

        <div class="text-xs text-slate-500 font-medium flex items-center gap-2">
            <span>Tahun Data: <strong class="text-slate-800">{{ $tahun }}</strong></span>
            <span>&bull;</span>
            <span>Total Penduduk: <strong class="text-slate-800">{{ number_format($totals['total']) }} Jiwa</strong></span>
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
                    <span class="text-xs font-bold text-blue-950 block">Penduduk Laki-laki</span>
                    <span class="text-[11px] text-blue-700">Usia Sekolah (5-18 Th): <strong>{{ number_format($genderRecap['usia_sekolah_l']) }}</strong> Jiwa</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xl font-black text-blue-900">{{ number_format($genderRecap['total_l']) }}</span>
                <span class="text-[10px] text-blue-600 block">Jiwa ({{ $genderRecap['persen_l'] }}%)</span>
            </div>
        </div>
        <div class="p-4 rounded-2xl bg-pink-50/60 border border-pink-200/80 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-pink-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    P
                </div>
                <div>
                    <span class="text-xs font-bold text-pink-950 block">Penduduk Perempuan</span>
                    <span class="text-[11px] text-pink-700">Usia Sekolah (5-18 Th): <strong>{{ number_format($genderRecap['usia_sekolah_p']) }}</strong> Jiwa</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xl font-black text-pink-900">{{ number_format($genderRecap['total_p']) }}</span>
                <span class="text-[10px] text-pink-600 block">Jiwa ({{ $genderRecap['persen_p'] }}%)</span>
            </div>
        </div>
    </div>

    <!-- Summary Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">5 – 6 Tahun (PAUD) {{ $gender === 'L' ? '(L)' : ($gender === 'P' ? '(P)' : '') }}</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($totals['usia_5_6']) }}</div>
            <p class="text-[10px] text-slate-500">Jiwa di 20 Kecamatan</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">7 – 12 Tahun (SD/MI) {{ $gender === 'L' ? '(L)' : ($gender === 'P' ? '(P)' : '') }}</span>
            <div class="text-xl font-extrabold text-blue-600 mt-1">{{ number_format($totals['usia_7_12']) }}</div>
            <p class="text-[10px] text-slate-500">Jiwa di 20 Kecamatan</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">13 – 15 Tahun (SMP) {{ $gender === 'L' ? '(L)' : ($gender === 'P' ? '(P)' : '') }}</span>
            <div class="text-xl font-extrabold text-indigo-600 mt-1">{{ number_format($totals['usia_13_15']) }}</div>
            <p class="text-[10px] text-slate-500">Jiwa di 20 Kecamatan</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">16 – 18 Tahun (SMA) {{ $gender === 'L' ? '(L)' : ($gender === 'P' ? '(P)' : '') }}</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($totals['usia_16_18']) }}</div>
            <p class="text-[10px] text-slate-500">Jiwa di 20 Kecamatan</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold uppercase text-slate-400">> 18 Tahun {{ $gender === 'L' ? '(L)' : ($gender === 'P' ? '(P)' : '') }}</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($totals['usia_18_plus']) }}</div>
            <p class="text-[10px] text-slate-500">Pendidikan Lanjutan</p>
        </div>
    </div>

    <!-- Table of 20 Kecamatan Demographic Data -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Rincian Data Usia Sekolah per Kecamatan</h3>
                <p class="text-xs text-slate-500">Data bersumber dari agregasi database kependudukan Kabupaten Banjarnegara</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4 text-right">0–4 Th</th>
                        <th class="py-3.5 px-4 text-right">5–6 Th (PAUD)</th>
                        <th class="py-3.5 px-4 text-right">7–12 Th (SD)</th>
                        <th class="py-3.5 px-4 text-right">13–15 Th (SMP)</th>
                        <th class="py-3.5 px-4 text-right">16–18 Th (SMA)</th>
                        <th class="py-3.5 px-4 text-right">>18 Th</th>
                        <th class="py-3.5 px-4 text-right">Total Penduduk</th>
                        <th class="py-3.5 px-4 text-center" data-sortable="false">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($pendudukList as $p)
                    @php
                        $u0_4 = $gender === 'L' ? $p->usia_0_4_l : ($gender === 'P' ? $p->usia_0_4_p : $p->usia_0_4_total);
                        $u5_6 = $gender === 'L' ? $p->usia_5_6_l : ($gender === 'P' ? $p->usia_5_6_p : $p->usia_5_6_total);
                        $u7_12 = $gender === 'L' ? $p->usia_7_12_l : ($gender === 'P' ? $p->usia_7_12_p : $p->usia_7_12_total);
                        $u13_15 = $gender === 'L' ? $p->usia_13_15_l : ($gender === 'P' ? $p->usia_13_15_p : $p->usia_13_15_total);
                        $u16_18 = $gender === 'L' ? $p->usia_16_18_l : ($gender === 'P' ? $p->usia_16_18_p : $p->usia_16_18_total);
                        $u18p = $gender === 'L' ? (int) round($p->usia_18_plus_total * 0.505) : ($gender === 'P' ? (int) round($p->usia_18_plus_total * 0.495) : $p->usia_18_plus_total);
                        $uTot = $gender === 'L' ? ($p->usia_0_4_l + $p->usia_5_6_l + $p->usia_7_12_l + $p->usia_13_15_l + $p->usia_16_18_l + $u18p)
                              : ($gender === 'P' ? ($p->usia_0_4_p + $p->usia_5_6_p + $p->usia_7_12_p + $p->usia_13_15_p + $p->usia_16_18_p + $u18p)
                              : $p->total_penduduk);
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-mono text-slate-400">{{ $p->kecamatan->kode }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $p->kecamatan->nama }}</td>
                        <td class="py-3.5 px-4 text-right text-slate-600">{{ number_format($u0_4) }}</td>
                        <td class="py-3.5 px-4 text-right font-medium text-slate-800">
                            <div>{{ number_format($u5_6) }}</div>
                            @if($gender === 'all')
                                <div class="text-[9px] text-slate-400 font-mono">L:{{ number_format($p->usia_5_6_l) }} P:{{ number_format($p->usia_5_6_p) }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-blue-600">
                            <div>{{ number_format($u7_12) }}</div>
                            @if($gender === 'all')
                                <div class="text-[9px] text-blue-400 font-mono">L:{{ number_format($p->usia_7_12_l) }} P:{{ number_format($p->usia_7_12_p) }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-indigo-600">
                            <div>{{ number_format($u13_15) }}</div>
                            @if($gender === 'all')
                                <div class="text-[9px] text-indigo-400 font-mono">L:{{ number_format($p->usia_13_15_l) }} P:{{ number_format($p->usia_13_15_p) }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-medium text-slate-800">
                            <div>{{ number_format($u16_18) }}</div>
                            @if($gender === 'all')
                                <div class="text-[9px] text-slate-400 font-mono">L:{{ number_format($p->usia_16_18_l) }} P:{{ number_format($p->usia_16_18_p) }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-600">{{ number_format($u18p) }}</td>
                        <td class="py-3.5 px-4 text-right font-semibold text-slate-900">{{ number_format($uTot) }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" 
                                        @click="editModal = true; activeRow = {
                                            id: '{{ $p->id }}',
                                            nama: '{{ $p->kecamatan->nama }}',
                                            u5_6: '{{ $p->usia_5_6_total }}',
                                            u7_12: '{{ $p->usia_7_12_total }}',
                                            u13_15: '{{ $p->usia_13_15_total }}',
                                            u16_18: '{{ $p->usia_16_18_total }}'
                                        }" 
                                        class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer" 
                                        title="Edit Data Kependudukan">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-extrabold text-slate-900 border-t-2 border-slate-300">
                        <td colspan="2" class="py-3.5 px-4 uppercase">Total Kabupaten Banjarnegara</td>
                        <td class="py-3.5 px-4 text-right">{{ number_format($totals['usia_0_4']) }}</td>
                        <td class="py-3.5 px-4 text-right">{{ number_format($totals['usia_5_6']) }}</td>
                        <td class="py-3.5 px-4 text-right text-blue-700">{{ number_format($totals['usia_7_12']) }}</td>
                        <td class="py-3.5 px-4 text-right text-indigo-700">{{ number_format($totals['usia_13_15']) }}</td>
                        <td class="py-3.5 px-4 text-right">{{ number_format($totals['usia_16_18']) }}</td>
                        <td class="py-3.5 px-4 text-right">{{ number_format($totals['usia_18_plus']) }}</td>
                        <td class="py-3.5 px-4 text-right text-slate-900">{{ number_format($totals['total']) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Edit Demographic Modal -->
    <div x-show="editModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-100 space-y-6"
             @click.away="editModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Perbarui Data Usia Sekolah</h3>
                    <p class="text-xs text-slate-500">Kecamatan <span class="font-bold text-blue-600" x-text="activeRow.nama"></span></p>
                </div>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form :action="'{{ url('/tujuan1/penduduk') }}/' + activeRow.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Usia 5 – 6 Tahun (PAUD/TK)</label>
                    <input type="number" name="usia_5_6_total" :value="activeRow.u5_6" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Usia 7 – 12 Tahun (SD/MI)</label>
                    <input type="number" name="usia_7_12_total" :value="activeRow.u7_12" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Usia 13 – 15 Tahun (SMP/MTs)</label>
                    <input type="number" name="usia_13_15_total" :value="activeRow.u13_15" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Usia 16 – 18 Tahun (SMA/SMK)</label>
                    <input type="number" name="usia_16_18_total" :value="activeRow.u16_18" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition shadow-md shadow-blue-600/30 cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create / Manual Input Demographic Modal -->
    <div x-show="createModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-100 space-y-6"
             @click.away="createModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Input Data Penduduk Baru</h3>
                    <p class="text-xs text-slate-500">Tambah rincian kelompok usia sekolah untuk kecamatan yang belum terdaftar</p>
                </div>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('tujuan1.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-200/80 text-[11px] text-blue-800 flex items-start gap-2">
                    <span class="text-blue-600 mt-0.5">ℹ️</span>
                    <span>Setiap kecamatan hanya memiliki 1 baris data per tahun. Untuk mengubah data yang sudah terdaftar, gunakan tombol <strong>Edit</strong> pada tabel.</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kecamatan <span class="text-rose-500">*</span></label>
                        <select name="kecamatan_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-xs font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Kecamatan --</option>
                            @php $existingKecIds = $pendudukList->pluck('kecamatan_id')->toArray(); @endphp
                            @foreach($kecamatanList as $k)
                                @php $alreadyRecorded = in_array($k->id, $existingKecIds); @endphp
                                <option value="{{ $k->id }}" {{ old('kecamatan_id') == $k->id ? 'selected' : '' }} class="{{ $alreadyRecorded ? 'text-slate-400 bg-slate-50' : 'font-bold' }}">
                                    {{ $k->nama }}{{ $alreadyRecorded ? ' (Sudah ada di '.$tahun.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tahun Kinerja <span class="text-rose-500">*</span></label>
                        <input type="number" name="tahun" value="{{ old('tahun', $tahun) }}" required min="2020" max="2035" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-xs font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">0 – 4 Tahun</label>
                        <input type="number" name="usia_0_4_total" value="{{ old('usia_0_4_total', 0) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-800">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">5 – 6 Tahun (PAUD) <span class="text-rose-500">*</span></label>
                        <input type="number" name="usia_5_6_total" value="{{ old('usia_5_6_total', 0) }}" required min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-800">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">7 – 12 Tahun (SD) <span class="text-rose-500">*</span></label>
                        <input type="number" name="usia_7_12_total" value="{{ old('usia_7_12_total', 0) }}" required min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-800">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">13 – 15 Tahun (SMP) <span class="text-rose-500">*</span></label>
                        <input type="number" name="usia_13_15_total" value="{{ old('usia_13_15_total', 0) }}" required min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-800">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">16 – 18 Tahun (SMA) <span class="text-rose-500">*</span></label>
                        <input type="number" name="usia_16_18_total" value="{{ old('usia_16_18_total', 0) }}" required min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-800">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">> 18 Tahun</label>
                        <input type="number" name="usia_18_plus_total" value="{{ old('usia_18_plus_total', 0) }}" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-slate-800">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="createModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition shadow-md shadow-blue-600/30 cursor-pointer">Simpan Data Manual</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Import Modal (Excel .xlsx / .csv) -->
    <div x-show="importModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-100 space-y-6"
             @click.away="importModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Import File Excel / CSV Penduduk</h3>
                    <p class="text-xs text-slate-500">Unggah berkas penduduk.xlsx dari DUKCAPIL atau file CSV</p>
                </div>
                <button @click="importModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('import.penduduk') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tahun Kinerja Data <span class="text-rose-500">*</span></label>
                    <input type="number" name="tahun" value="{{ $tahun }}" required min="2020" max="2035" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-xs font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Berkas (.xlsx atau .csv) <span class="text-rose-500">*</span></label>
                    <input type="file" name="file_penduduk" required accept=".xlsx,.csv,.txt" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                </div>

                <div class="p-3.5 rounded-2xl bg-emerald-50/50 border border-emerald-100 text-[11px] text-slate-600 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-emerald-900">Belum memiliki contoh format kolom?</span>
                        <a href="{{ route('import.template', 'penduduk') }}" class="font-bold text-emerald-600 hover:text-emerald-800 hover:underline shrink-0 ml-2 inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Unduh Template CSV
                        </a>
                    </div>
                    <p class="text-[10px] text-slate-500 leading-relaxed">
                        Urutan kolom: <code class="font-mono text-emerald-700 bg-white px-1.5 py-0.5 rounded border border-emerald-200">kode_kecamatan, nama_kecamatan, tahun, usia_0_4, usia_5_6, usia_7_12, usia_13_15, usia_16_18, usia_18_plus</code>
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="importModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-md shadow-emerald-600/30 cursor-pointer">Proses Import Data</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
