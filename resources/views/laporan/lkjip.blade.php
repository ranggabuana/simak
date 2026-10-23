<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LKjIP DINDIKPORA Banjarnegara Tahun {{ $tahun }} - Laporan Kinerja Instansi Pemerintah</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-banjarnegara.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11pt; color: black !important; }
            .page-break { page-break-before: always; }
            .print-shadow-none { box-shadow: none !important; border-color: #cbd5e1 !important; }
            .print-p-0 { padding: 0 !important; }
            @page {
                size: A4 portrait;
                margin: 20mm 15mm 20mm 15mm;
            }
        }
    </style>
</head>
<body class="antialiased py-6 sm:py-10">

    <!-- Top Action Bar (No Print) -->
    <div class="max-w-4xl mx-auto px-4 mb-6 no-print">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Kembali ke Dashboard">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-sm font-bold text-slate-900">Dokumen Resmi LKjIP DINDIKPORA Banjarnegara</h1>
                    <p class="text-[11px] text-slate-500">Laporan Akuntabilitas Kinerja Instansi Pemerintah (Tahun Anggaran {{ $tahun }})</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <form method="GET" class="flex items-center gap-2">
                    <select name="tahun" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800">
                        @foreach([2024, 2025, 2026, 2027, 2028, 2029] as $t)
                            <option value="{{ $t }}" {{ $tahun === $t ? 'selected' : '' }}>T.A {{ $t }}</option>
                        @endforeach
                    </select>
                </form>

                <button type="button" 
                        onclick="window.print()" 
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm flex items-center gap-2 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak / Simpan PDF (A4)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Printable Document Container -->
    <main class="max-w-4xl mx-auto bg-white p-8 sm:p-14 rounded-3xl border border-slate-200/90 shadow-lg print-shadow-none print-p-0 space-y-8 text-slate-900 leading-relaxed text-xs sm:text-sm">

        <!-- KOP SURAT RESMI DINAS -->
        <header class="border-b-4 border-double border-slate-900 pb-4 flex items-center justify-between gap-4">
            <div class="w-16 h-20 sm:w-20 sm:h-24 shrink-0 flex items-center justify-center">
                <img src="{{ asset('images/logo-banjarnegara.png') }}" alt="Logo Kab. Banjarnegara" class="w-full h-full object-contain">
            </div>
            <div class="flex-1 text-center space-y-0.5">
                <h2 class="text-sm sm:text-base font-extrabold uppercase tracking-wide">Pemerintah Kabupaten Banjarnegara</h2>
                <h1 class="text-lg sm:text-xl font-black uppercase tracking-tight text-slate-950">Dinas Pendidikan, Kepemudaan dan Olahraga</h1>
                <p class="text-[11px] sm:text-xs text-slate-600">
                    Jl. Mayjen Panjaitan No. 59, Banjarnegara, Jawa Tengah 53414 | Telp: (0286) 591244
                </p>
                <p class="text-[10px] sm:text-[11px] text-slate-500 font-mono">
                    Laman: https://dindikpora.banjarnegarakab.go.id &bull; Pos-el: dindikpora@banjarnegarakab.go.id
                </p>
            </div>
            <div class="w-16 sm:w-20 shrink-0 hidden sm:block"></div>
        </header>

        <!-- JUDUL LAPORAN -->
        <div class="text-center pt-2 pb-4 space-y-1.5">
            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase bg-blue-50 text-blue-800 border border-blue-200 tracking-wider">
                Dokumen SAKIP / LKjIP Resmi
            </span>
            <h3 class="text-base sm:text-lg font-black uppercase text-slate-900 pt-1">
                Laporan Kinerja Instansi Pemerintah (LKjIP)
            </h3>
            <p class="text-xs font-bold text-slate-600">
                Tahun Anggaran {{ $tahun }} &bull; Periode Evaluasi Renstra 2025–2029
            </p>
        </div>

        <!-- BAB I: RINGKASAN EKSEKUTIF -->
        <section class="space-y-3">
            <h4 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-blue-900 border-b border-blue-200 pb-1">
                I. Ringkasan Eksekutif & Gambaran Umum
            </h4>
            <p class="text-justify text-slate-700 leading-relaxed">
                Laporan Kinerja Instansi Pemerintah (LKjIP) Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Banjarnegara Tahun {{ $tahun }} disusun sebagai bentuk pertanggungjawaban pelaksanaan tugas pokok, fungsi, dan kewenangan dalam penyelenggaraan urusan pemerintahan wajib bidang pendidikan. Dokumen ini merefleksikan akuntabilitas kinerja sesuai Peraturan Menteri Pendayagunaan Aparatur Negara dan Reformasi Birokrasi (PermenPAN-RB) Nomor 88 dan 89 Tahun 2021 tentang Petunjuk Teknis Evaluasi SAKIP.
            </p>
            <p class="text-justify text-slate-700 leading-relaxed">
                Pada tahun {{ $tahun }}, total penduduk usia sekolah di Kabupaten Banjarnegara tercatat sebanyak <strong>{{ number_format($kabupaten['populasi']['total']) }} jiwa</strong> yang tersebar di 20 wilayah kecamatan. Capaian Angka Partisipasi Kasar (APK) SD/MI mencapai <strong>{{ number_format($kabupaten['apk']['sd'], 2) }}%</strong> dan APK SMP/MTs mencapai <strong>{{ number_format($kabupaten['apk']['smp'], 2) }}%</strong>, didukung oleh <strong>{{ number_format($kabupaten['guru']['sertifikasi']) }} guru bersertifikat pendidik</strong> ({{ number_format($kabupaten['guru']['persentase'], 1) }}%).
            </p>
        </section>

        <!-- BAB II: CAPAIAN INDIKATOR KINERJA MAKRO -->
        <section class="space-y-4">
            <h4 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-blue-900 border-b border-blue-200 pb-1">
                II. Capaian Indikator Kinerja Makro Pendidikan Tahun {{ $tahun }}
            </h4>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-500 uppercase block">APK SD/MI</span>
                    <strong class="text-base sm:text-lg font-black text-blue-700">{{ number_format($kabupaten['apk']['sd'], 2) }}%</strong>
                    <span class="text-[10px] text-slate-500 block">Target: {{ $target?->target_apk_sd ?? 101.8 }}%</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-500 uppercase block">APM SD/MI</span>
                    <strong class="text-base sm:text-lg font-black text-blue-700">{{ number_format($kabupaten['apm']['sd'], 2) }}%</strong>
                    <span class="text-[10px] text-slate-500 block">Target: {{ $target?->target_apm_sd ?? 98.4 }}%</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-500 uppercase block">APK SMP/MTs</span>
                    <strong class="text-base sm:text-lg font-black text-indigo-700">{{ number_format($kabupaten['apk']['smp'], 2) }}%</strong>
                    <span class="text-[10px] text-slate-500 block">Target: {{ $target?->target_apk_smp ?? 97.5 }}%</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-500 uppercase block">APM SMP/MTs</span>
                    <strong class="text-base sm:text-lg font-black text-indigo-700">{{ number_format($kabupaten['apm']['smp'], 2) }}%</strong>
                    <span class="text-[10px] text-slate-500 block">Target: {{ $target?->target_apm_smp ?? 86.8 }}%</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3.5 bg-purple-50/50 rounded-xl border border-purple-200 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-purple-950 block">Guru Bersertifikat Pendidik</span>
                        <span class="text-[11px] text-purple-800">{{ number_format($kabupaten['guru']['sertifikasi']) }} dari {{ number_format($kabupaten['guru']['total']) }} guru aktif</span>
                    </div>
                    <strong class="text-base font-extrabold text-purple-700">{{ number_format($kabupaten['guru']['persentase'], 1) }}%</strong>
                </div>
                <div class="p-3.5 bg-amber-50/50 rounded-xl border border-amber-200 flex justify-between items-center">
                    <div>
                        <span class="font-bold text-amber-950 block">PAUD Terakreditasi Minimal B</span>
                        <span class="text-[11px] text-amber-800">{{ $kabupaten['paud_akreditasi']['min_b'] }} dari {{ $kabupaten['paud_akreditasi']['total'] }} lembaga PAUD/TK</span>
                    </div>
                    <strong class="text-base font-extrabold text-amber-700">{{ number_format($kabupaten['paud_akreditasi']['persentase'], 1) }}%</strong>
                </div>
            </div>
        </section>

        <!-- BAB III: MATRIKS KOMPARASI RENSTRA VS REALISASI -->
        <section class="space-y-3 page-break">
            <h4 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-blue-900 border-b border-blue-200 pb-1">
                III. Matriks Analisis Kesenjangan (Gap Analysis) Sasaran Renstra
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-300">
                    <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="p-2.5 border border-slate-300">No</th>
                            <th class="p-2.5 border border-slate-300">Indikator Kinerja Utama (IKU)</th>
                            <th class="p-2.5 border border-slate-300 text-center">Target Renstra</th>
                            <th class="p-2.5 border border-slate-300 text-center">Realisasi Riil</th>
                            <th class="p-2.5 border border-slate-300 text-center">Deviasi (Gap)</th>
                            <th class="p-2.5 border border-slate-300 text-center">Status SAKIP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrix as $idx => $m)
                        @php
                            $gap = round($m['realisasi'] - $m['target'], 2);
                            $isAchieved = $gap >= 0;
                        @endphp
                        <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                            <td class="p-2 border border-slate-300 text-center font-bold">{{ $idx + 1 }}</td>
                            <td class="p-2 border border-slate-300 font-medium text-slate-900">{{ $m['nama'] }}</td>
                            <td class="p-2 border border-slate-300 text-center font-mono">{{ $m['target'] }}{{ $m['satuan'] }}</td>
                            <td class="p-2 border border-slate-300 text-center font-bold font-mono">{{ $m['realisasi'] }}{{ $m['satuan'] }}</td>
                            <td class="p-2 border border-slate-300 text-center font-bold font-mono {{ $isAchieved ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $gap > 0 ? '+'.$gap : $gap }}{{ $m['satuan'] }}
                            </td>
                            <td class="p-2 border border-slate-300 text-center font-bold text-[10px]">
                                {{ $isAchieved ? 'TERCAPAI' : 'PERLU ATENSI' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- BAB IV: PERUMUSAN EVALUASI & REKOMENDASI -->
        <section class="space-y-4">
            <h4 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-blue-900 border-b border-blue-200 pb-1">
                IV. Catatan Evaluasi & Rekomendasi Kebijakan Dinas
            </h4>

            <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs">
                <div>
                    <strong class="text-slate-900 block font-bold mb-1">A. Catatan Evaluasi Ketercapaian Kinerja:</strong>
                    <p class="text-slate-700 leading-relaxed text-justify">
                        {{ $realisasi?->catatan_evaluasi ?: 'Capaian kinerja pendidikan Kabupaten Banjarnegara menunjukkan tren positif pada indikator pemerataan akses pendidikan dasar SD/SMP. Namun demikian, akselerasi sertifikasi pendidik dan peningkatan akreditasi kelembagaan PAUD masih memerlukan optimalisasi anggaran pembinaan berkelanjutan.' }}
                    </p>
                </div>
                <div class="pt-2 border-t border-slate-200">
                    <strong class="text-slate-900 block font-bold mb-1">B. Perumusan Rekomendasi Kebijakan Tindak Lanjut:</strong>
                    <p class="text-slate-700 leading-relaxed text-justify">
                        {{ $realisasi?->rekomendasi ?: '1. Memperkuat intervensi beasiswa bagi siswa prasejahtera di wilayah pegunungan (Batur, Pejawaran, Wanayasa) untuk menekan angka anak putus sekolah dini.\n2. Mengadakan bimbingan teknis intensif pemenuhan instrumen akreditasi bagi 200 satuan KB dan TK bersama BAN-PDM.\n3. Memfasilitasi pendampingan seleksi PPG bagi seluruh guru honorer terdata di Dapodik.' }}
                    </p>
                </div>
            </div>
        </section>

        <!-- BAB V: REKAPITULASI PRESTASI SISWA -->
        <section class="space-y-3">
            <h4 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-blue-900 border-b border-blue-200 pb-1">
                V. Prestasi Peserta Didik Unggulan (Tujuan 2)
            </h4>
            <p class="text-slate-600 text-[11px]">
                Daftar perolehan kejuaraan dan medali tingkat Kabupaten, Provinsi, hingga Nasional tahun {{ $tahun }}:
            </p>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[11px] border border-slate-300">
                    <thead class="bg-slate-100 font-bold uppercase text-[9px]">
                        <tr>
                            <th class="p-2 border border-slate-300">Nama Siswa</th>
                            <th class="p-2 border border-slate-300">Satuan Pendidikan</th>
                            <th class="p-2 border border-slate-300">Kejuaraan</th>
                            <th class="p-2 border border-slate-300 text-center">Tingkat</th>
                            <th class="p-2 border border-slate-300 text-center">Hasil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPrestasi as $pres)
                        <tr>
                            <td class="p-2 border border-slate-300 font-bold">{{ $pres->nama_siswa }}</td>
                            <td class="p-2 border border-slate-300">{{ $pres->satuanPendidikan->nama_sekolah ?? '-' }}</td>
                            <td class="p-2 border border-slate-300">{{ $pres->nama_kejuaraan }}</td>
                            <td class="p-2 border border-slate-300 text-center font-bold text-blue-700">{{ $pres->tingkat }}</td>
                            <td class="p-2 border border-slate-300 text-center font-extrabold text-emerald-700">{{ $pres->peringkat }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-slate-400">Belum ada data kejuaraan tercatat untuk tahun ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- LEMBAR PENGESAHAN KEPALA DINAS -->
        <footer class="pt-8 text-xs flex justify-end">
            <div class="text-center w-72 space-y-1">
                <p>Banjarnegara, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-slate-800">Mengetahui & Menyetujui,</p>
                <p class="font-bold uppercase text-slate-900 leading-tight">
                    Kepala Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Banjarnegara
                </p>
                
                <!-- Ruang Tanda Tangan -->
                <div class="h-20 flex items-center justify-center">
                    <span class="text-[10px] text-slate-300 font-mono italic">[ Tanda Tangan & Cap Dinas ]</span>
                </div>

                <p class="font-extrabold text-slate-950 underline text-sm">
                    {{ $pimpinan->name }}
                </p>
                <p class="text-[11px] text-slate-600 font-mono">
                    NIP. {{ $pimpinan->nip }}
                </p>
                <p class="text-[10px] text-slate-500 font-bold uppercase">
                    Pembina Utama Muda (IV/c)
                </p>
            </div>
        </footer>

    </main>

</body>
</html>
