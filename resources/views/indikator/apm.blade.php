@extends('layouts.app', ['title' => 'Angka Partisipasi Murni (APM)'])

@section('content')
<div class="space-y-8">

    <!-- Formula Reference Banner (From Flowchart.pdf) -->
    <div class="p-6 rounded-3xl bg-indigo-50/70 border border-indigo-200 text-indigo-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-sm">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-indigo-600 text-white tracking-wider">Formula Flowchart</span>
                <h2 class="text-base font-bold text-slate-900">Rumus Angka Partisipasi Murni (APM)</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-mono bg-white/80 p-2.5 rounded-xl border border-indigo-100">
                APM = (Jumlah anak usia sekolah tertentu yang bersekolah pada jenjang tersebut / Jumlah anak usia sekolah tertentu pada kabupaten/kota) &times; 100
            </p>
            <p class="text-[11px] text-slate-500 italic">
                *Contoh: APM SD/MI = (Jumlah siswa jenjang SD & MI usia 7 - 12 tahun / Jumlah anak usia SD&MI (7-12 Tahun) di Kabupaten Banjarnegara) &times; 100
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('export.kecamatan', ['tahun' => $tahun]) }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-indigo-700 border border-indigo-200 font-bold text-xs shadow-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- 4 Age Breakdown Cards (5-6, 7-12, 13-15, 16-18) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 5 - 6 Tahun (PAUD) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APM 5 – 6 TAHUN (PAUD/TK)</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['apm']['paud'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_apm_paud ?? 65.2 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Siswa Usia Tepat (5-6 Th):</span> <strong>{{ number_format($kabupaten['populasi']['usia_5_6'] * ($kabupaten['apm']['paud']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 5–6 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_5_6']) }}</strong></div>
            </div>
        </div>

        <!-- 7 - 12 Tahun (SD/MI) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APM 7 – 12 TAHUN (SD/MI)</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-indigo-600">{{ number_format($kabupaten['apm']['sd'], 2) }}%</span>
                <span class="text-xs font-semibold text-emerald-600">Target: {{ $kabupaten['target']?->target_apm_sd ?? 98.4 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Siswa Usia Tepat (7-12 Th):</span> <strong>{{ number_format($kabupaten['populasi']['usia_7_12'] * ($kabupaten['apm']['sd']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 7–12 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_7_12']) }}</strong></div>
            </div>
        </div>

        <!-- 13 - 15 Tahun (SMP/MTs) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APM 13 – 15 TAHUN (SMP/MTS)</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['apm']['smp'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_apm_smp ?? 86.8 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Siswa Usia Tepat (13-15 Th):</span> <strong>{{ number_format($kabupaten['populasi']['usia_13_15'] * ($kabupaten['apm']['smp']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 13–15 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_13_15']) }}</strong></div>
            </div>
        </div>

        <!-- 16 - 18 Tahun (SMA/SMK) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">APM 16 – 18 TAHUN (SMA/SMK)</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($kabupaten['apm']['sma'], 2) }}%</span>
                <span class="text-xs font-semibold text-slate-500">Target: {{ $kabupaten['target']?->target_apm_sma ?? 72.5 }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <div class="flex justify-between"><span>Siswa Usia Tepat (16-18 Th):</span> <strong>{{ number_format($kabupaten['populasi']['usia_16_18'] * ($kabupaten['apm']['sma']/100)) }}</strong></div>
                <div class="flex justify-between"><span>Populasi 16–18 Th:</span> <strong>{{ number_format($kabupaten['populasi']['usia_16_18']) }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Dual View Switcher (Basic Wilayah vs Basic Sekolah) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-2 p-1 rounded-2xl bg-slate-100 max-w-max">
                <a href="{{ route('indikator.apm', array_merge(request()->query(), ['tab' => 'wilayah'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'wilayah' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Basic Wilayah (20 Kecamatan)
                </a>
                <a href="{{ route('indikator.apm', array_merge(request()->query(), ['tab' => 'sekolah'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'sekolah' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Basic Sekolah (Satuan Pendidikan)
                </a>
            </div>

            @if($tab === 'sekolah')
            <div class="flex flex-wrap items-center gap-2.5">
                <form method="GET" class="flex flex-wrap items-center gap-2.5 text-xs">
                    <input type="hidden" name="tab" value="sekolah">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    
                    <select name="kecamatan_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 outline-none">
                        <option value="">Semua Kecamatan</option>
                        @foreach($allKecamatan as $k)
                            <option value="{{ $k->id }}" {{ $kecamatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>

                    <select name="jenjang" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 outline-none">
                        <option value="">Semua Jenjang</option>
                        <option value="PAUD" {{ $jenjang == 'PAUD' ? 'selected' : '' }}>PAUD/TK</option>
                        <option value="SD" {{ $jenjang == 'SD' ? 'selected' : '' }}>SD</option>
                        <option value="SMP" {{ $jenjang == 'SMP' ? 'selected' : '' }}>SMP</option>
                        <option value="SMA" {{ $jenjang == 'SMA' ? 'selected' : '' }}>SMA</option>
                        <option value="SMK" {{ $jenjang == 'SMK' ? 'selected' : '' }}>SMK</option>
                    </select>
                </form>

                <a href="{{ route('sekolah.index') }}" class="px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition flex items-center gap-1.5 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Kelola Data Sekolah</span>
                </a>
            </div>
            @endif
        </div>

        @if($tab === 'wilayah')
        <!-- Tabel Basic Wilayah -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Nama Kecamatan</th>
                        <th class="py-3.5 px-4 text-center">APM 5–6 Th (PAUD)</th>
                        <th class="py-3.5 px-4 text-center">APM 7–12 Th (SD)</th>
                        <th class="py-3.5 px-4 text-center">APM 13–15 Th (SMP)</th>
                        <th class="py-3.5 px-4 text-center">APM 16–18 Th (SMA)</th>
                        <th class="py-3.5 px-4 text-right">Penduduk 7–12 Th</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kecamatanList as $kec)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-mono text-slate-400">{{ $kec['kode'] }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $kec['nama'] }}</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['apm']['paud'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center font-bold text-indigo-600">{{ number_format($kec['apm']['sd'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['apm']['smp'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-center font-medium">{{ number_format($kec['apm']['sma'], 1) }}%</td>
                        <td class="py-3.5 px-4 text-right font-medium text-slate-700">
                            {{ number_format($kec['populasi']['usia_7_12']) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <!-- Tabel Basic Sekolah -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" data-page-size="10">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                        <th class="py-3.5 px-4">NPSN</th>
                        <th class="py-3.5 px-4">Nama Satuan Pendidikan</th>
                        <th class="py-3.5 px-4">Jenjang</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4 text-center">Akreditasi</th>
                        <th class="py-3.5 px-4 text-right">Siswa Usia Sesuai</th>
                        <th class="py-3.5 px-4 text-right">Total Siswa</th>
                        <th class="py-3.5 px-4 text-center">% Ketepatan Usia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sekolahList as $sch)
                    @php 
                        $p = $sch->partisipasiSiswa->first(); 
                        $rasio = ($p && $p->total_siswa_semua_usia > 0) ? round(($p->siswa_usia_sesuai / $p->total_siswa_semua_usia) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-mono text-slate-400">{{ $sch->npsn }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $sch->nama_sekolah }}</td>
                        <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded font-bold bg-slate-100 text-slate-700 text-[11px]">{{ $sch->jenjang }}</span></td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $sch->kecamatan->nama }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $sch->akreditasi === 'A' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                {{ $sch->akreditasi }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-indigo-700">{{ number_format($p?->siswa_usia_sesuai ?? 0) }}</td>
                        <td class="py-3.5 px-4 text-right font-medium text-slate-600">{{ number_format($p?->total_siswa_semua_usia ?? 0) }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ $rasio }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-6 text-slate-400">Tidak ada data satuan pendidikan ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

</div>
@endsection
