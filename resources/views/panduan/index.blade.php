@extends('layouts.app', ['title' => 'Panduan & Glosarium SAKIP'])

@section('content')
<div class="space-y-8">

    <!-- Header Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-blue-50/80 via-slate-50 to-white text-slate-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-xs border border-blue-200">
        <div class="space-y-1.5 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800 tracking-wider">Pusat Pengetahuan SAKIP</span>
                <h2 class="text-lg font-bold text-slate-900">Panduan Operasional & Glosarium Indikator Pendidikan</h2>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kamus istilah resmi, formula matematis Kemendikbud, alur integrasi data sesuai Flowchart DINDIKPORA, serta dasar hukum evaluasi akuntabilitas kinerja pemerintah daerah.
            </p>
        </div>
    </div>

    <!-- 3 Core Sections Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- 1. Glosarium Rumus APK, APM, APS -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 font-bold text-xs">01</span>
                <h3 class="text-sm font-bold text-slate-900">Formula Partisipasi Sekolah</h3>
            </div>
            
            <div class="space-y-3.5 text-xs text-slate-600">
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                    <strong class="text-blue-900 block">APK (Angka Partisipasi Kasar)</strong>
                    <p class="font-mono text-[11px] bg-white p-2 rounded-lg border border-slate-200 text-slate-800">
                        APK = (Siswa Jenjang Tertentu / Penduduk Usia Jenjang) &times; 100
                    </p>
                    <p class="text-[10px] text-slate-500">Mencakup seluruh siswa pada jenjang tertentu tanpa memandang batas usia (bisa &gt; 100%).</p>
                </div>

                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                    <strong class="text-indigo-900 block">APM (Angka Partisipasi Murni)</strong>
                    <p class="font-mono text-[11px] bg-white p-2 rounded-lg border border-slate-200 text-slate-800">
                        APM = (Siswa Usia Resmi di Jenjangnya / Penduduk Usia Jenjang) &times; 100
                    </p>
                    <p class="text-[10px] text-slate-500">Hanya mengukur anak yang bersekolah tepat pada usia resminya (maksimal 100%).</p>
                </div>

                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                    <strong class="text-slate-900 block">APS (Angka Partisipasi Sekolah)</strong>
                    <p class="font-mono text-[11px] bg-white p-2 rounded-lg border border-slate-200 text-slate-800">
                        APS = (Anak Usia Tertentu yang Bersekolah / Penduduk Usia Tersebut) &times; 100
                    </p>
                    <p class="text-[10px] text-slate-500">Mengukur daya serap anak usia sekolah di jenjang apapun (termasuk paket kesetaraan).</p>
                </div>
            </div>
        </div>

        <!-- 2. Alur Kerja Sesuai Flowchart SAKIP -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 font-bold text-xs">02</span>
                <h3 class="text-sm font-bold text-slate-900">Alur Data Flowchart SIMAK</h3>
            </div>
            
            <div class="space-y-3 text-xs text-slate-600">
                <div class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">1</span>
                    <div>
                        <strong class="text-slate-900 block">Input Data Tujuan 1 (Demografi)</strong>
                        <p class="text-[11px] text-slate-500">Data kependudukan 5–6 th, 7–12 th, 13–15 th, 16–18 th sebagai penyebut rumus makro.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">2</span>
                    <div>
                        <strong class="text-slate-900 block">Input Data Satuan Pendidikan & Dapodik</strong>
                        <p class="text-[11px] text-slate-500">Jumlah peserta didik per sekolah dan data tenaga pendidik bersertifikat sebagai pembilang.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">3</span>
                    <div>
                        <strong class="text-slate-900 block">Kalkulasi Otomatis Indikator Kinerja</strong>
                        <p class="text-[11px] text-slate-500">Sistem mengagregasi APK, APM, APS, Mutu Guru, dan Akreditasi PAUD tingkat kecamatan & kabupaten.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">4</span>
                    <div>
                        <strong class="text-slate-900 block">Gap Analysis & Rekomendasi SAKIP</strong>
                        <p class="text-[11px] text-slate-500">Komparasi capaian riil terhadap target Renstra 2025–2029 untuk perumusan kebijakan dinas.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Landasan Hukum & Regulasi -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600 font-bold text-xs">03</span>
                <h3 class="text-sm font-bold text-slate-900">Dasar Hukum & Regulasi</h3>
            </div>
            
            <div class="space-y-3 text-xs text-slate-600">
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <strong class="text-slate-900 block text-[11px]">PermenPAN-RB No. 88 & 89 Tahun 2021</strong>
                    <p class="text-[10px] text-slate-500 mt-1">Tentang Evaluasi Akuntabilitas Kinerja Instansi Pemerintah (AKIP) dan Penyelenggaraan Sistem SAKIP.</p>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <strong class="text-slate-900 block text-[11px]">Permendikbudristek No. 32 Tahun 2022</strong>
                    <p class="text-[10px] text-slate-500 mt-1">Tentang Standar Teknis Pelayanan Minimal (SPM) Pendidikan di Kabupaten/Kota.</p>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <strong class="text-slate-900 block text-[11px]">Perda Rencana Strategis (Renstra) Banjarnegara</strong>
                    <p class="text-[10px] text-slate-500 mt-1">Renstra DINDIKPORA Periode 2025–2029 penetapan target IKU dan IKK tahunan.</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
