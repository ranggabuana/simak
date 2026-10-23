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
            $rowJson = json_encode($g->only(['id', 'satuan_pendidikan_id', 'nama', 'nuptk', 'nip', 'jenis_kelamin', 'status_kepegawaian', 'sudah_sertifikasi', 'bidang_studi']));
        @endphp
        <div class="flex items-center justify-center gap-1.5">
            <button type="button" 
                    @click="activeRow = {{ $rowJson }}; editModal = true" 
                    class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer" 
                    title="Edit Data">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button type="button" 
                    @click="activeRow = {{ $rowJson }}; deleteModal = true" 
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
    <td colspan="8" class="text-center py-10 text-slate-400">Belum ada data tenaga pendidik yang cocok dengan filter atau pencarian.</td>
</tr>
@endforelse
