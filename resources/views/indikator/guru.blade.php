@extends('layouts.app', ['title' => 'Sertifikasi Pendidik'])

@section('content')
<div class="space-y-8">

    <!-- KPI Header Card -->
    <div class="p-6 rounded-3xl bg-purple-50/70 border border-purple-200 text-purple-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-sm">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-purple-600 text-white tracking-wider">Indikator Mutu Pendidik</span>
                <h2 class="text-base font-bold text-slate-900">Persentase Guru yang Memiliki Sertifikat Pendidik</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Mengukur rasio guru dan tenaga kependidikan bersertifikasi profesional di satuan pendidikan se-Kabupaten Banjarnegara untuk memenuhi standar mutu Renstra DINDIKPORA.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('export.kecamatan', ['tahun' => $tahun]) }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-purple-700 border border-purple-200 font-bold text-xs shadow-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Rekap Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- 3 Stat Highlight Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Guru Aktif</span>
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
