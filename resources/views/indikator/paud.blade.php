@extends('layouts.app', ['title' => 'Akreditasi PAUD'])

@section('content')
<div class="space-y-8">

    <!-- KPI Header Card -->
    <div class="p-6 rounded-3xl bg-amber-50/70 border border-amber-200 text-amber-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-sm">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-600 text-white tracking-wider">Standar Pelayanan Minimal (SPM)</span>
                <h2 class="text-base font-bold text-slate-900">Persentase Satuan PAUD Terakreditasi Minimal B</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Menghitung proporsi satuan PAUD (TK, KB, TPA, SPS) yang telah terakreditasi A atau B oleh BAN-PDM di seluruh Kabupaten Banjarnegara.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('export.kecamatan', ['tahun' => $tahun]) }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-amber-800 border border-amber-200 font-bold text-xs shadow-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Rekap Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- 3 Stat Highlight Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Satuan PAUD/TK</span>
            <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ $kabupaten['paud_akreditasi']['total'] }}</div>
            <p class="text-xs text-slate-500 mt-1">Lembaga Pendidikan Anak Usia Dini Terdaftar</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Terakreditasi A & B</span>
            <div class="mt-2 text-3xl font-extrabold text-amber-600">{{ $kabupaten['paud_akreditasi']['min_b'] }}</div>
            <p class="text-xs text-slate-500 mt-1">Memenuhi Standar Mutu Minimal B</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Persentase Capaian</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['paud_akreditasi']['persentase'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_paud_akreditasi_b ?? 68 }}%</span>
            </div>
            <p class="text-xs text-amber-700 font-medium mt-1">Indikator Kunci Standar Pelayanan Minimal</p>
        </div>
    </div>

    <!-- Table per Kecamatan -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Sebaran Akreditasi PAUD per Kecamatan</h3>
                <p class="text-xs text-slate-500">Kinerja pembinaan mutu dan akreditasi PAUD oleh Korwilcam</p>
            </div>
            <a href="{{ route('sekolah.index', ['jenjang' => 'PAUD']) }}" class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs transition flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Kelola Satuan PAUD & Akreditasi</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4 text-center">Total PAUD</th>
                        <th class="py-3.5 px-4 text-center">Akreditasi A & B</th>
                        <th class="py-3.5 px-4 text-center">Belum / Akr. C</th>
                        <th class="py-3.5 px-4 text-center">% Min. B</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kecamatanList as $kec)
                    @php $sisa = $kec['paud_akreditasi']['total'] - $kec['paud_akreditasi']['min_b']; @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-mono text-slate-400">{{ $kec['kode'] }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $kec['nama'] }}</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ $kec['paud_akreditasi']['total'] }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-amber-700">{{ $kec['paud_akreditasi']['min_b'] }}</td>
                        <td class="py-3.5 px-4 text-center text-slate-400">{{ $sisa }}</td>
                        <td class="py-3.5 px-4 text-center font-extrabold text-slate-900">{{ number_format($kec['paud_akreditasi']['persentase'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $kec['paud_akreditasi']['persentase'] >= 68 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $kec['paud_akreditasi']['persentase'] >= 68 ? 'Target Tercapai' : 'Proses Akreditasi' }}
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
