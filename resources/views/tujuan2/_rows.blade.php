@forelse($prestasiList as $pres)
<tr class="hover:bg-slate-50 transition">
    <td class="py-3.5 px-4 font-bold text-slate-900">{{ $pres->nama_siswa }}</td>
    <td class="py-3.5 px-4 font-mono text-slate-500">{{ $pres->nisn ?? '-' }}</td>
    <td class="py-3.5 px-4 font-semibold text-slate-800">
        {{ $pres->nama_kejuaraan }}
        @if($pres->keterangan)
        <span class="block text-[11px] text-slate-400 font-normal italic">"{{ $pres->keterangan }}"</span>
        @endif
    </td>
    <td class="py-3.5 px-4 text-slate-600">{{ $pres->bidang }}</td>
    <td class="py-3.5 px-4 text-center">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-50 text-blue-700 border border-blue-200">
            {{ $pres->tingkat }}
        </span>
    </td>
    <td class="py-3.5 px-4 text-center">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
            {{ $pres->peringkat }}
        </span>
    </td>
    <td class="py-3.5 px-4 text-slate-700">
        {{ $pres->satuanPendidikan->nama_sekolah ?? '-' }}
        <span class="block text-[10px] text-slate-400">Kec. {{ $pres->kecamatan->nama ?? '-' }}</span>
    </td>
    <td class="py-3.5 px-4 text-center font-medium text-slate-800">{{ $pres->tahun }}</td>
    <td class="py-3.5 px-4 text-center">
        @php
            $rowJson = json_encode([
                'id' => $pres->id,
                'nama_siswa' => $pres->nama_siswa,
                'nisn' => $pres->nisn ?? '',
                'satuan_pendidikan_id' => $pres->satuan_pendidikan_id,
                'nama_kejuaraan' => $pres->nama_kejuaraan,
                'bidang' => $pres->bidang,
                'tingkat' => $pres->tingkat,
                'peringkat' => $pres->peringkat,
                'tahun' => $pres->tahun,
                'penyelenggara' => $pres->penyelenggara ?? '',
                'keterangan' => $pres->keterangan ?? '',
            ]);
        @endphp
        <div class="flex items-center justify-center gap-1.5">
            <button type="button" 
                    @click="activeRow = {{ $rowJson }}; editModal = true" 
                    class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer" 
                    title="Edit Data Prestasi">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <form action="{{ route('tujuan2.destroy', $pres->id) }}" method="POST" onsubmit="return confirm('Hapus data prestasi ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition cursor-pointer" title="Hapus Data">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada data prestasi siswa yang sesuai filter atau pencarian.</td>
</tr>
@endforelse
