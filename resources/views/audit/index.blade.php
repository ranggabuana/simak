@extends('layouts.app', ['title' => 'Audit Trail & Log Aktivitas'])

@section('content')
<div class="space-y-8">

    <!-- Header Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-slate-100 via-slate-50 to-white text-slate-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-xs border border-slate-200">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-slate-200 text-slate-800 tracking-wider">Kepatuhan SAKIP & Integritas Data</span>
                <h2 class="text-lg font-bold text-slate-900">Log Aktivitas & Audit Trail Perubahan Data</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Rekam jejak digital riwayat penambahan, modifikasi, penghapusan, impor data, serta perumusan rekomendasi kebijakan oleh pengguna sistem.
            </p>
        </div>
    </div>

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

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('audit.index') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
            <span class="font-bold text-slate-600 uppercase text-[10px] tracking-wider">Filter Audit:</span>
            
            <select name="action" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium">
                <option value="">Semua Aksi (Action)</option>
                <option value="CREATE" {{ $filterAction === 'CREATE' ? 'selected' : '' }}>CREATE (Tambah)</option>
                <option value="UPDATE" {{ $filterAction === 'UPDATE' ? 'selected' : '' }}>UPDATE (Ubah)</option>
                <option value="DELETE" {{ $filterAction === 'DELETE' ? 'selected' : '' }}>DELETE (Hapus)</option>
                <option value="IMPORT" {{ $filterAction === 'IMPORT' ? 'selected' : '' }}>IMPORT (Impor)</option>
                <option value="EVALUASI" {{ $filterAction === 'EVALUASI' ? 'selected' : '' }}>EVALUASI (Kebijakan)</option>
            </select>

            <select name="module" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 font-medium">
                <option value="">Semua Modul</option>
                <option value="sekolah" {{ $filterModule === 'sekolah' ? 'selected' : '' }}>Satuan Pendidikan</option>
                <option value="siswa" {{ $filterModule === 'siswa' ? 'selected' : '' }}>Partisipasi Siswa</option>
                <option value="guru" {{ $filterModule === 'guru' ? 'selected' : '' }}>Data Guru</option>
                <option value="penduduk" {{ $filterModule === 'penduduk' ? 'selected' : '' }}>Penduduk Usia Sekolah</option>
                <option value="prestasi" {{ $filterModule === 'prestasi' ? 'selected' : '' }}>Prestasi Siswa</option>
                <option value="evaluasi" {{ $filterModule === 'evaluasi' ? 'selected' : '' }}>Evaluasi SAKIP</option>
            </select>

            @if($filterAction || $filterModule)
                <a href="{{ route('audit.index') }}" class="px-3 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold transition">
                    &times; Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Riwayat Catatan Audit (Audit Trail)</h3>
                <p class="text-xs text-slate-500">Menampilkan {{ number_format($logs->total()) }} catatan aktivitas transparan</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs datatable" 
                   data-server-side="true" 
                   data-url="{{ route('audit.index') }}" 
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
@endsection
