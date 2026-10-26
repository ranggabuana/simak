@extends('layouts.app', ['title' => 'Sertifikasi Pendidik'])

@section('content')
<div class="space-y-8">

    <!-- KPI Header Card -->
    <div class="p-6 rounded-3xl bg-purple-50/70 border border-purple-200 text-purple-950 shadow-sm space-y-4">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-purple-600 text-white tracking-wider">Indikator Mutu Pendidik</span>
                <h2 class="text-base font-bold text-slate-900">Persentase Guru yang Memiliki Sertifikat Pendidik</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Mengukur rasio guru dan tenaga kependidikan bersertifikasi profesional di satuan pendidikan se-Kabupaten Banjarnegara untuk memenuhi standar mutu Renstra DINDIKPORA.
            </p>
        </div>

        <!-- Dedicated Action Toolbar (Divider + Filter Gender Left + Unduh Excel Right) -->
        <div class="pt-3.5 border-t border-purple-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Gender:
                </span>
                <div class="inline-flex items-center p-1 bg-white rounded-2xl border border-purple-200 text-xs font-semibold shadow-2xs">
                    <a href="{{ route('indikator.guru', array_merge(request()->query(), ['gender' => ''])) }}" 
                       class="px-3.5 py-1.5 rounded-xl transition {{ empty($filterGender) ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua
                    </a>
                    <a href="{{ route('indikator.guru', array_merge(request()->query(), ['gender' => 'L'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl transition {{ ($filterGender ?? '') === 'L' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        ♂ Laki-laki
                    </a>
                    <a href="{{ route('indikator.guru', array_merge(request()->query(), ['gender' => 'P'])) }}" 
                       class="px-3.5 py-1.5 rounded-xl transition {{ ($filterGender ?? '') === 'P' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        ♀ Perempuan
                    </a>
                </div>
            </div>

            <a href="{{ route('export.kecamatan', ['tahun' => $tahun]) }}" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-purple-700 hover:text-purple-800 border border-purple-200 font-bold text-xs shadow-2xs hover:shadow-xs transition shrink-0">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Rekap Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- 3 Stat Highlight Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Guru Aktif {{ $filterGender === 'L' ? '(Laki-laki)' : ($filterGender === 'P' ? '(Perempuan)' : '') }}</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['guru']['total']) }}</div>
            <p class="text-xs text-slate-500 mt-1">PNS, PPPK, dan Guru Tetap Yayasan/Honor</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Guru Sudah Sertifikasi</span>
            <div class="mt-2 text-3xl font-extrabold text-purple-600">{{ number_format($kabupaten['guru']['sertifikasi']) }}</div>
            <p class="text-xs text-slate-500 mt-1">Memiliki Sertifikat Pendidik Resmi</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Persentase Capaian</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['guru']['persentase'], 2) }}%</span>
                <span class="text-xs font-semibold text-emerald-600">Target: {{ $kabupaten['target']?->target_guru_sertifikasi ?? 72 }}%</span>
            </div>
            <p class="text-xs text-emerald-600 font-bold mt-1">&check; Target Renstra Terpenuhi</p>
        </div>
    </div>

    <!-- Rekapitulasi Komparasi Gender Guru -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Rekapitulasi Sertifikasi Berdasarkan Jenis Kelamin</h3>
                <p class="text-[11px] text-slate-500">Komparasi capaian sertifikasi antara guru laki-laki dan perempuan se-Kabupaten Banjarnegara</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                Data GTK Riil
            </span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-blue-900 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Guru Laki-laki
                    </span>
                    <strong class="text-base text-blue-950 font-black">{{ number_format($kabupaten['guru']['total_l'] ?? 0) }} Guru</strong>
                </div>
                <div class="flex justify-between items-center text-slate-600 pt-1 border-t border-blue-100">
                    <span>Sudah Bersertifikat:</span>
                    <span class="font-bold text-blue-800">{{ number_format($kabupaten['guru']['sertifikasi_l'] ?? 0) }} Guru</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Persentase Sertifikasi:</span>
                    <span class="font-extrabold text-blue-700 text-sm">{{ $kabupaten['guru']['persentase_l'] ?? 0 }}%</span>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-pink-50/70 border border-pink-200 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-pink-900 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-pink-600"></span> Guru Perempuan
                    </span>
                    <strong class="text-base text-pink-950 font-black">{{ number_format($kabupaten['guru']['total_p'] ?? 0) }} Guru</strong>
                </div>
                <div class="flex justify-between items-center text-slate-600 pt-1 border-t border-pink-100">
                    <span>Sudah Bersertifikat:</span>
                    <span class="font-bold text-pink-800">{{ number_format($kabupaten['guru']['sertifikasi_p'] ?? 0) }} Guru</span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Persentase Sertifikasi:</span>
                    <span class="font-extrabold text-pink-700 text-sm">{{ $kabupaten['guru']['persentase_p'] ?? 0 }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Table per Kecamatan -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Distribusi Sertifikasi Pendidik per Kecamatan</h3>
                <p class="text-xs text-slate-500">Rekapitulasi guru bersertifikat pendidik pada 20 wilayah kecamatan</p>
            </div>
            <a href="{{ route('guru.index') }}" class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs transition flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Kelola Data Guru & Sertifikasi</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4 text-center">Total Guru</th>
                        <th class="py-3.5 px-4 text-center">Sudah Sertifikasi</th>
                        <th class="py-3.5 px-4 text-center">Belum Sertifikasi</th>
                        <th class="py-3.5 px-4 text-center">% Sertifikasi</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kecamatanList as $kec)
                    @php $belum = $kec['guru']['total'] - $kec['guru']['sertifikasi']; @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-mono text-slate-400">{{ $kec['kode'] }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $kec['nama'] }}</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['guru']['total']) }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-purple-700">{{ number_format($kec['guru']['sertifikasi']) }}</td>
                        <td class="py-3.5 px-4 text-center text-slate-500">{{ number_format($belum) }}</td>
                        <td class="py-3.5 px-4 text-center font-extrabold text-slate-900">{{ number_format($kec['guru']['persentase'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $kec['guru']['persentase'] >= 72 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $kec['guru']['persentase'] >= 72 ? 'Sesuai Target' : 'Perlu Peningkatan' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
