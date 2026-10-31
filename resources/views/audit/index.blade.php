@extends('layouts.app', ['title' => 'Log System'])

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ $tab ?? 'audit' }}',
    detailModal: false,
    selectedError: null,
    selectedErrorIds: [],
    selectAllErrors: false,
    toggleAllErrors(event, totalOnPage) {
        if (event.target.checked) {
            const checkboxes = document.querySelectorAll('.error-item-checkbox');
            this.selectedErrorIds = Array.from(checkboxes).map(cb => cb.value);
        } else {
            this.selectedErrorIds = [];
        }
    }
}">

    <!-- Header Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-slate-100 via-slate-50 to-white text-slate-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-xs border border-slate-200">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-slate-200 text-slate-800 tracking-wider">Pemantauan & Kepatuhan Sistem</span>
                <h2 class="text-lg font-bold text-slate-900">Log System: Audit Trail & Riwayat Error</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Pusat transparansi sistem untuk memantau rekam jejak aktivitas pengguna (Audit Trail) dan rekaman galat aplikasi (Log Error) dengan zona waktu resmi <strong>WIB (Asia/Jakarta)</strong>.
            </p>
        </div>

        <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-2xl border border-slate-200 text-xs font-bold shrink-0">
            <a href="{{ route('audit.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'audit'])) }}" 
               @click.prevent="activeTab = 'audit'; window.history.pushState(null, '', '{{ route('audit.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'audit'])) }}')"
               class="px-4 py-2 rounded-xl transition cursor-pointer flex items-center gap-2"
               :class="activeTab === 'audit' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Audit Trail</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700">{{ number_format($stats['total']) }}</span>
            </a>

            <a href="{{ route('audit.index', array_merge(request()->except(['tab', 'error_page']), ['tab' => 'error'])) }}" 
               @click.prevent="activeTab = 'error'; window.history.pushState(null, '', '{{ route('audit.index', array_merge(request()->except(['tab', 'error_page']), ['tab' => 'error'])) }}')"
               class="px-4 py-2 rounded-xl transition cursor-pointer flex items-center gap-2"
               :class="activeTab === 'error' ? 'bg-white text-rose-600 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Log Error</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $errorLogs->total() > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }}">{{ number_format($errorLogs->total()) }}</span>
            </a>
        </div>
    </div>

    <!-- TAB 1: AUDIT TRAIL AKTIVITAS -->
    <div x-show="activeTab === 'audit'" class="space-y-6">
        <!-- Summary Stats Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase text-slate-400">Total Aktivitas</span>
                <div class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
                <p class="text-[10px] text-slate-500">Seluruh Transaksi</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase text-slate-400">Data Baru (Create)</span>
                <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['creates']) }}</div>
                <p class="text-[10px] text-slate-500">Penambahan Data</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase text-slate-400">Perubahan (Update)</span>
                <div class="text-xl font-extrabold text-blue-600 mt-1">{{ number_format($stats['updates']) }}</div>
                <p class="text-[10px] text-slate-500">Edit & Pembaruan</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-[11px] font-bold uppercase text-slate-400">Penghapusan</span>
                <div class="text-xl font-extrabold text-rose-600 mt-1">{{ number_format($stats['deletes']) }}</div>
                <p class="text-[10px] text-slate-500">Data Terhapus</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm col-span-2 sm:col-span-1">
                <span class="text-[11px] font-bold uppercase text-slate-400">Impor Berkas</span>
                <div class="text-xl font-extrabold text-purple-600 mt-1">{{ number_format($stats['imports']) }}</div>
                <p class="text-[10px] text-slate-500">CSV & Excel Dapodik</p>
            </div>
        </div>

        <!-- Filter Form Audit -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <form action="{{ route('audit.index') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
                <input type="hidden" name="tab" value="audit">
                <span class="font-bold text-slate-600 uppercase text-[10px] tracking-wider">Filter Audit:</span>
                
                <select name="action" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium">
                    <option value="">Semua Aksi (Action)</option>
                    <option value="CREATE" {{ $filterAction === 'CREATE' ? 'selected' : '' }}>CREATE (Tambah)</option>
                    <option value="UPDATE" {{ $filterAction === 'UPDATE' ? 'selected' : '' }}>UPDATE (Ubah)</option>
                    <option value="DELETE" {{ $filterAction === 'DELETE' ? 'selected' : '' }}>DELETE (Hapus)</option>
                    <option value="IMPORT" {{ $filterAction === 'IMPORT' ? 'selected' : '' }}>IMPORT (Impor)</option>
                    <option value="SYNC" {{ $filterAction === 'SYNC' ? 'selected' : '' }}>SYNC (Sinkronisasi)</option>
                    <option value="EVALUASI" {{ $filterAction === 'EVALUASI' ? 'selected' : '' }}>EVALUASI (Kebijakan)</option>
                </select>

                <select name="module" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium">
                    <option value="">Semua Modul</option>
                    <option value="sekolah" {{ $filterModule === 'sekolah' ? 'selected' : '' }}>Satuan Pendidikan</option>
                    <option value="siswa" {{ $filterModule === 'siswa' ? 'selected' : '' }}>Partisipasi Siswa</option>
                    <option value="guru" {{ $filterModule === 'guru' ? 'selected' : '' }}>Data Guru</option>
                    <option value="penduduk" {{ $filterModule === 'penduduk' ? 'selected' : '' }}>Penduduk Usia Sekolah</option>
                    <option value="prestasi" {{ $filterModule === 'prestasi' ? 'selected' : '' }}>Prestasi Siswa</option>
                    <option value="dapodik" {{ $filterModule === 'dapodik' ? 'selected' : '' }}>Sinkronisasi Dapodik</option>
                    <option value="evaluasi" {{ $filterModule === 'evaluasi' ? 'selected' : '' }}>Evaluasi SAKIP</option>
                </select>

                @if($filterAction || $filterModule)
                    <a href="{{ route('audit.index', ['tab' => 'audit']) }}" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold transition">
                        &times; Reset Filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Data Table Audit -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Riwayat Catatan Audit (Audit Trail)</h3>
                    <p class="text-xs text-slate-500">Menampilkan {{ number_format($logs->total()) }} catatan aktivitas transparan (WIB - Asia/Jakarta)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs datatable" 
                       data-server-side="true" 
                       data-url="{{ route('audit.index', ['tab' => 'audit']) }}" 
                       data-total="{{ $logs->total() }}" 
                       data-current-page="{{ $logs->currentPage() }}" 
                       data-page-size="10">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                            <th class="py-3.5 px-4" data-column="created_at">Waktu (WIB)</th>
                            <th class="py-3.5 px-4" data-column="user_name">Pengguna</th>
                            <th class="py-3.5 px-4 text-center" data-column="action">Aksi</th>
                            <th class="py-3.5 px-4" data-column="module">Modul</th>
                            <th class="py-3.5 px-4" data-column="description">Deskripsi Aktivitas</th>
                            <th class="py-3.5 px-4 text-slate-400 font-mono" data-sortable="false">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @include('audit._rows')
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: LOG ERROR APLIKASI -->
    <div x-show="activeTab === 'error'" x-cloak class="space-y-6">
        <!-- Filter Form Error Log -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
            <form action="{{ route('audit.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <input type="hidden" name="tab" value="error">
                <span class="font-bold text-slate-600 uppercase text-[10px] tracking-wider">Cari & Filter Error:</span>

                <input type="text" 
                       name="error_search" 
                       value="{{ request('error_search') }}" 
                       placeholder="Cari pesan error / kelas..." 
                       class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:ring-2 focus:ring-rose-500/20 outline-none">

                <select name="error_level" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium">
                    <option value="">Semua Level</option>
                    <option value="ERROR" {{ request('error_level') === 'ERROR' ? 'selected' : '' }}>ERROR</option>
                    <option value="CRITICAL" {{ request('error_level') === 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
                    <option value="WARNING" {{ request('error_level') === 'WARNING' ? 'selected' : '' }}>WARNING</option>
                    <option value="NOTICE" {{ request('error_level') === 'NOTICE' ? 'selected' : '' }}>NOTICE</option>
                </select>

                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold transition">
                    Cari
                </button>

                @if(request('error_search') || request('error_level'))
                    <a href="{{ route('audit.index', ['tab' => 'error']) }}" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold transition">
                        &times; Reset
                    </a>
                @endif
            </form>

            <div class="text-[11px] text-slate-500 font-medium shrink-0">
                Berkas: <code class="font-mono text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">storage/logs/laravel.log</code>
            </div>
        </div>

        <!-- Error Log Table -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Rekaman Galat Aplikasi (Application Error Logs)</h3>
                    <p class="text-xs text-slate-500">Mencatat exception, pesan galat, dan stack trace sistem (WIB - Asia/Jakarta)</p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Tombol Batch Delete Terpilih -->
                    <form action="{{ route('audit.errors.destroy') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ' + selectedErrorIds.length + ' log error yang dipilih?')">
                        @csrf
                        @method('DELETE')
                        <template x-for="id in selectedErrorIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit" 
                                x-show="selectedErrorIds.length > 0"
                                x-cloak
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm shadow-rose-500/20 transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Hapus Terpilih (<span x-text="selectedErrorIds.length"></span>)</span>
                        </button>
                    </form>

                    <!-- Tombol Bersihkan Semua Log Error -->
                    @if($errorLogs->total() > 0)
                    <form action="{{ route('audit.errors.destroy') }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin mengosongkan SELURUH berkas log error sistem?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="clear_all" value="1">
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Bersihkan Seluruh Log</span>
                        </button>
                    </form>
                    @endif

                    <span class="px-3 py-1 rounded-xl text-xs font-bold {{ $errorLogs->total() > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        Total: {{ number_format($errorLogs->total()) }} Insiden
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 font-bold uppercase text-[11px] border-b border-slate-200">
                            <th class="py-3.5 px-3 w-10 text-center">
                                <input type="checkbox" 
                                       @change="toggleAllErrors($event)"
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Waktu Kejadian (WIB)</th>
                            <th class="py-3.5 px-4 text-center">Level</th>
                            <th class="py-3.5 px-4 text-center">Env</th>
                            <th class="py-3.5 px-4">Pesan Kesalahan (Headline)</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($errorLogs as $err)
                        <tr class="hover:bg-slate-50 transition" :class="selectedErrorIds.includes('{{ $err['id'] }}') ? 'bg-blue-50/50' : ''">
                            <td class="py-3.5 px-3 text-center">
                                <input type="checkbox" 
                                       value="{{ $err['id'] }}" 
                                       x-model="selectedErrorIds"
                                       class="error-item-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500 whitespace-nowrap">
                                {{ $err['time']->format('d/m/Y H:i:s') }} <span class="text-[10px] text-slate-400 font-sans">WIB</span>
                                <span class="block text-[10px] text-slate-400 font-sans">{{ $err['time']->diffForHumans() }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase
                                    {{ $err['level'] === 'ERROR' || $err['level'] === 'CRITICAL' ? 'bg-rose-100 text-rose-800' : ($err['level'] === 'WARNING' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $err['level'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-[11px] text-slate-500">
                                {{ $err['env'] }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-800">
                                <span class="font-bold block text-slate-900 leading-snug break-all">{{ $err['headline'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            @click="selectedError = {{ json_encode($err) }}; detailModal = true"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition cursor-pointer"
                                            title="Lihat Rincian Stack Trace">
                                        Trace
                                    </button>

                                    <!-- Single Delete Form -->
                                    <form action="{{ route('audit.errors.destroy') }}" method="POST" onsubmit="return confirm('Hapus catatan log error ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="ids[]" value="{{ $err['id'] }}">
                                        <button type="submit" 
                                                class="p-1 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition cursor-pointer"
                                                title="Hapus Error Ini">
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
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-emerald-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="font-bold text-slate-700">Tidak ada log error yang tercatat</p>
                                <p class="text-[11px] text-slate-500">Sistem aplikasi berjalan dengan normal dan stabil.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($errorLogs->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $errorLogs->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- MODAL DETAIL STACK TRACE ERROR -->
    <div x-show="detailModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @click.away="detailModal = false">
        <div class="bg-white rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-5 max-h-[90vh] flex flex-col">
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-rose-100 text-rose-800" x-text="selectedError?.level"></span>
                        <span class="text-xs text-slate-400 font-mono" x-text="selectedError ? new Date(selectedError.time).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) + ' WIB' : ''"></span>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 break-all" x-text="selectedError?.headline"></h3>
                </div>
                <button type="button" @click="detailModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer p-1">&times;</button>
            </div>

            <div class="flex-1 overflow-y-auto space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pesan Lengkap & Exception:</label>
                    <div class="p-4 rounded-2xl bg-slate-900 text-rose-400 font-mono text-[11px] overflow-x-auto leading-relaxed whitespace-pre-wrap select-all" x-text="selectedError?.body"></div>
                </div>

                <div x-show="selectedError?.stack">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Stack Trace:</label>
                    <div class="p-4 rounded-2xl bg-slate-950 text-slate-300 font-mono text-[10px] overflow-x-auto leading-relaxed whitespace-pre select-all max-h-72" x-text="selectedError?.stack"></div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button type="button" @click="detailModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
