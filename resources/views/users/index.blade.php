@extends('layouts.app', ['title' => 'Manajemen Pengguna & Hak Akses'])

@section('content')
<div class="space-y-8" 
     x-data="{ 
        currentTab: '{{ $tab }}',
        createModal: false, 
        editModal: false, 
        deleteModal: false, 
        hasPimpinan: {{ $hasPimpinan ? 'true' : 'false' }},
        activeUser: { id: null, name: '', email: '', role: 'operator', nip: '', jabatan: '', kecamatan_id: '' }
     }">

    <!-- Header Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-blue-50/80 via-slate-50 to-white text-slate-900 flex flex-col xl:flex-row xl:items-center justify-between gap-5 shadow-xs border border-blue-200/60">
        <div class="space-y-1.5 flex-1 min-w-0 pr-2">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800 tracking-wider">Tata Kelola & Autentikasi</span>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna & Matriks Hak Akses</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kelola akun pengguna DINDIKPORA berdasarkan peran (Kepala Dinas, Perencana, Operator, Super Admin) dan atur konfigurasi matriks hak akses menu sidebar secara dinamis.
            </p>
        </div>
        
        <!-- Action Row -->
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
            <button type="button" 
                    @click="createModal = true" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm shadow-blue-500/25 transition whitespace-nowrap cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1 shadow-xs">
            <p class="font-bold flex items-center gap-1.5 text-rose-900">
                <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                Terdapat kesalahan pada isian form:
            </p>
            <ul class="list-disc list-inside pl-1 space-y-0.5 text-rose-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Role Summary Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Total -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-400">Total Pengguna</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
            <p class="text-[10px] text-slate-500 mt-0.5">Semua Akun Sistem</p>
        </div>
        <!-- Kepala Dinas -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm border-l-4 border-l-emerald-500">
            <span class="text-[11px] font-bold uppercase text-emerald-600">Kepala Dinas</span>
            <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ number_format($stats['pimpinan']) }}</div>
            <p class="text-[10px] text-slate-500 mt-0.5">Pimpinan Eksekutif</p>
        </div>
        <!-- Perencana -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm border-l-4 border-l-purple-500">
            <span class="text-[11px] font-bold uppercase text-purple-600">Perencana</span>
            <div class="text-2xl font-extrabold text-purple-700 mt-1">{{ number_format($stats['perencana']) }}</div>
            <p class="text-[10px] text-slate-500 mt-0.5">Analis SAKIP & Renstra</p>
        </div>
        <!-- Operator -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm border-l-4 border-l-amber-500">
            <span class="text-[11px] font-bold uppercase text-amber-600">Operator</span>
            <div class="text-2xl font-extrabold text-amber-700 mt-1">{{ number_format($stats['operator']) }}</div>
            <p class="text-[10px] text-slate-500 mt-0.5">Pengelola Data Wilayah</p>
        </div>
        <!-- Super Admin -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm border-l-4 border-l-blue-500">
            <span class="text-[11px] font-bold uppercase text-blue-600">Super Admin</span>
            <div class="text-2xl font-extrabold text-blue-700 mt-1">{{ number_format($stats['admin']) }}</div>
            <p class="text-[10px] text-slate-500 mt-0.5">Akses Penuh Sistem</p>
        </div>
    </div>

    <!-- Navigation Tabs (Users List vs Permission Matrix) -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6" aria-label="Tabs">
            <button type="button" 
                    @click="currentTab = 'users'"
                    :class="currentTab === 'users' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm transition-colors flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Daftar & Kelola Pengguna</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-600 font-bold">{{ $users->total() }}</span>
            </button>

            <button type="button" 
                    @click="currentTab = 'permissions'"
                    :class="currentTab === 'permissions' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm transition-colors flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Matriks Hak Akses Menu</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[9px] bg-emerald-100 text-emerald-800 font-bold uppercase">Role-Based</span>
            </button>
        </nav>
    </div>

    <!-- TAB 1: USERS MANAGEMENT -->
    <div x-show="currentTab === 'users'" class="space-y-6">
        
        <!-- Filter and Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form method="GET" action="{{ route('users.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-3">
                <input type="hidden" name="tab" value="users">
                
                <div class="relative w-full sm:w-80">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Cari nama, email, NIP, jabatan..." 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div class="w-full sm:w-56">
                    <select name="role" 
                            onchange="this.form.submit()" 
                            class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition cursor-pointer">
                        <option value="">-- Semua Peran / Role --</option>
                        <option value="pimpinan" {{ $roleFilter === 'pimpinan' ? 'selected' : '' }}>Kepala Dinas (Pimpinan)</option>
                        <option value="perencana" {{ $roleFilter === 'perencana' ? 'selected' : '' }}>Perencana (SAKIP / Renstra)</option>
                        <option value="operator" {{ $roleFilter === 'operator' ? 'selected' : '' }}>Operator (Wilayah / Korwil)</option>
                        <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition cursor-pointer">
                        Cari
                    </button>
                    @if($search || $roleFilter)
                        <a href="{{ route('users.index', ['tab' => 'users']) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-xs rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Users Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">Pengguna & Akun</th>
                            <th class="py-3.5 px-4">NIP & Jabatan</th>
                            <th class="py-3.5 px-4 text-center">Peran (Role)</th>
                            <th class="py-3.5 px-4">Wilayah Tugas</th>
                            <th class="py-3.5 px-4 text-center">Didaftarkan</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Nama & Email -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-200 shadow-2xs">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="truncate">
                                            <p class="font-bold text-slate-900 text-xs">{{ $user->name }}</p>
                                            <p class="text-slate-500 text-[11px] font-mono">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- NIP & Jabatan -->
                                <td class="py-3.5 px-4">
                                    <p class="font-medium text-slate-800">{{ $user->jabatan ?: '-' }}</p>
                                    <p class="text-slate-500 font-mono text-[11px]">{{ $user->nip ?: 'NIP: -' }}</p>
                                </td>

                                <!-- Role Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($user->role === 'pimpinan')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Kepala Dinas
                                        </span>
                                    @elseif($user->role === 'perencana')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            Perencana
                                        </span>
                                    @elseif($user->role === 'operator')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Operator
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Super Admin
                                        </span>
                                    @endif
                                </td>

                                <!-- Wilayah Tugas -->
                                <td class="py-3.5 px-4">
                                    @if($user->role === 'operator' && $user->kecamatan)
                                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-medium text-[11px]">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            <span>Kec. {{ $user->kecamatan->nama }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px]">DINDIKPORA Induk</span>
                                    @endif
                                </td>

                                <!-- Didaftarkan -->
                                <td class="py-3.5 px-4 text-center text-slate-500 text-[11px]">
                                    {{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                @click="activeUser = {
                                                    id: {{ $user->id }},
                                                    name: '{{ addslashes($user->name) }}',
                                                    email: '{{ addslashes($user->email) }}',
                                                    role: '{{ $user->role }}',
                                                    nip: '{{ addslashes($user->nip ?? '') }}',
                                                    jabatan: '{{ addslashes($user->jabatan ?? '') }}',
                                                    kecamatan_id: '{{ $user->kecamatan_id ?? '' }}'
                                                }; editModal = true;"
                                                title="Edit Pengguna"
                                                class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Delete Button -->
                                        @if($user->id !== auth()->id() && $user->email !== 'admin@dindikpora.banjarnegarakab.go.id')
                                            <button type="button" 
                                                    @click="activeUser = {
                                                        id: {{ $user->id }},
                                                        name: '{{ addslashes($user->name) }}',
                                                        email: '{{ addslashes($user->email) }}',
                                                        role: '{{ $user->role }}'
                                                    }; deleteModal = true;"
                                                    title="Hapus Pengguna"
                                                    class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @else
                                            <span class="p-1.5 rounded-lg bg-slate-50 text-slate-300 cursor-not-allowed" title="Akun ini tidak dapat dihapus">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <p class="font-bold text-sm text-slate-700">Tidak ada data pengguna ditemukan</p>
                                    <p class="text-xs text-slate-500">Coba ubah kata kunci pencarian atau filter peran yang dipilih.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: ROLE MENU PERMISSIONS MATRIX -->
    <div x-show="currentTab === 'permissions'" class="space-y-6">
        
        <!-- Explanation Info Card -->
        <div class="p-5 rounded-2xl bg-blue-50/80 border border-blue-200/80 text-blue-900 text-xs leading-relaxed flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="p-2 rounded-xl bg-blue-600 text-white shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h4 class="font-bold text-sm text-blue-950">Aturan Konfigurasi Hak Akses Menu Role-Based</h4>
                    <p class="text-blue-800">
                        Pilih menu apa saja yang boleh diakses dan tampil pada sidebar navigasi untuk peran <strong>Kepala Dinas</strong>, <strong>Perencana</strong>, dan <strong>Operator</strong>.
                        Setiap perubahan akan langsung diterapkan pada tampilan sidebar dan rute akses middleware pengguna terkait.
                    </p>
                    <p class="text-[11px] text-blue-700 font-medium">
                        * Catatan: Akun dengan peran <strong>Super Admin</strong> secara otomatis memiliki izin akses penuh ke seluruh menu dan modul sistem.
                    </p>
                </div>
            </div>
        </div>

        <!-- Permissions Form -->
        <form method="POST" action="{{ route('users.permissions.update') }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-4 px-6 w-1/3">Modul & Nama Menu</th>
                                <th class="py-4 px-4 text-center w-40">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Kepala Dinas
                                    </div>
                                    <span class="block text-[10px] text-slate-400 mt-0.5 font-normal">Pimpinan</span>
                                </th>
                                <th class="py-4 px-4 text-center w-40">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        Perencana
                                    </div>
                                    <span class="block text-[10px] text-slate-400 mt-0.5 font-normal">Analis SAKIP</span>
                                </th>
                                <th class="py-4 px-4 text-center w-40">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Operator
                                    </div>
                                    <span class="block text-[10px] text-slate-400 mt-0.5 font-normal">Data Wilayah</span>
                                </th>
                                <th class="py-4 px-4 text-center w-36">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Super Admin
                                    </div>
                                    <span class="block text-[10px] text-slate-400 mt-0.5 font-normal">Bawaan Sistem</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($menuDefinitions as $groupKey => $group)
                                <!-- Group Header Row -->
                                <tr class="bg-slate-100/70">
                                    <td colspan="5" class="py-2.5 px-6 font-extrabold text-slate-700 uppercase tracking-wider text-[11px] flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                        <span>{{ $group['label'] }}</span>
                                    </td>
                                </tr>

                                @foreach($group['menus'] as $menuKey => $menuInfo)
                                    @php
                                        $pimpinanHas = in_array($menuKey, $rolePermissions['pimpinan'] ?? [], true);
                                        $perencanaHas = in_array($menuKey, $rolePermissions['perencana'] ?? [], true);
                                        $operatorHas = in_array($menuKey, $rolePermissions['operator'] ?? [], true);
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <!-- Menu Name & Desc -->
                                        <td class="py-3 px-6">
                                            <p class="font-bold text-slate-900 text-xs">{{ $menuInfo['name'] }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $menuInfo['desc'] }}</p>
                                        </td>

                                        <!-- Kepala Dinas Checkbox -->
                                        <td class="py-3 px-4 text-center">
                                            <label class="inline-flex items-center justify-center p-2 rounded-xl hover:bg-emerald-50 cursor-pointer transition">
                                                <input type="checkbox" 
                                                       name="permissions[pimpinan][]" 
                                                       value="{{ $menuKey }}"
                                                       {{ $pimpinanHas ? 'checked' : '' }}
                                                       class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 transition cursor-pointer">
                                            </label>
                                        </td>

                                        <!-- Perencana Checkbox -->
                                        <td class="py-3 px-4 text-center">
                                            <label class="inline-flex items-center justify-center p-2 rounded-xl hover:bg-purple-50 cursor-pointer transition">
                                                <input type="checkbox" 
                                                       name="permissions[perencana][]" 
                                                       value="{{ $menuKey }}"
                                                       {{ $perencanaHas ? 'checked' : '' }}
                                                       class="w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500 transition cursor-pointer">
                                            </label>
                                        </td>

                                        <!-- Operator Checkbox -->
                                        <td class="py-3 px-4 text-center">
                                            <label class="inline-flex items-center justify-center p-2 rounded-xl hover:bg-amber-50 cursor-pointer transition">
                                                <input type="checkbox" 
                                                       name="permissions[operator][]" 
                                                       value="{{ $menuKey }}"
                                                       {{ $operatorHas ? 'checked' : '' }}
                                                       class="w-4 h-4 text-amber-600 border-slate-300 rounded focus:ring-amber-500 transition cursor-pointer">
                                            </label>
                                        </td>

                                        <!-- Super Admin (Permanent) -->
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <svg class="w-3 h-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                Penuh
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Save Action -->
                <div class="p-6 bg-slate-50/80 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500">
                        Pastikan Anda menyimpan perubahan matriks agar aturan hak akses menu terbaru langsung berlaku di seluruh sistem.
                    </p>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/25 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span>Simpan Matriks Hak Akses Menu</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- MODAL: TAMBAH PENGGUNA BARU -->
    <div x-show="createModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="createModal = false">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-6 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto"
             @click.away="createModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Tambah Akun Pengguna Baru</h3>
                        <p class="text-[11px] text-slate-500">Pilih salah satu peran yang tersedia di sistem SIMAK</p>
                    </div>
                </div>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('users.store') }}" class="space-y-4" x-data="{ newRole: 'operator' }">
                @csrf
                
                <!-- Nama Lengkap -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           name="name" 
                           required 
                           placeholder="Contoh: Drs. Bambang Sutrisno, M.Pd" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Alamat Email <span class="text-rose-500">*</span></label>
                    <input type="email" 
                           name="email" 
                           required 
                           placeholder="Contoh: b.sutrisno@banjarnegarakab.go.id" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Password -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Kata Sandi (Password) <span class="text-rose-500">*</span></label>
                    <input type="password" 
                           name="password" 
                           required 
                           minlength="8" 
                           placeholder="Minimal 8 karakter kombinasi" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Role Selector -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Peran / Role Pengguna <span class="text-rose-500">*</span></label>
                    <select name="role" 
                            x-model="newRole" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition cursor-pointer">
                        <option value="pimpinan" @if($hasPimpinan) disabled class="text-slate-400 bg-slate-100" @endif>
                            Kepala Dinas (Pimpinan Eksekutif) @if($hasPimpinan) — [Sudah Terdaftar] @endif
                        </option>
                        <option value="perencana">Perencana (Analis AKIP Renstra)</option>
                        <option value="operator" selected>Operator (Pengelola Data Wilayah)</option>
                        <option value="admin">Super Admin (Administrator Sistem)</option>
                    </select>
                    @if($hasPimpinan)
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] leading-relaxed">
                            <svg class="w-4 h-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Akun Kepala Dinas saat ini telah terdaftar atas nama <strong>{{ $pimpinanUser->name ?? 'Kepala Dinas' }}</strong> &mdash; sistem dibatasi hanya untuk 1 akun Kepala Dinas aktif.</span>
                        </div>
                    @endif
                    <p class="text-[10px] text-slate-500">
                        Pilihan role yang tersedia: <strong>Kepala Dinas</strong>, <strong>Perencana</strong>, <strong>Operator</strong>, dan <strong>Super Admin</strong>.
                    </p>
                </div>

                <!-- Wilayah Kecamatan (Hanya jika role operator) -->
                <div class="space-y-1" x-show="newRole === 'operator'" x-transition>
                    <label class="block text-xs font-bold text-slate-700">Wilayah Tugas Korwilcam (Khusus Operator)</label>
                    <select name="kecamatan_id" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition cursor-pointer">
                        <option value="">-- DINDIKPORA Kabupaten / Lintas Wilayah --</option>
                        @foreach($kecamatanList as $kec)
                            <option value="{{ $kec->id }}">Kecamatan {{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- NIP -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700">NIP (Opsional)</label>
                        <input type="text" 
                               name="nip" 
                               placeholder="19800101 200501 1 002" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <!-- Jabatan -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700">Jabatan (Opsional)</label>
                        <input type="text" 
                               name="jabatan" 
                               placeholder="Contoh: Analis Kebijakan" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="createModal = false" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/25 transition">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT PENGGUNA -->
    <div x-show="editModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="editModal = false">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-6 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto"
             @click.away="editModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Perbarui Data Pengguna</h3>
                        <p class="text-[11px] text-slate-500" x-text="'ID #' + activeUser.id + ' - ' + activeUser.email"></p>
                    </div>
                </div>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" :action="'{{ url('manajemen-pengguna') }}/' + activeUser.id" class="space-y-4">
                @csrf
                @method('PUT')
                
                <!-- Nama Lengkap -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           name="name" 
                           x-model="activeUser.name"
                           required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Alamat Email <span class="text-rose-500">*</span></label>
                    <input type="email" 
                           name="email" 
                           x-model="activeUser.email"
                           required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Password (Optional on update) -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">Ganti Kata Sandi (Opsional)</label>
                    <input type="password" 
                           name="password" 
                           minlength="8" 
                           placeholder="Kosongkan bila tidak ingin mengubah sandi" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    <p class="text-[10px] text-slate-400">Minimal 8 karakter jika diisi.</p>
                </div>

                <!-- Role Selector -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Peran / Role Pengguna <span class="text-rose-500">*</span></label>
                    <select name="role" 
                            x-model="activeUser.role" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition cursor-pointer">
                        <option value="pimpinan" :disabled="activeUser.role !== 'pimpinan' && hasPimpinan">
                            Kepala Dinas (Pimpinan Eksekutif)
                        </option>
                        <option value="perencana">Perencana (Analis AKIP Renstra)</option>
                        <option value="operator">Operator (Pengelola Data Wilayah)</option>
                        <option value="admin">Super Admin (Administrator Sistem)</option>
                    </select>
                    <div x-show="activeUser.role !== 'pimpinan' && hasPimpinan" class="flex items-center gap-2 p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] leading-relaxed">
                        <svg class="w-4 h-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Role Kepala Dinas tidak dapat dipilih karena akun Kepala Dinas sudah terdaftar (hanya 1 akun yang diperbolehkan).</span>
                    </div>
                </div>

                <!-- Wilayah Kecamatan (Hanya jika role operator) -->
                <div class="space-y-1" x-show="activeUser.role === 'operator'" x-transition>
                    <label class="block text-xs font-bold text-slate-700">Wilayah Tugas Korwilcam (Khusus Operator)</label>
                    <select name="kecamatan_id" 
                            x-model="activeUser.kecamatan_id"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition cursor-pointer">
                        <option value="">-- DINDIKPORA Kabupaten / Lintas Wilayah --</option>
                        @foreach($kecamatanList as $kec)
                            <option value="{{ $kec->id }}">Kecamatan {{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- NIP -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700">NIP</label>
                        <input type="text" 
                               name="nip" 
                               x-model="activeUser.nip"
                               placeholder="19800101 200501 1 002" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <!-- Jabatan -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700">Jabatan</label>
                        <input type="text" 
                               name="jabatan" 
                               x-model="activeUser.jabatan"
                               placeholder="Contoh: Analis Kebijakan" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="editModal = false" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/25 transition">
                        Perbarui Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: HAPUS PENGGUNA -->
    <div x-show="deleteModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="deleteModal = false">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-5 shadow-2xl border border-slate-200 text-center"
             @click.away="deleteModal = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-slate-900">Konfirmasi Hapus Pengguna</h3>
                <p class="text-xs text-slate-500">
                    Apakah Anda yakin ingin menghapus akun <strong class="text-slate-800" x-text="activeUser.name"></strong> (<span class="text-slate-600 font-mono text-[11px]" x-text="activeUser.email"></span>)? Tindakan ini permanen.
                </p>
            </div>
            <form method="POST" :action="'{{ url('manajemen-pengguna') }}/' + activeUser.id" class="flex items-center justify-center gap-2.5 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" 
                        @click="deleteModal = false" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-500/25 transition">
                    Ya, Hapus Akun
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
