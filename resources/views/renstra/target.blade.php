@extends('layouts.app', ['title' => 'Target Renstra & AKIP'])

@section('content')
<div class="space-y-8" x-data="{ 
    createModal: false, 
    editModal: false, 
    deleteModal: false, 
    activeTarget: {}, 
    deleteTarget: { id: null, tahun: '' },
    targets: @js($targetList->keyBy('id')),
    openEdit(id) {
        this.activeTarget = this.targets[id] || {};
        this.editModal = true;
    },
    openDelete(id, tahun) {
        this.deleteTarget = { id: id, tahun: tahun };
        this.deleteModal = true;
    }
}">

    <!-- Header Banner (Light Style) -->
    <div class="p-6 sm:p-7 rounded-3xl bg-gradient-to-r from-blue-50/80 via-slate-50 to-white text-slate-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-xs border border-blue-200/60">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-700 tracking-wider border border-blue-200">Perencanaan Renstra</span>
                <h2 class="text-lg font-bold text-slate-900">Target Renstra, AKIP, RLS & HLS</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Penetapan dan pengelolaan indikator target capaian kinerja lima tahunan DINDIKPORA Kabupaten Banjarnegara sesuai Dokumen Rencana Strategis dan RPJMD Daerah.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <button @click="createModal = true" 
                    type="button"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/25 transition-all duration-150 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Input Target Baru</span>
            </button>
        </div>
    </div>

    <!-- Target Matrix Table Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Matriks Penetapan Target Kinerja</h3>
                <p class="text-xs text-slate-500">Daftar proyeksi target kinerja tahunan dan indikator Renstra DINDIKPORA Banjarnegara</p>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200/80">
                    Total: <strong class="text-slate-900">{{ $targetList->count() }} Tahun</strong>
                </span>
                <button @click="createModal = true" 
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs border border-blue-200 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Data Target</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">Tahun</th>
                        <th class="py-3.5 px-4 text-center">APK SD</th>
                        <th class="py-3.5 px-4 text-center">APM SD</th>
                        <th class="py-3.5 px-4 text-center">APK SMP</th>
                        <th class="py-3.5 px-4 text-center">APM SMP</th>
                        <th class="py-3.5 px-4 text-center">Guru Sertif</th>
                        <th class="py-3.5 px-4 text-center">PAUD Akr. B</th>
                        <th class="py-3.5 px-4 text-center">Target AKIP</th>
                        <th class="py-3.5 px-4 text-center">Target RLS</th>
                        <th class="py-3.5 px-4 text-center">Target HLS</th>
                        <th class="py-3.5 px-4 text-center">IKK Renstra</th>
                        <th class="py-3.5 px-4 text-center" data-sortable="false">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($targetList as $t)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-4 px-4 font-extrabold text-blue-600 text-sm">{{ $t->tahun }}</td>
                        <td class="py-4 px-4 text-center font-bold text-slate-900">{{ number_format($t->target_apk_sd, 2) }}%</td>
                        <td class="py-4 px-4 text-center text-slate-700">{{ number_format($t->target_apm_sd, 2) }}%</td>
                        <td class="py-4 px-4 text-center font-bold text-slate-900">{{ number_format($t->target_apk_smp, 2) }}%</td>
                        <td class="py-4 px-4 text-center text-slate-700">{{ number_format($t->target_apm_smp, 2) }}%</td>
                        <td class="py-4 px-4 text-center text-purple-700 font-bold">{{ number_format($t->target_guru_sertifikasi, 2) }}%</td>
                        <td class="py-4 px-4 text-center text-amber-700 font-bold">{{ number_format($t->target_paud_akreditasi_b, 2) }}%</td>
                        <td class="py-4 px-4 text-center font-extrabold text-blue-700">{{ number_format($t->target_akip, 2) }}</td>
                        <td class="py-4 px-4 text-center font-mono font-medium">{{ number_format($t->target_rls, 2) }} Th</td>
                        <td class="py-4 px-4 text-center font-mono font-medium">{{ number_format($t->target_hls, 2) }} Th</td>
                        <td class="py-4 px-4 text-center font-bold text-emerald-600">{{ number_format($t->target_ikk_renstra, 2) }}%</td>
                        <td class="py-4 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="openEdit({{ $t->id }})" 
                                        type="button"
                                        title="Edit Target Tahun {{ $t->tahun }}"
                                        class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="openDelete({{ $t->id }}, '{{ $t->tahun }}')" 
                                        type="button"
                                        title="Hapus Target Tahun {{ $t->tahun }}"
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
                        <td colspan="12" class="py-12 text-center text-slate-400">
                            <div class="max-w-xs mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Belum ada data target renstra di sistem</p>
                                <button @click="createModal = true" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs shadow-xs">
                                    Input Target Baru
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: INPUT TARGET BARU -->
    <div x-show="createModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
         @keydown.escape.window="createModal = false">
        <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-xl w-full shadow-2xl border border-slate-100 space-y-4 my-8 max-h-[88vh] overflow-y-auto"
             @click.away="createModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-blue-50 text-blue-600 border border-blue-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Input Data Target Renstra Baru</h3>
                        <p class="text-xs text-slate-500">Tetapkan target capaian kinerja lima tahunan DINDIKPORA</p>
                    </div>
                </div>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('renstra.target.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf

                <!-- Tahun Perencanaan Target -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block font-bold text-slate-800 text-xs">
                            Tahun Perencanaan Target <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Rentang: 2020 – 2035</span>
                    </div>
                    <input type="number" 
                           name="tahun" 
                           value="{{ old('tahun', $suggestedNextTahun ?? 2026) }}" 
                           min="2020" 
                           max="2035" 
                           required 
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-blue-700 bg-white focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Bagian 1: Target APK -->
                <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-blue-100/80 pb-1.5">
                        <h4 class="font-bold text-blue-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            Target Angka Partisipasi Kasar (APK)
                        </h4>
                        <span class="text-[10px] font-semibold text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded-md">Satuan: %</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APK PAUD (%)">APK PAUD (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_apk_paud" value="{{ old('target_apk_paud', '85.00') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APK SD (%)">APK SD (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_apk_sd" value="{{ old('target_apk_sd', '104.50') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APK SMP (%)">APK SMP (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_apk_smp" value="{{ old('target_apk_smp', '98.50') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APK SMA/SMK (%)">APK SMA (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_apk_sma" value="{{ old('target_apk_sma', '88.00') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Target APM -->
                <div class="p-3.5 rounded-2xl bg-indigo-50/40 border border-indigo-100 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-indigo-100/80 pb-1.5">
                        <h4 class="font-bold text-indigo-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            Target Angka Partisipasi Murni (APM)
                        </h4>
                        <span class="text-[10px] font-semibold text-indigo-700 bg-indigo-100/70 px-2 py-0.5 rounded-md">Satuan: %</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APM PAUD (%)">APM PAUD (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_apm_paud" value="{{ old('target_apm_paud', '62.00') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APM SD (%)">APM SD (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_apm_sd" value="{{ old('target_apm_sd', '99.20') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APM SMP (%)">APM SMP (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_apm_smp" value="{{ old('target_apm_smp', '96.50') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APM SMA/SMK (%)">APM SMA (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_apm_sma" value="{{ old('target_apm_sma', '78.50') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bagian 3: Target APS -->
                <div class="p-3.5 rounded-2xl bg-emerald-50/40 border border-emerald-100 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-emerald-100/80 pb-1.5">
                        <h4 class="font-bold text-emerald-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            Target Angka Partisipasi Sekolah (APS)
                        </h4>
                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md">Satuan: %</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APS PAUD (5-6 Th)">PAUD 5-6 Th</label>
                            <input type="number" step="0.01" name="target_aps_paud" value="{{ old('target_aps_paud', '65.00') }}" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APS SD (7-12 Th)">SD 7-12 Th</label>
                            <input type="number" step="0.01" name="target_aps_sd" value="{{ old('target_aps_sd', '99.50') }}" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APS SMP (13-15 Th)">SMP 13-15 Th</label>
                            <input type="number" step="0.01" name="target_aps_smp" value="{{ old('target_aps_smp', '97.20') }}" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APS SMA (16-18 Th)">SMA 16-18 Th</label>
                            <input type="number" step="0.01" name="target_aps_sma" value="{{ old('target_aps_sma', '82.50') }}" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bagian 4: Target Mutu Guru & PAUD -->
                <div class="p-3.5 rounded-2xl bg-amber-50/40 border border-amber-100 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-amber-100/80 pb-1.5">
                        <h4 class="font-bold text-amber-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                            Target Mutu Pendidik & Akreditasi
                        </h4>
                        <span class="text-[10px] font-semibold text-amber-700 bg-amber-100/70 px-2 py-0.5 rounded-md">Satuan: %</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1">Guru Sertifikasi (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_guru_sertifikasi" value="{{ old('target_guru_sertifikasi', '75.00') }}" required class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1">PAUD Akreditasi Min. B (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_paud_akreditasi_b" value="{{ old('target_paud_akreditasi_b', '65.00') }}" required class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bagian 5: Target AKIP & Indikator IPM (RLS, HLS, IKK) -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-1.5">
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                            Target SAKIP & Indikator Makro IPM
                        </h4>
                        <span class="text-[10px] font-semibold text-slate-600 bg-slate-200/80 px-2 py-0.5 rounded-md">Poin / Th / %</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="Nilai AKIP (Poin)">AKIP (Poin) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_akip" value="{{ old('target_akip', '76.50') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="Target RLS (Tahun)">RLS (Th) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_rls" value="{{ old('target_rls', '7.65') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="Target HLS (Tahun)">HLS (Th) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_hls" value="{{ old('target_hls', '13.60') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="IKK Renstra (%)">IKK (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="target_ikk_renstra" value="{{ old('target_ikk_renstra', '86.50') }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition cursor-pointer text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition cursor-pointer text-xs">
                        Simpan Data Target
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: UBAH TARGET RENSTRA -->
    <div x-show="editModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
         @keydown.escape.window="editModal = false">
        <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-xl w-full shadow-2xl border border-slate-100 space-y-4 my-8 max-h-[88vh] overflow-y-auto"
             @click.away="editModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-blue-50 text-blue-600 border border-blue-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Ubah Target Renstra Pendidikan</h3>
                        <p class="text-xs text-slate-500">Tahun Perencanaan: <span class="font-bold text-blue-600" x-text="activeTarget.tahun"></span></p>
                    </div>
                </div>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('/renstra/target') }}/' + activeTarget.id" method="POST" class="space-y-3.5 text-xs">
                @csrf
                @method('PUT')

                <!-- Bagian 1: APK -->
                <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-blue-100/80 pb-1.5">
                        <h4 class="font-bold text-blue-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            Target Angka Partisipasi Kasar (APK)
                        </h4>
                        <span class="text-[10px] font-semibold text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded-md">Satuan: %</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APK PAUD (%)">APK PAUD (%)</label>
                            <input type="number" step="0.01" name="target_apk_paud" :value="activeTarget.target_apk_paud" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APK SD (%)">APK SD (%)</label>
                            <input type="number" step="0.01" name="target_apk_sd" :value="activeTarget.target_apk_sd" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APK SMP (%)">APK SMP (%)</label>
                            <input type="number" step="0.01" name="target_apk_smp" :value="activeTarget.target_apk_smp" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APK SMA/SMK (%)">APK SMA (%)</label>
                            <input type="number" step="0.01" name="target_apk_sma" :value="activeTarget.target_apk_sma" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: APM -->
                <div class="p-3.5 rounded-2xl bg-indigo-50/40 border border-indigo-100 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-indigo-100/80 pb-1.5">
                        <h4 class="font-bold text-indigo-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            Target Angka Partisipasi Murni (APM)
                        </h4>
                        <span class="text-[10px] font-semibold text-indigo-700 bg-indigo-100/70 px-2 py-0.5 rounded-md">Satuan: %</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APM PAUD (%)">APM PAUD (%)</label>
                            <input type="number" step="0.01" name="target_apm_paud" :value="activeTarget.target_apm_paud" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APM SD (%)">APM SD (%)</label>
                            <input type="number" step="0.01" name="target_apm_sd" :value="activeTarget.target_apm_sd" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APM SMP (%)">APM SMP (%)</label>
                            <input type="number" step="0.01" name="target_apm_smp" :value="activeTarget.target_apm_smp" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APM SMA/SMK (%)">APM SMA (%)</label>
                            <input type="number" step="0.01" name="target_apm_sma" :value="activeTarget.target_apm_sma" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bagian 3: APS -->
                <div class="p-3.5 rounded-2xl bg-emerald-50/40 border border-emerald-100 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-emerald-100/80 pb-1.5">
                        <h4 class="font-bold text-emerald-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            Target Angka Partisipasi Sekolah (APS)
                        </h4>
                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md">Satuan: %</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APS PAUD (5-6 Th)">PAUD 5-6 Th</label>
                            <input type="number" step="0.01" name="target_aps_paud" :value="activeTarget.target_aps_paud" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APS SD (7-12 Th)">SD 7-12 Th</label>
                            <input type="number" step="0.01" name="target_aps_sd" :value="activeTarget.target_aps_sd" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APS SMP (13-15 Th)">SMP 13-15 Th</label>
                            <input type="number" step="0.01" name="target_aps_smp" :value="activeTarget.target_aps_smp" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="APS SMA (16-18 Th)">SMA 16-18 Th</label>
                            <input type="number" step="0.01" name="target_aps_sma" :value="activeTarget.target_aps_sma" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bagian 4: Mutu Pendidik & Kelembagaan -->
                <div class="p-3.5 rounded-2xl bg-amber-50/40 border border-amber-100 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-amber-100/80 pb-1.5">
                        <h4 class="font-bold text-amber-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                            Target Mutu Pendidik & Akreditasi
                        </h4>
                        <span class="text-[10px] font-semibold text-amber-700 bg-amber-100/70 px-2 py-0.5 rounded-md">Satuan: %</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1">Target Guru Sertif (%)</label>
                            <input type="number" step="0.01" name="target_guru_sertifikasi" :value="activeTarget.target_guru_sertifikasi" required class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1">Target PAUD Min B (%)</label>
                            <input type="number" step="0.01" name="target_paud_akreditasi_b" :value="activeTarget.target_paud_akreditasi_b" required class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bagian 5: AKIP, RLS, HLS & IKK -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-1.5">
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                            Target SAKIP & Indikator Makro IPM
                        </h4>
                        <span class="text-[10px] font-semibold text-slate-600 bg-slate-200/80 px-2 py-0.5 rounded-md">Poin / Th / %</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="Target AKIP (Poin)">AKIP (Poin)</label>
                            <input type="number" step="0.01" name="target_akip" :value="activeTarget.target_akip" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="Target RLS (Th)">RLS (Th)</label>
                            <input type="number" step="0.01" name="target_rls" :value="activeTarget.target_rls" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="Target HLS (Th)">HLS (Th)</label>
                            <input type="number" step="0.01" name="target_hls" :value="activeTarget.target_hls" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 text-[11px] mb-1 truncate" title="IKK Renstra (%)">IKK (%)</label>
                            <input type="number" step="0.01" name="target_ikk_renstra" :value="activeTarget.target_ikk_renstra" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-center font-medium">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition cursor-pointer text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition cursor-pointer text-xs">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: KONFIRMASI HAPUS TARGET -->
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
                <h3 class="text-sm font-bold text-slate-900">Konfirmasi Hapus Data Target</h3>
                <p class="text-xs text-slate-500">
                    Apakah Anda yakin ingin menghapus data Target Renstra untuk <strong class="text-slate-900">Tahun <span x-text="deleteTarget.tahun"></span></strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <form method="POST" :action="'{{ url('/renstra/target') }}/' + deleteTarget.id" class="flex items-center justify-center gap-2.5 pt-2">
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

</div>
@endsection
