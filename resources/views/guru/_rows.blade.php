@forelse($guruList as $g)
<tr class="hover:bg-slate-50 transition">
    <td class="py-3.5 px-4">
        <div class="font-bold text-slate-900">{{ $g->nama }}</div>
        <span class="text-[10px] text-slate-400 font-semibold uppercase">JK: {{ $g->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
    </td>
    <td class="py-3.5 px-4 font-mono text-slate-600">
        <div><strong class="text-blue-700">{{ $g->nuptk ?: '-' }}</strong></div>
        @if($g->nip)
            <div class="text-[10px] text-slate-400">NIP: {{ $g->nip }}</div>
        @endif
    </td>
    <td class="py-3.5 px-4 font-medium text-slate-800">
        {{ $g->satuanPendidikan->nama_sekolah ?? '-' }}
        <span class="inline-block ml-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600">
            {{ $g->satuanPendidikan->jenjang ?? '' }}
        </span>
    </td>
    <td class="py-3.5 px-4 text-slate-600 font-medium">
        {{ $g->kecamatan->nama ?? '-' }}
    </td>
    <td class="py-3.5 px-4 text-center font-bold">
        <span class="px-2 py-0.5 rounded text-[10px] 
            {{ $g->status_kepegawaian === 'PNS' ? 'bg-blue-50 text-blue-700' : ($g->status_kepegawaian === 'PPPK' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-600') }}">
            {{ $g->status_kepegawaian }}
        </span>
    </td>
    <td class="py-3.5 px-4 text-slate-600">
        {{ $g->bidang_studi ?: 'Guru Kelas/Mapel' }}
    </td>
    <td class="py-3.5 px-4 text-center">
        @if($g->sudah_sertifikasi)
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-100 text-purple-800 border border-purple-200">
                &check; Bersertifikat
            </span>
        @else
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                Belum
            </span>
        @endif
    </td>
    <td class="py-3.5 px-4 text-center">
        @php
            $rowDetail = [
                'id' => $g->id,
                'nama' => $g->nama,
                'nuptk' => $g->nuptk ?: '-',
                'nip' => $g->nip ?: '-',
                'jenis_kelamin' => $g->jenis_kelamin,
                'jenis_kelamin_label' => $g->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                'status_kepegawaian' => $g->status_kepegawaian,
                'bidang_studi' => $g->bidang_studi ?: 'Guru Kelas / Mapel',
                'sudah_sertifikasi' => (bool) $g->sudah_sertifikasi,
                'satuan_pendidikan_nama' => $g->satuanPendidikan->nama_sekolah ?? '-',
                'satuan_pendidikan_npsn' => $g->satuanPendidikan->npsn ?? '-',
                'satuan_pendidikan_jenjang' => $g->satuanPendidikan->jenjang ?? '-',
                'kecamatan_nama' => $g->kecamatan->nama ?? '-',
            ];
        @endphp
        <button type="button" 
                @click="activeRow = {{ json_encode($rowDetail) }}; detailModal = true" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs transition cursor-pointer shadow-2xs" 
                title="Lihat Detail Tenaga Pendidik">
            <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span>Detail</span>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-10 text-slate-400">Belum ada data tenaga pendidik yang cocok dengan filter atau pencarian.</td>
</tr>
@endforelse
