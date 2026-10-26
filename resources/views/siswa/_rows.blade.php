@forelse($siswaList as $row)
@php
    $gender = $gender ?? 'all';
    $rasio = $row->total_siswa_semua_usia > 0 ? round(($row->siswa_usia_sesuai / $row->total_siswa_semua_usia) * 100, 1) : 0;
    $rowData = [
        'id' => $row->id,
        'satuan_pendidikan_id' => $row->satuan_pendidikan_id,
        'satuan_pendidikan' => [
            'nama_sekolah' => $row->satuanPendidikan->nama_sekolah ?? '-',
            'npsn' => $row->satuanPendidikan->npsn ?? '-',
        ],
        'jenjang' => $row->jenjang,
        'tahun' => $row->tahun,
        'tahun_ajaran' => $row->tahun_ajaran,
        'total_siswa_semua_usia' => $row->total_siswa_semua_usia,
        'siswa_usia_sesuai' => $row->siswa_usia_sesuai,
        'siswa_l' => $row->siswa_l ?? 0,
        'siswa_p' => $row->siswa_p ?? 0,
        'siswa_usia_sesuai_l' => $row->siswa_usia_sesuai_l ?? 0,
        'siswa_usia_sesuai_p' => $row->siswa_usia_sesuai_p ?? 0,
        'jumlah_rombel' => $row->jumlah_rombel,
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
        <div class="flex items-center justify-center gap-1.5">
            <button type="button" 
                    @click="activeRow = {{ json_encode($rowData) }}; editModal = true" 
                    class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer" 
                    title="Edit Data">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button type="button" 
                    @click="activeRow = {{ json_encode($rowData) }}; deleteModal = true" 
                    class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition cursor-pointer" 
                    title="Hapus Data">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center py-10 text-slate-400">Belum ada data partisipasi siswa yang sesuai filter atau pencarian.</td>
</tr>
@endforelse
