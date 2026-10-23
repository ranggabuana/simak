@extends('layouts.app', ['title' => 'Evaluasi Kinerja & Gap Analysis'])

@section('content')
<div class="space-y-8">

    <!-- Header Banner (Light Style) -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-indigo-50/80 via-slate-50 to-white text-slate-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-xs border border-indigo-200/70">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-indigo-100 text-indigo-700 tracking-wider border border-indigo-200">Evaluasi Flowchart</span>
                <h2 class="text-lg font-bold text-slate-900">Evaluasi Kinerja & Analisis Kesenjangan (Tahun {{ $tahun }})</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Analisis gap deviasi antara target Renstra pendidikan dengan realisasi riil di lapangan sebagai dasar rekomendasi perumusan kebijakan dinas.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold 
                {{ $realisasi?->evaluasi_status === 'Sangat Baik' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                Status SAKIP: {{ $realisasi?->evaluasi_status ?? 'Baik' }}
            </span>
        </div>
    </div>

    <!-- Gap Matrix Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-900">Matriks Gap Analysis Target vs Realisasi</h3>
            <p class="text-xs text-slate-500">Kalkulasi deviasi capaian kinerja terhadap target Renstra Dinas Pendidikan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">Indikator Kinerja</th>
                        <th class="py-3.5 px-4 text-center">Satuan</th>
                        <th class="py-3.5 px-4 text-right">Target Renstra</th>
                        <th class="py-3.5 px-4 text-right">Realisasi</th>
                        <th class="py-3.5 px-4 text-right">Deviasi</th>
                        <th class="py-3.5 px-4 text-center">% Capaian</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($evaluasiMatrix as $row)
                    @php
                        $deviasi = $row['realisasi'] - $row['target'];
                        $persenCapaian = ($row['target'] > 0) ? round(($row['realisasi'] / $row['target']) * 100, 1) : 100;
                        $statusClass = $persenCapaian >= 100 ? 'bg-emerald-50 text-emerald-700' : ($persenCapaian >= 85 ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-700');
                        $statusLabel = $persenCapaian >= 100 ? 'Tercapai' : ($persenCapaian >= 85 ? 'Mendekati' : 'Perlu Intervensi');
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-4 px-4 font-bold text-slate-900">{{ $row['indikator'] }}</td>
                        <td class="py-4 px-4 text-center text-slate-400 font-mono">{{ $row['satuan'] }}</td>
                        <td class="py-4 px-4 text-right font-medium text-slate-600">{{ number_format($row['target'], 2) }}</td>
                        <td class="py-4 px-4 text-right font-extrabold text-slate-900">{{ number_format($row['realisasi'], 2) }}</td>
                        <td class="py-4 px-4 text-right font-bold {{ $deviasi >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $deviasi >= 0 ? '+' : '' }}{{ number_format($deviasi, 2) }}
                        </td>
                        <td class="py-4 px-4 text-center font-extrabold text-slate-800">{{ $persenCapaian }}%</td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-6 text-slate-400">Target belum ditetapkan untuk tahun ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pratinjau / Hasil Rumusan Tersimpan (Jika Sudah Pernah Diisi) -->
    @if($realisasi && ($realisasi->catatan_evaluasi || $realisasi->rekomendasi))
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-xl bg-emerald-100 text-emerald-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <h3 class="text-base font-bold text-slate-900">Hasil Rumusan Dokumen Evaluasi & Rekomendasi (Tahun {{ $tahun }})</h3>
                </div>
                <p class="text-xs text-slate-500">Pratinjau narasi resmi yang tersimpan untuk dokumen pelaporan kinerja SAKIP Dindikpora</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold {{ $realisasi->evaluasi_status === 'Sangat Baik' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                    Status: {{ $realisasi->evaluasi_status ?? 'Baik' }}
                </span>
                <a href="{{ route('renstra.realisasi') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 text-xs font-semibold transition">
                    <span>Lihat di Realisasi Renstra</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-2 p-5 rounded-2xl bg-slate-50/80 border border-slate-200/70">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Catatan Analisis Kinerja</span>
                </div>
                <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $realisasi->catatan_evaluasi }}
                </p>
            </div>

            <div class="space-y-2 p-5 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                <div class="flex items-center gap-2 text-xs font-bold text-indigo-900 uppercase tracking-wider">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Rekomendasi Tindak Lanjut Kebijakan</span>
                </div>
                <p class="text-xs text-indigo-950 leading-relaxed whitespace-pre-line">
                    {{ $realisasi->rekomendasi }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Rekomendasi Kebijakan Form -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-5">
        <div class="border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">{{ ($realisasi && ($realisasi->catatan_evaluasi || $realisasi->rekomendasi)) ? 'Pemutakhiran Form Evaluasi & Rekomendasi' : 'Perumusan Evaluasi & Rekomendasi Kebijakan' }}</h3>
            <p class="text-xs text-slate-500">Catatan resmi internal Tim Evaluasi Kinerja untuk dokumen pelaporan AKIP</p>
        </div>

        <form action="{{ route('evaluasi.rekomendasi') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">

            <div class="max-w-xs">
                <label class="block font-semibold text-slate-700 mb-1">Status Penilaian Evaluasi Akhir *</label>
                <select name="evaluasi_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Sangat Baik" {{ ($realisasi?->evaluasi_status === 'Sangat Baik') ? 'selected' : '' }}>Sangat Baik</option>
                    <option value="Baik" {{ ($realisasi?->evaluasi_status === 'Baik' || !$realisasi) ? 'selected' : '' }}>Baik</option>
                    <option value="Cukup" {{ ($realisasi?->evaluasi_status === 'Cukup') ? 'selected' : '' }}>Cukup</option>
                    <option value="Kurang" {{ ($realisasi?->evaluasi_status === 'Kurang') ? 'selected' : '' }}>Kurang</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Catatan Analisis Kinerja *</label>
                <textarea name="catatan_evaluasi" rows="3" required placeholder="Tuliskan faktor pendukung atau penghambat capaian kinerja..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">{{ old('catatan_evaluasi', $realisasi?->catatan_evaluasi) }}</textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Rekomendasi Tindak Lanjut Kebijakan *</label>
                <textarea name="rekomendasi" rows="3" required placeholder="Langkah strategis, alokasi anggaran, atau intervensi program dinas..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-blue-500">{{ old('rekomendasi', $realisasi?->rekomendasi) }}</textarea>
            </div>

            <div class="flex items-center justify-end pt-3">
                <button type="submit" class="px-6 py-3 rounded-xl font-bold text-xs text-white bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-600/30 transition">
                    Simpan Catatan & Rekomendasi
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
