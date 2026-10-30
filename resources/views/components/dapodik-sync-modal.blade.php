@props(['defaultTarget' => 'semua', 'tahun' => 2025])
@php
    $hasDummyData = \App\Models\SatuanPendidikan::whereBetween('npsn', ['20300001', '20300500'])->exists()
        || \App\Models\GuruPendidik::where('nama', 'like', 'Guru %')->exists();
@endphp

<div x-show="dapodikModal" 
     x-cloak 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
     x-data="{
        tab: 'agent',
        target: '{{ $defaultTarget }}',
        entityFile: '{{ $defaultTarget === 'semua' ? 'sekolah' : $defaultTarget }}',
        tahunTarget: {{ (int) $tahun }},
        purgeDummy: {{ $hasDummyData ? 'true' : 'false' }},
        syncing: false,
        taskId: null,
        status: '',
        message: '',
        counts: { sekolah: 0, siswa: 0, guru: 0, total: 0 },
        pollTimer: null,
        triggerAgentSync() {
            this.syncing = true;
            this.status = 'pending';
            this.message = 'Mengirim permintaan sinkronisasi ke antrean SIMAK...';
            fetch('{{ route('dapodik.trigger') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ target: this.target, semester_id: '20261', purge_dummy: this.purgeDummy })
            })
            .then(r => r.json())
            .then(data => {
                this.taskId = data.task_id;
                this.message = data.message || 'Menunggu agen PowerShell di Windows Server Kominfo...';
                this.startPolling();
            })
            .catch(err => {
                this.syncing = false;
                this.status = 'failed';
                this.message = 'Gagal membuat tugas sinkronisasi: ' + err.message;
            });
        },
        startPolling() {
            if (this.pollTimer) clearInterval(this.pollTimer);
            this.pollTimer = setInterval(() => {
                fetch('/dapodik-sync/status/' + this.taskId, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    this.status = data.status;
                    this.message = data.message;
                    this.counts = {
                        sekolah: data.sekolah_synced || 0,
                        siswa: data.siswa_synced || 0,
                        guru: data.guru_synced || 0,
                        total: data.total_records || 0
                    };
                    if (data.status === 'completed') {
                        clearInterval(this.pollTimer);
                        this.syncing = false;
                        setTimeout(() => window.location.reload(), 1500);
                    } else if (data.status === 'failed') {
                        clearInterval(this.pollTimer);
                        this.syncing = false;
                    }
                });
            }, 2500);
        }
     }">
    <div @click.away="if (!syncing) dapodikModal = false" 
         class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5 overflow-hidden">
        
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Sinkronisasi Data Dapodik Banjarnegara</h3>
                    <p class="text-[11px] text-slate-500">Sumber: SQL Server Datamart Kominfo (`kode_kabupaten = 030400`)</p>
                </div>
            </div>
            <button type="button" @click="if (!syncing) dapodikModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Pilihan Metode Sinkronisasi -->
        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-2xl text-xs font-bold">
            <button type="button" 
                    @click="tab = 'agent'" 
                    :class="tab === 'agent' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="py-2 px-3 rounded-xl transition cursor-pointer">
                1. Tarik Otomatis (Agent Server)
            </button>
            <button type="button" 
                    @click="tab = 'file'" 
                    :class="tab === 'file' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                    class="py-2 px-3 rounded-xl transition cursor-pointer">
                2. Unggah Hasil View SSMS (.csv)
            </button>
        </div>

        <!-- TAB 1: REVERSE PUSH AGENT KOMINFO -->
        <div x-show="tab === 'agent'" class="space-y-4">
            <div class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-100 text-xs text-slate-700 space-y-1">
                <p class="font-bold text-indigo-950">Mode Reverse Push / Polling Agent (Opsi 3)</p>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                    Saat tombol diklik, SIMAK membuat antrean tugas. Skrip PowerShell (`dapodik_agent.ps1`) di Windows Server Kominfo akan mengeksekusi View SQL Server (`v_simak_*_bna`) dan mengirim datanya ke SIMAK secara bertahap.
                </p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Cakupan Data yang Disinkronkan</label>
                <select x-model="target" :disabled="syncing" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="semua">Semua Data Dapodik (Sekolah + Partisipasi Siswa + Guru)</option>
                    <option value="sekolah">Hanya Satuan Pendidikan (dbo.v_simak_sekolah_bna)</option>
                    <option value="siswa">Hanya Partisipasi Siswa (dbo.v_simak_siswa_bna)</option>
                    <option value="guru">Hanya Data Guru & Sertifikasi (dbo.v_simak_guru_bna)</option>
                </select>
            </div>

            @if($hasDummyData)
            <label class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200/80 cursor-pointer">
                <input type="checkbox" x-model="purgeDummy" :disabled="syncing" class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-[11px] text-slate-700 leading-snug">
                    <strong class="font-bold text-slate-900">Bersihkan data dummy bawaan seeder sebelum sinkronisasi</strong>
                    <span class="block text-[10px] text-slate-500">Menghapus sekolah dummy (NPSN 20300001..20300500) & guru dummy agar tidak tercampur dengan data riil Dapodik.</span>
                </span>
            </label>
            @endif

            <!-- Panel Status Live Polling -->
            <div x-show="status !== ''" class="p-4 rounded-2xl border space-y-3"
                 :class="{
                    'bg-amber-50/80 border-amber-200 text-amber-900': status === 'pending' || status === 'processing',
                    'bg-emerald-50 border-emerald-200 text-emerald-900': status === 'completed',
                    'bg-rose-50 border-rose-200 text-rose-900': status === 'failed'
                 }">
                <div class="flex items-center justify-between text-xs font-bold">
                    <span x-text="status === 'completed' ? '✅ Sinkronisasi Selesai' : (status === 'failed' ? '❌ Sinkronisasi Gagal' : '⏳ Sedang Menyinkronkan...')"></span>
                    <span class="font-mono text-[11px]" x-text="'Total: ' + counts.total + ' baris'"></span>
                </div>
                <p class="text-[11px]" x-text="message"></p>
                <div class="grid grid-cols-3 gap-2 pt-1 text-center">
                    <div class="bg-white/80 rounded-xl p-2 border border-slate-200/60">
                        <div class="text-[10px] text-slate-500 font-semibold">Sekolah</div>
                        <div class="text-sm font-extrabold text-slate-900" x-text="counts.sekolah"></div>
                    </div>
                    <div class="bg-white/80 rounded-xl p-2 border border-slate-200/60">
                        <div class="text-[10px] text-slate-500 font-semibold">Rekap Siswa</div>
                        <div class="text-sm font-extrabold text-slate-900" x-text="counts.siswa"></div>
                    </div>
                    <div class="bg-white/80 rounded-xl p-2 border border-slate-200/60">
                        <div class="text-[10px] text-slate-500 font-semibold">Guru & Tendik</div>
                        <div class="text-sm font-extrabold text-slate-900" x-text="counts.guru"></div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" @click="dapodikModal = false" :disabled="syncing" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Tutup
                </button>
                <button type="button" @click="triggerAgentSync()" :disabled="syncing" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-xs font-bold shadow-sm cursor-pointer">
                    <svg x-show="syncing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="syncing ? 'Memproses...' : 'Tarik Data dari Server Kominfo'"></span>
                </button>
            </div>
        </div>

        <!-- TAB 2: UNGGAH LANGSUNG FILE CSV HASIL SQL VIEW SSMS -->
        <form x-show="tab === 'file'" action="{{ route('dapodik.import_view') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-xs text-slate-700 space-y-1">
                <p class="font-bold text-emerald-950">Sinkronisasi Langsung dari Hasil Query View SSMS</p>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                    Di SSMS, jalankan `SELECT * FROM dbo.v_simak_sekolah_bna` (atau `v_simak_siswa_bna` / `v_simak_guru_bna`), klik kanan pada tabel hasil &rarr; **Save Results As... &rarr; CSV**, lalu unggah file tersebut di sini.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis View yang Diunggah</label>
                    <select name="entity" x-model="entityFile" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-semibold text-slate-800">
                        <option value="sekolah">1. View Sekolah (v_simak_sekolah_bna)</option>
                        <option value="siswa">2. View Partisipasi Siswa (v_simak_siswa_bna)</option>
                        <option value="guru">3. View Guru & Sertifikasi (v_simak_guru_bna)</option>
                    </select>
                </div>
                <div x-show="entityFile === 'siswa'">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Simpan untuk Tahun Evaluasi</label>
                    <select name="tahun_target" x-model="tahunTarget" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-semibold text-slate-800">
                        <option value="2025">Tahun 2025 (Renstra Aktif)</option>
                        <option value="2026">Tahun 2026 (Semester 20261)</option>
                        <option value="2024">Tahun 2024</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih File CSV / TXT Hasil Ekspor SSMS</label>
                <input type="file" name="file_dapodik" accept=".csv,.txt,.tsv" required class="w-full text-xs text-slate-700 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl p-1.5">
            </div>

            @if($hasDummyData)
            <label class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200/80 cursor-pointer">
                <input type="checkbox" name="purge_dummy" value="1" checked class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span class="text-[11px] text-slate-700 leading-snug">
                    <strong class="font-bold text-slate-900">Bersihkan data dummy bawaan seeder sebelum sinkronisasi</strong>
                    <span class="block text-[10px] text-slate-500">Menghapus sekolah & guru dummy bawaan awal agar tidak tercampur dengan data riil Dapodik.</span>
                </span>
            </label>
            @endif

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" @click="dapodikModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm cursor-pointer">
                    <span>Proses & Sinkronkan Sekarang</span>
                </button>
            </div>
        </form>

    </div>
</div>

