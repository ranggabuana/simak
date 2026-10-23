<?php

namespace App\Services;

use App\Models\GuruPendidik;
use App\Models\PartisipasiSiswa;
use App\Models\PendudukUsiaSekolah;
use App\Models\PrestasiSiswa;
use App\Models\RealisasiKinerja;
use App\Models\SatuanPendidikan;
use App\Models\TargetRenstra;
use App\Models\WilayahKecamatan;
use Illuminate\Support\Collection;

class KinerjaService
{
    /**
     * Hitung indikator kinerja makro tingkat Kabupaten Banjarnegara
     *
     * @return array<string, mixed>
     */
    public function hitungKinerjaKabupaten(int $tahun = 2025): array
    {
        // 1. Total Penduduk Usia Sekolah
        $penduduk = PendudukUsiaSekolah::where('tahun', $tahun)->get();

        $pop5_6 = $penduduk->sum('usia_5_6_total');
        $pop7_12 = $penduduk->sum('usia_7_12_total');
        $pop13_15 = $penduduk->sum('usia_13_15_total');
        $pop16_18 = $penduduk->sum('usia_16_18_total');

        // 2. Partisipasi Siswa
        $partisipasi = PartisipasiSiswa::where('tahun', $tahun)->get();

        // Siswa per Jenjang (semua usia)
        $siswaPaudAll = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('total_siswa_semua_usia');
        $siswaSdAll = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('total_siswa_semua_usia');
        $siswaSmpAll = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('total_siswa_semua_usia');
        $siswaSmaAll = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('total_siswa_semua_usia');

        // Siswa sesuai usia resmi di jenjangnya (untuk APM)
        $siswaPaudSesuai = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai');
        $siswaSdSesuai = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai');
        $siswaSmpSesuai = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai');
        $siswaSmaSesuai = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai');

        // Siswa usia jenjang tertentu di SEMUA jenjang (untuk APS)
        $siswaUsia5_6Semua = $partisipasi->sum('siswa_usia_5_6');
        $siswaUsia7_12Semua = $partisipasi->sum('siswa_usia_7_12');
        $siswaUsia13_15Semua = $partisipasi->sum('siswa_usia_13_15');
        $siswaUsia16_18Semua = $partisipasi->sum('siswa_usia_16_18');

        // Rumus 1: APK = (Siswa jenjang tertentu semua usia / Populasi usia jenjang) * 100
        $apkPaud = $pop5_6 > 0 ? round(($siswaPaudAll / $pop5_6) * 100, 2) : 0;
        $apkSd = $pop7_12 > 0 ? round(($siswaSdAll / $pop7_12) * 100, 2) : 0;
        $apkSmp = $pop13_15 > 0 ? round(($siswaSmpAll / $pop13_15) * 100, 2) : 0;
        $apkSma = $pop16_18 > 0 ? round(($siswaSmaAll / $pop16_18) * 100, 2) : 0;

        // Rumus 2: APM = (Siswa usia tertentu di jenjangnya / Populasi usia jenjang) * 100
        $apmPaud = $pop5_6 > 0 ? min(100.0, round(($siswaPaudSesuai / $pop5_6) * 100, 2)) : 0;
        $apmSd = $pop7_12 > 0 ? min(100.0, round(($siswaSdSesuai / $pop7_12) * 100, 2)) : 0;
        $apmSmp = $pop13_15 > 0 ? min(100.0, round(($siswaSmpSesuai / $pop13_15) * 100, 2)) : 0;
        $apmSma = $pop16_18 > 0 ? min(100.0, round(($siswaSmaSesuai / $pop16_18) * 100, 2)) : 0;

        // Rumus 3: APS = (Siswa usia tertentu di SEMUA jenjang / Populasi usia jenjang) * 100
        $apsPaud = $pop5_6 > 0 ? min(100.0, round(($siswaUsia5_6Semua / $pop5_6) * 100, 2)) : 0;
        $apsSd = $pop7_12 > 0 ? min(100.0, round(($siswaUsia7_12Semua / $pop7_12) * 100, 2)) : 0;
        $apsSmp = $pop13_15 > 0 ? min(100.0, round(($siswaUsia13_15Semua / $pop13_15) * 100, 2)) : 0;
        $apsSma = $pop16_18 > 0 ? min(100.0, round(($siswaUsia16_18Semua / $pop16_18) * 100, 2)) : 0;

        // 4. Guru Bersertifikat Pendidik
        $totalGuru = GuruPendidik::count();
        $guruSertif = GuruPendidik::where('sudah_sertifikasi', true)->count();
        $persenGuruSertif = $totalGuru > 0 ? round(($guruSertif / $totalGuru) * 100, 2) : 0;

        // 5. PAUD Terakreditasi Minimal B
        $totalPaud = SatuanPendidikan::whereIn('jenjang', ['PAUD', 'TK'])->count();
        $paudMinB = SatuanPendidikan::whereIn('jenjang', ['PAUD', 'TK'])->whereIn('akreditasi', ['A', 'B'])->count();
        $persenPaudMinB = $totalPaud > 0 ? round(($paudMinB / $totalPaud) * 100, 2) : 0;

        // 6. Target Renstra & Realisasi Makro
        $target = TargetRenstra::where('tahun', $tahun)->first();
        $realisasi = RealisasiKinerja::where('tahun', $tahun)->first();
        $realisasiPrev = RealisasiKinerja::where('tahun', $tahun - 1)->first();

        // 7. Total Prestasi Siswa
        $prestasiCount = PrestasiSiswa::where('tahun', $tahun)->count();

        return [
            'tahun' => $tahun,
            'populasi' => [
                'usia_5_6' => $pop5_6,
                'usia_7_12' => $pop7_12,
                'usia_13_15' => $pop13_15,
                'usia_16_18' => $pop16_18,
                'total' => $penduduk->sum('total_penduduk'),
            ],
            'siswa' => [
                'paud_all' => $siswaPaudAll,
                'sd_all' => $siswaSdAll,
                'smp_all' => $siswaSmpAll,
                'sma_all' => $siswaSmaAll,
                'total_all' => $siswaPaudAll + $siswaSdAll + $siswaSmpAll + $siswaSmaAll,
            ],
            'apk' => [
                'paud' => $apkPaud,
                'sd' => $apkSd,
                'smp' => $apkSmp,
                'sma' => $apkSma,
            ],
            'apm' => [
                'paud' => $apmPaud,
                'sd' => $apmSd,
                'smp' => $apmSmp,
                'sma' => $apmSma,
            ],
            'aps' => [
                'paud' => $apsPaud,
                'sd' => $apsSd,
                'smp' => $apsSmp,
                'sma' => $apsSma,
            ],
            'guru' => [
                'total' => $totalGuru,
                'sertifikasi' => $guruSertif,
                'persentase' => $persenGuruSertif,
            ],
            'paud_akreditasi' => [
                'total' => $totalPaud,
                'min_b' => $paudMinB,
                'persentase' => $persenPaudMinB,
            ],
            'target' => $target,
            'realisasi' => $realisasi,
            'realisasi_prev' => $realisasiPrev,
            'prestasi_count' => $prestasiCount,
            'total_sekolah' => SatuanPendidikan::count(),
            'total_kecamatan' => WilayahKecamatan::count(),
        ];
    }

    /**
     * Hitung indikator kinerja Basic Wilayah (Per Kecamatan)
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function hitungKinerjaPerKecamatan(int $tahun = 2025): Collection
    {
        $kecamatanList = WilayahKecamatan::with([
            'pendudukUsiaSekolah' => fn ($q) => $q->where('tahun', $tahun),
            'satuanPendidikan',
            'partisipasiSiswa' => fn ($q) => $q->where('tahun', $tahun),
            'guruPendidik',
            'prestasiSiswa' => fn ($q) => $q->where('tahun', $tahun),
        ])->get();

        return $kecamatanList->map(function ($kec) {
            $demografi = $kec->pendudukUsiaSekolah->first();
            $pop5_6 = $demografi?->usia_5_6_total ?? 0;
            $pop7_12 = $demografi?->usia_7_12_total ?? 0;
            $pop13_15 = $demografi?->usia_13_15_total ?? 0;
            $pop16_18 = $demografi?->usia_16_18_total ?? 0;

            $partisipasi = $kec->partisipasiSiswa;

            // Siswa per jenjang
            $siswaPaud = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('total_siswa_semua_usia');
            $siswaSd = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('total_siswa_semua_usia');
            $siswaSmp = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('total_siswa_semua_usia');
            $siswaSma = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('total_siswa_semua_usia');

            $siswaPaudSesuai = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai');
            $siswaSdSesuai = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai');
            $siswaSmpSesuai = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai');
            $siswaSmaSesuai = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai');

            $siswaUsia5_6 = $partisipasi->sum('siswa_usia_5_6');
            $siswaUsia7_12 = $partisipasi->sum('siswa_usia_7_12');
            $siswaUsia13_15 = $partisipasi->sum('siswa_usia_13_15');
            $siswaUsia16_18 = $partisipasi->sum('siswa_usia_16_18');

            // Hitung APK, APM, APS
            $apkPaud = $pop5_6 > 0 ? round(($siswaPaud / $pop5_6) * 100, 2) : 0;
            $apkSd = $pop7_12 > 0 ? round(($siswaSd / $pop7_12) * 100, 2) : 0;
            $apkSmp = $pop13_15 > 0 ? round(($siswaSmp / $pop13_15) * 100, 2) : 0;
            $apkSma = $pop16_18 > 0 ? round(($siswaSma / $pop16_18) * 100, 2) : 0;

            $apmPaud = $pop5_6 > 0 ? min(100.0, round(($siswaPaudSesuai / $pop5_6) * 100, 2)) : 0;
            $apmSd = $pop7_12 > 0 ? min(100.0, round(($siswaSdSesuai / $pop7_12) * 100, 2)) : 0;
            $apmSmp = $pop13_15 > 0 ? min(100.0, round(($siswaSmpSesuai / $pop13_15) * 100, 2)) : 0;
            $apmSma = $pop16_18 > 0 ? min(100.0, round(($siswaSmaSesuai / $pop16_18) * 100, 2)) : 0;

            $apsPaud = $pop5_6 > 0 ? min(100.0, round(($siswaUsia5_6 / $pop5_6) * 100, 2)) : 0;
            $apsSd = $pop7_12 > 0 ? min(100.0, round(($siswaUsia7_12 / $pop7_12) * 100, 2)) : 0;
            $apsSmp = $pop13_15 > 0 ? min(100.0, round(($siswaUsia13_15 / $pop13_15) * 100, 2)) : 0;
            $apsSma = $pop16_18 > 0 ? min(100.0, round(($siswaUsia16_18 / $pop16_18) * 100, 2)) : 0;

            // Guru
            $totalGuru = $kec->guruPendidik->count();
            $guruSertif = $kec->guruPendidik->where('sudah_sertifikasi', true)->count();
            $persenGuruSertif = $totalGuru > 0 ? round(($guruSertif / $totalGuru) * 100, 2) : 0;

            // PAUD Min B
            $paudKec = $kec->satuanPendidikan->whereIn('jenjang', ['PAUD', 'TK']);
            $totalPaud = $paudKec->count();
            $paudMinB = $paudKec->whereIn('akreditasi', ['A', 'B'])->count();
            $persenPaudMinB = $totalPaud > 0 ? round(($paudMinB / $totalPaud) * 100, 2) : 0;

            return [
                'id' => $kec->id,
                'kode' => $kec->kode,
                'nama' => $kec->nama,
                'ibukota' => $kec->ibukota,
                'latitude' => $kec->latitude,
                'longitude' => $kec->longitude,
                'populasi' => [
                    'usia_5_6' => $pop5_6,
                    'usia_7_12' => $pop7_12,
                    'usia_13_15' => $pop13_15,
                    'usia_16_18' => $pop16_18,
                    'total' => $demografi?->total_penduduk ?? 0,
                ],
                'sekolah_count' => $kec->satuanPendidikan->count(),
                'apk' => ['paud' => $apkPaud, 'sd' => $apkSd, 'smp' => $apkSmp, 'sma' => $apkSma],
                'apm' => ['paud' => $apmPaud, 'sd' => $apmSd, 'smp' => $apmSmp, 'sma' => $apmSma],
                'aps' => ['paud' => $apsPaud, 'sd' => $apsSd, 'smp' => $apsSmp, 'sma' => $apsSma],
                'guru' => [
                    'total' => $totalGuru,
                    'sertifikasi' => $guruSertif,
                    'persentase' => $persenGuruSertif,
                ],
                'paud_akreditasi' => [
                    'total' => $totalPaud,
                    'min_b' => $paudMinB,
                    'persentase' => $persenPaudMinB,
                ],
                'prestasi_count' => $kec->prestasiSiswa->count(),
            ];
        });
    }

    /**
     * Hitung indikator Basic Sekolah
     */
    public function hitungKinerjaSekolah(?int $kecamatanId = null, ?string $jenjang = null, int $tahun = 2025)
    {
        $query = SatuanPendidikan::with([
            'kecamatan',
            'partisipasiSiswa' => fn ($q) => $q->where('tahun', $tahun),
            'guruPendidik',
            'prestasiSiswa',
        ]);

        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        if ($jenjang) {
            $query->where('jenjang', $jenjang);
        }

        return $query->get();
    }

    /**
     * Hitung Sistem Peringatan Dini (Early Warning System / EWS Kinerja)
     *
     * @return array<string, mixed>
     */
    public function hitungEarlyWarningSystem(int $tahun = 2025): array
    {
        $kabupaten = $this->hitungKinerjaKabupaten($tahun);
        $kecamatanList = $this->hitungKinerjaPerKecamatan($tahun);
        $target = TargetRenstra::where('tahun', $tahun)->first();

        $items = [];
        $kritisCount = 0;
        $waspadaCount = 0;
        $optimalCount = 0;

        if ($target) {
            $metrics = [
                [
                    'key' => 'apk_sd',
                    'nama' => 'APK SD / MI',
                    'target' => (float) $target->target_apk_sd,
                    'realisasi' => (float) $kabupaten['apk']['sd'],
                    'satuan' => '%',
                    'intervensi' => 'Jaring anak usia non-standar dan perkuat validasi rombel sekolah perbatasan.',
                ],
                [
                    'key' => 'apm_sd',
                    'nama' => 'APM SD / MI (Usia Resmi 7-12)',
                    'target' => (float) $target->target_apm_sd,
                    'realisasi' => (float) $kabupaten['apm']['sd'],
                    'satuan' => '%',
                    'intervensi' => 'Pantau anak usia 7 tahun wajib masuk SD Negeri/Swasta tanpa syarat seleksi berbelit.',
                ],
                [
                    'key' => 'apk_smp',
                    'nama' => 'APK SMP / MTs',
                    'target' => (float) $target->target_apk_smp,
                    'realisasi' => (float) $kabupaten['apk']['smp'],
                    'satuan' => '%',
                    'intervensi' => 'Optimalkan kuota SPMB/PPDB jalur zonasi dan afirmasi di SMP pinggiran.',
                ],
                [
                    'key' => 'apm_smp',
                    'nama' => 'APM SMP / MTs (Usia Resmi 13-15)',
                    'target' => (float) $target->target_apm_smp,
                    'realisasi' => (float) $kabupaten['apm']['smp'],
                    'satuan' => '%',
                    'intervensi' => 'Akselerasi program tuntas transisi lulusan SD ke SMP untuk cegah putus sekolah dini.',
                ],
                [
                    'key' => 'guru',
                    'nama' => 'Persentase Guru Bersertifikat Pendidik',
                    'target' => (float) $target->target_guru_sertifikasi,
                    'realisasi' => (float) $kabupaten['guru']['persentase'],
                    'satuan' => '%',
                    'intervensi' => 'Dorong keikutsertaan seleksi PPG Guru Tertentu bagi guru ASN dan honorer terdaftar.',
                ],
                [
                    'key' => 'paud',
                    'nama' => 'Satuan PAUD Terakreditasi Minimal B',
                    'target' => (float) $target->target_paud_akreditasi_b,
                    'realisasi' => (float) $kabupaten['paud_akreditasi']['persentase'],
                    'satuan' => '%',
                    'intervensi' => 'Bimtek pemenuhan 8 Standar Nasional Pendidikan bagi KB/TK bersama asesor BAN-PDM.',
                ],
            ];

            foreach ($metrics as $m) {
                $gap = round($m['realisasi'] - $m['target'], 2);
                if ($gap >= 0) {
                    $status = 'optimal';
                    $optimalCount++;
                } elseif ($gap >= -3.0) {
                    $status = 'waspada';
                    $waspadaCount++;
                } else {
                    $status = 'kritis';
                    $kritisCount++;
                }

                $items[] = array_merge($m, [
                    'gap' => $gap,
                    'status' => $status,
                ]);
            }
        }

        // Top At-Risk Kecamatan
        $sortedByApmSmp = $kecamatanList->sortBy('apm.smp')->take(3)->values();
        $sortedByGuru = $kecamatanList->sortBy('guru.persentase')->take(3)->values();
        $sortedByPaud = $kecamatanList->sortBy('paud_akreditasi.persentase')->take(3)->values();

        return [
            'tahun' => $tahun,
            'summary' => [
                'total_indikator' => count($items),
                'kritis' => $kritisCount,
                'waspada' => $waspadaCount,
                'optimal' => $optimalCount,
                'indeks_kesehatan_kinerja' => count($items) > 0 ? round(($optimalCount / count($items)) * 100, 1) : 100,
            ],
            'items' => $items,
            'wilayah_atensi' => [
                'apm_smp' => $sortedByApmSmp,
                'guru' => $sortedByGuru,
                'paud' => $sortedByPaud,
            ],
        ];
    }
}
