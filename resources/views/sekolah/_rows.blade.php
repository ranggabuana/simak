@forelse($sekolahList as $s)
<tr class="hover:bg-slate-50 transition">
    <td class="py-3.5 px-4 font-mono font-bold text-blue-700">{{ $s->npsn }}</td>
    <td class="py-3.5 px-4">
        <span class="font-bold text-slate-900 block">{{ $s->nama_sekolah }}</span>
        @if($s->telepon)
            <span class="text-[10px] text-slate-400 font-mono">Telp: {{ $s->telepon }}</span>
        @endif
    </td>
    <td class="py-3.5 px-4 text-center">
        <span class="px-2 py-0.5 rounded text-[10px] font-bold 
            {{ in_array($s->jenjang, ['PAUD', 'TK']) ? 'bg-amber-50 text-amber-700' : (in_array($s->jenjang, ['SD', 'MI']) ? 'bg-blue-50 text-blue-700' : (in_array($s->jenjang, ['SMP', 'MTS']) ? 'bg-indigo-50 text-indigo-700' : 'bg-purple-50 text-purple-700')) }}">
            {{ $s->jenjang }}
        </span>
    </td>
    <td class="py-3.5 px-4 text-center font-medium">
        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $s->status === 'Negeri' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
            {{ $s->status }}
        </span>
    </td>
    <td class="py-3.5 px-4 text-center">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold 
            {{ $s->akreditasi === 'A' ? 'bg-emerald-100 text-emerald-800' : ($s->akreditasi === 'B' ? 'bg-blue-100 text-blue-800' : ($s->akreditasi === 'C' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500')) }}">
            {{ $s->akreditasi }}
        </span>
    </td>
    <td class="py-3.5 px-4 text-slate-700 font-medium">
        {{ $s->kecamatan->nama ?? '-' }}
    </td>
    <td class="py-3.5 px-4 text-slate-500">
        <div class="font-medium text-slate-700">{{ $s->kepala_sekolah ?: '-' }}</div>
        <div class="text-[11px] text-slate-400 truncate max-w-[200px]">{{ $s->alamat ?: '-' }}</div>
    </td>
    <td class="py-3.5 px-4 text-center">
        @php
            $rowDetail = [
                'id' => $s->id,
                'npsn' => $s->npsn,
                'nama_sekolah' => $s->nama_sekolah,
                'jenjang' => $s->jenjang,
                'status' => $s->status,
                'akreditasi' => $s->akreditasi,
                'kecamatan_nama' => $s->kecamatan->nama ?? '-',
                'alamat' => $s->alamat ?: '-',
                'kepala_sekolah' => $s->kepala_sekolah ?: '-',
                'telepon' => $s->telepon ?: '-',
                'latitude' => $s->latitude,
                'longitude' => $s->longitude,
            ];
        @endphp
        <button type="button" 
                @click="activeRow = {{ json_encode($rowDetail) }}; detailModal = true" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition cursor-pointer shadow-2xs" 
                title="Lihat Detail Satuan Pendidikan">
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
    <td colspan="8" class="text-center py-10 text-slate-400">Belum ada data satuan pendidikan yang sesuai filter atau pencarian.</td>
</tr>
@endforelse
