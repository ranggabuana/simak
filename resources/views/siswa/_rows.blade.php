@forelse($siswaList as $row)
@php
    $gender = $gender ?? 'all';
    $rasio = $row->total_siswa_semua_usia > 0 ? round(($row->siswa_usia_sesuai / $row->total_siswa_semua_usia) * 100, 1) : 0;
    $avgRombel = $row->jumlah_rombel > 0 ? round($row->total_siswa_semua_usia / $row->jumlah_rombel, 1) : 0;
    $rowData = [
        'id' => $row->id,
        'satuan_pendidikan_id' => $row->satuan_pendidikan_id,
        'npsn' => $row->satuanPendidikan->npsn ?? '-',
        'nama_sekolah' => $row->satuanPendidikan->nama_sekolah ?? '-',
        'jenjang' => $row->jenjang,
        'kecamatan_nama' => $row->satuanPendidikan->kecamatan->nama ?? ($row->kecamatan->nama ?? '-'),
        'tahun' => $row->tahun,
        'tahun_ajaran' => $row->tahun_ajaran,
        'total_siswa_semua_usia' => (int) $row->total_siswa_semua_usia,
        'siswa_usia_sesuai' => (int) $row->siswa_usia_sesuai,
        'rasio' => $rasio,
        'siswa_l' => (int) ($row->siswa_l ?? 0),
        'siswa_p' => (int) ($row->siswa_p ?? 0),
        'siswa_usia_sesuai_l' => (int) ($row->siswa_usia_sesuai_l ?? 0),
        'siswa_usia_sesuai_p' => (int) ($row->siswa_usia_sesuai_p ?? 0),
        'siswa_usia_5_6' => (int) ($row->siswa_usia_5_6 ?? 0),
        'siswa_usia_7_12' => (int) ($row->siswa_usia_7_12 ?? 0),
        'siswa_usia_13_15' => (int) ($row->siswa_usia_13_15 ?? 0),
        'siswa_usia_16_18' => (int) ($row->siswa_usia_16_18 ?? 0),
        'siswa_usia_lainnya' => (int) ($row->siswa_usia_lainnya ?? 0),
        'jumlah_rombel' => (int) $row->jumlah_rombel,
        'avg_rombel' => $avgRombel,
    ];
@endphp
<tr class="hover:bg-slate-50 transition">
    <td class="py-3.5 px-4 font-mono font-bold text-blue-700">{{ $row->satuanPendidikan->npsn ?? '-' }}</td>
    <td class="py-3.5 px-4 font-bold text-slate-900">{{ $row->satuanPendidikan->nama_sekolah ?? '-' }}</td>
    <td class="py-3.5 px-4 text-center">
        <span class="px-2 py-0.5 rounded text-[10px] font-bold 
            {{ in_array($row->jenjang, ['PAUD', 'TK']) ? 'bg-amber-50 text-amber-700' : (in_array($row->jenjang, ['SD', 'MI']) ? 'bg-blue-50 text-blue-700' : (in_array($row->jenjang, ['SMP', 'MTS']) ? 'bg-indigo-50 text-indigo-700' : 'bg-purple-50 text-purple-700')) }}">
            {{ $row->jenjang }}
        </span>
    </td>
    <td class="py-3.5 px-4 text-slate-700 font-medium">
        {{ $row->satuanPendidikan->kecamatan->nama ?? '-' }}
    </td>
    <td class="py-3.5 px-4 text-center font-mono text-slate-500">
        {{ $row->tahun_ajaran }}
    </td>
    <td class="py-3.5 px-4 text-right">
        @if($gender === 'L')
            <span class="font-extrabold text-blue-700">{{ number_format($row->siswa_l) }}</span>
            <span class="block text-[10px] text-blue-500">Laki-laki</span>
        @elseif($gender === 'P')
            <span class="font-extrabold text-pink-700">{{ number_format($row->siswa_p) }}</span>
            <span class="block text-[10px] text-pink-500">Perempuan</span>
        @else
            <span class="font-extrabold text-slate-900">{{ number_format($row->total_siswa_semua_usia) }}</span>
            <span class="block text-[10px] text-slate-400 font-normal">
                <span class="text-blue-600 font-semibold">L: {{ number_format($row->siswa_l) }}</span> &bull; 
                <span class="text-pink-600 font-semibold">P: {{ number_format($row->siswa_p) }}</span>
            </span>
        @endif
    </td>
    <td class="py-3.5 px-4 text-right font-bold text-blue-600">
        @if($gender === 'L')
            <span class="font-extrabold text-blue-700">{{ number_format($row->siswa_usia_sesuai_l) }}</span>
        @elseif($gender === 'P')
            <span class="font-extrabold text-pink-700">{{ number_format($row->siswa_usia_sesuai_p) }}</span>
        @else
            <span>{{ number_format($row->siswa_usia_sesuai) }}</span>
            <span class="block text-[10px] text-slate-400 font-normal">
                <span class="text-blue-600 font-medium">L: {{ number_format($row->siswa_usia_sesuai_l) }}</span> &bull; 
                <span class="text-pink-600 font-medium">P: {{ number_format($row->siswa_usia_sesuai_p) }}</span>
            </span>
        @endif
    </td>
    <td class="py-3.5 px-4 text-right font-extrabold text-emerald-600">
        {{ $rasio }}%
    </td>
    <td class="py-3.5 px-4 text-center font-bold text-slate-700">
        {{ $row->jumlah_rombel }}
    </td>
    <td class="py-3.5 px-4 text-center">
        <button type="button" 
                @click="activeRow = {{ json_encode($rowData) }}; detailModal = true" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition cursor-pointer shadow-2xs" 
                title="Lihat Detail Partisipasi Siswa">
            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span>Detail</span>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center py-10 text-slate-400">Belum ada data partisipasi siswa yang sesuai filter atau pencarian.</td>
</tr>
@endforelse
