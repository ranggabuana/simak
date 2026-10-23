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
            $rowJson = json_encode($s->only(['id', 'npsn', 'nama_sekolah', 'jenjang', 'status', 'akreditasi', 'kecamatan_id', 'alamat', 'kepala_sekolah', 'telepon', 'latitude', 'longitude']));
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
    <td colspan="8" class="text-center py-10 text-slate-400">Belum ada data satuan pendidikan yang sesuai filter atau pencarian.</td>
</tr>
@endforelse
