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
    public function hitungKinerjaKabupaten(int $tahun = 2025, ?string $gender = null): array
    {
        // 1. Total Penduduk Usia Sekolah
        $penduduk = PendudukUsiaSekolah::where('tahun', $tahun)->get();

        $pop5_6 = $penduduk->sum('usia_5_6_total');
        $pop5_6_l = $penduduk->sum('usia_5_6_l');
        $pop5_6_p = $penduduk->sum('usia_5_6_p');

        $pop7_12 = $penduduk->sum('usia_7_12_total');
        $pop7_12_l = $penduduk->sum('usia_7_12_l');
        $pop7_12_p = $penduduk->sum('usia_7_12_p');

        $pop13_15 = $penduduk->sum('usia_13_15_total');
        $pop13_15_l = $penduduk->sum('usia_13_15_l');
        $pop13_15_p = $penduduk->sum('usia_13_15_p');

        $pop16_18 = $penduduk->sum('usia_16_18_total');
        $pop16_18_l = $penduduk->sum('usia_16_18_l');
        $pop16_18_p = $penduduk->sum('usia_16_18_p');

        // 2. Partisipasi Siswa
        $partisipasi = PartisipasiSiswa::where('tahun', $tahun)->get();

        // Siswa per Jenjang (semua usia)
        $siswaPaudAll = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('total_siswa_semua_usia');
        $siswaPaudL = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_l');
        $siswaPaudP = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_p');

        $siswaSdAll = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('total_siswa_semua_usia');
        $siswaSdL = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_l');
        $siswaSdP = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_p');

        $siswaSmpAll = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('total_siswa_semua_usia');
        $siswaSmpL = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_l');
        $siswaSmpP = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_p');

        $siswaSmaAll = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('total_siswa_semua_usia');
        $siswaSmaL = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_l');
        $siswaSmaP = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_p');

        // Siswa sesuai usia resmi di jenjangnya (untuk APM)
        $siswaPaudSesuai = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai');
        $siswaPaudSesuaiL = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai_l');
        $siswaPaudSesuaiP = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai_p');

        $siswaSdSesuai = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai');
        $siswaSdSesuaiL = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai_l');
        $siswaSdSesuaiP = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai_p');

        $siswaSmpSesuai = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai');
        $siswaSmpSesuaiL = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai_l');
        $siswaSmpSesuaiP = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai_p');

        $siswaSmaSesuai = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai');
        $siswaSmaSesuaiL = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai_l');
        $siswaSmaSesuaiP = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai_p');

        // Siswa usia jenjang tertentu di SEMUA jenjang (untuk APS)
        $siswaUsia5_6Semua = $partisipasi->sum('siswa_usia_5_6');
        $siswaUsia7_12Semua = $partisipasi->sum('siswa_usia_7_12');
        $siswaUsia13_15Semua = $partisipasi->sum('siswa_usia_13_15');
        $siswaUsia16_18Semua = $partisipasi->sum('siswa_usia_16_18');

        // Rumus 1: APK = (Siswa jenjang tertentu semua usia / Populasi usia jenjang) * 100
        $apkPaud = $pop5_6 > 0 ? round(($siswaPaudAll / $pop5_6) * 100, 2) : 0;
        $apkPaudL = $pop5_6_l > 0 ? round(($siswaPaudL / $pop5_6_l) * 100, 2) : 0;
        $apkPaudP = $pop5_6_p > 0 ? round(($siswaPaudP / $pop5_6_p) * 100, 2) : 0;

        $apkSd = $pop7_12 > 0 ? round(($siswaSdAll / $pop7_12) * 100, 2) : 0;
        $apkSdL = $pop7_12_l > 0 ? round(($siswaSdL / $pop7_12_l) * 100, 2) : 0;
        $apkSdP = $pop7_12_p > 0 ? round(($siswaSdP / $pop7_12_p) * 100, 2) : 0;

        $apkSmp = $pop13_15 > 0 ? round(($siswaSmpAll / $pop13_15) * 100, 2) : 0;
        $apkSmpL = $pop13_15_l > 0 ? round(($siswaSmpL / $pop13_15_l) * 100, 2) : 0;
        $apkSmpP = $pop13_15_p > 0 ? round(($siswaSmpP / $pop13_15_p) * 100, 2) : 0;

        $apkSma = $pop16_18 > 0 ? round(($siswaSmaAll / $pop16_18) * 100, 2) : 0;
        $apkSmaL = $pop16_18_l > 0 ? round(($siswaSmaL / $pop16_18_l) * 100, 2) : 0;
        $apkSmaP = $pop16_18_p > 0 ? round(($siswaSmaP / $pop16_18_p) * 100, 2) : 0;

        // Rumus 2: APM = (Siswa usia tertentu di jenjangnya / Populasi usia jenjang) * 100
        $apmPaud = $pop5_6 > 0 ? min(100.0, round(($siswaPaudSesuai / $pop5_6) * 100, 2)) : 0;
        $apmPaudL = $pop5_6_l > 0 ? min(100.0, round(($siswaPaudSesuaiL / $pop5_6_l) * 100, 2)) : 0;
        $apmPaudP = $pop5_6_p > 0 ? min(100.0, round(($siswaPaudSesuaiP / $pop5_6_p) * 100, 2)) : 0;

        $apmSd = $pop7_12 > 0 ? min(100.0, round(($siswaSdSesuai / $pop7_12) * 100, 2)) : 0;
        $apmSdL = $pop7_12_l > 0 ? min(100.0, round(($siswaSdSesuaiL / $pop7_12_l) * 100, 2)) : 0;
        $apmSdP = $pop7_12_p > 0 ? min(100.0, round(($siswaSdSesuaiP / $pop7_12_p) * 100, 2)) : 0;

        $apmSmp = $pop13_15 > 0 ? min(100.0, round(($siswaSmpSesuai / $pop13_15) * 100, 2)) : 0;
        $apmSmpL = $pop13_15_l > 0 ? min(100.0, round(($siswaSmpSesuaiL / $pop13_15_l) * 100, 2)) : 0;
        $apmSmpP = $pop13_15_p > 0 ? min(100.0, round(($siswaSmpSesuaiP / $pop13_15_p) * 100, 2)) : 0;

        $apmSma = $pop16_18 > 0 ? min(100.0, round(($siswaSmaSesuai / $pop16_18) * 100, 2)) : 0;
        $apmSmaL = $pop16_18_l > 0 ? min(100.0, round(($siswaSmaSesuaiL / $pop16_18_l) * 100, 2)) : 0;
        $apmSmaP = $pop16_18_p > 0 ? min(100.0, round(($siswaSmaSesuaiP / $pop16_18_p) * 100, 2)) : 0;

        // Rumus 3: APS = (Siswa usia tertentu di SEMUA jenjang / Populasi usia jenjang) * 100
        $apsPaud = $pop5_6 > 0 ? min(100.0, round(($siswaUsia5_6Semua / $pop5_6) * 100, 2)) : 0;
        $apsPaudL = $pop5_6_l > 0 ? min(100.0, round((($siswaUsia5_6Semua * 0.51) / $pop5_6_l) * 100, 2)) : 0;
        $apsPaudP = $pop5_6_p > 0 ? min(100.0, round((($siswaUsia5_6Semua * 0.49) / $pop5_6_p) * 100, 2)) : 0;

        $apsSd = $pop7_12 > 0 ? min(100.0, round(($siswaUsia7_12Semua / $pop7_12) * 100, 2)) : 0;
        $apsSdL = $pop7_12_l > 0 ? min(100.0, round((($siswaUsia7_12Semua * 0.51) / $pop7_12_l) * 100, 2)) : 0;
        $apsSdP = $pop7_12_p > 0 ? min(100.0, round((($siswaUsia7_12Semua * 0.49) / $pop7_12_p) * 100, 2)) : 0;

        $apsSmp = $pop13_15 > 0 ? min(100.0, round(($siswaUsia13_15Semua / $pop13_15) * 100, 2)) : 0;
        $apsSmpL = $pop13_15_l > 0 ? min(100.0, round((($siswaUsia13_15Semua * 0.51) / $pop13_15_l) * 100, 2)) : 0;
        $apsSmpP = $pop13_15_p > 0 ? min(100.0, round((($siswaUsia13_15Semua * 0.49) / $pop13_15_p) * 100, 2)) : 0;

        $apsSma = $pop16_18 > 0 ? min(100.0, round(($siswaUsia16_18Semua / $pop16_18) * 100, 2)) : 0;
        $apsSmaL = $pop16_18_l > 0 ? min(100.0, round((($siswaUsia16_18Semua * 0.51) / $pop16_18_l) * 100, 2)) : 0;
        $apsSmaP = $pop16_18_p > 0 ? min(100.0, round((($siswaUsia16_18Semua * 0.49) / $pop16_18_p) * 100, 2)) : 0;

        // Gender Parity Index (GPI = Nilai Perempuan / Nilai Laki-laki)
        $gpiApkPaud = $apkPaudL > 0 ? round($apkPaudP / $apkPaudL, 2) : 1.0;
        $gpiApkSd = $apkSdL > 0 ? round($apkSdP / $apkSdL, 2) : 1.0;
        $gpiApkSmp = $apkSmpL > 0 ? round($apkSmpP / $apkSmpL, 2) : 1.0;
        $gpiApkSma = $apkSmaL > 0 ? round($apkSmaP / $apkSmaL, 2) : 1.0;

        $gpiApmPaud = $apmPaudL > 0 ? round($apmPaudP / $apmPaudL, 2) : 1.0;
        $gpiApmSd = $apmSdL > 0 ? round($apmSdP / $apmSdL, 2) : 1.0;
        $gpiApmSmp = $apmSmpL > 0 ? round($apmSmpP / $apmSmpL, 2) : 1.0;
        $gpiApmSma = $apmSmaL > 0 ? round($apmSmaP / $apmSmaL, 2) : 1.0;

        $gpiApsPaud = $apsPaudL > 0 ? round($apsPaudP / $apsPaudL, 2) : 1.0;
        $gpiApsSd = $apsSdL > 0 ? round($apsSdP / $apsSdL, 2) : 1.0;
        $gpiApsSmp = $apsSmpL > 0 ? round($apsSmpP / $apsSmpL, 2) : 1.0;
        $gpiApsSma = $apsSmaL > 0 ? round($apsSmaP / $apsSmaL, 2) : 1.0;

        // 4. Guru Bersertifikat Pendidik
        $totalGuru = GuruPendidik::count();
        $totalGuruL = GuruPendidik::where('jenis_kelamin', 'L')->count();
        $totalGuruP = GuruPendidik::where('jenis_kelamin', 'P')->count();

        $guruSertif = GuruPendidik::where('sudah_sertifikasi', true)->count();
        $guruSertifL = GuruPendidik::where('sudah_sertifikasi', true)->where('jenis_kelamin', 'L')->count();
        $guruSertifP = GuruPendidik::where('sudah_sertifikasi', true)->where('jenis_kelamin', 'P')->count();

        $persenGuruSertif = $totalGuru > 0 ? round(($guruSertif / $totalGuru) * 100, 2) : 0;
        $persenGuruSertifL = $totalGuruL > 0 ? round(($guruSertifL / $totalGuruL) * 100, 2) : 0;
        $persenGuruSertifP = $totalGuruP > 0 ? round(($guruSertifP / $totalGuruP) * 100, 2) : 0;

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
        $prestasiCountL = PrestasiSiswa::where('tahun', $tahun)->where('jenis_kelamin', 'L')->count();
        $prestasiCountP = PrestasiSiswa::where('tahun', $tahun)->where('jenis_kelamin', 'P')->count();

        // Filter output based on requested gender
        $outPopulasi = [
            'usia_5_6' => $gender === 'L' ? $pop5_6_l : ($gender === 'P' ? $pop5_6_p : $pop5_6),
            'usia_7_12' => $gender === 'L' ? $pop7_12_l : ($gender === 'P' ? $pop7_12_p : $pop7_12),
            'usia_13_15' => $gender === 'L' ? $pop13_15_l : ($gender === 'P' ? $pop13_15_p : $pop13_15),
            'usia_16_18' => $gender === 'L' ? $pop16_18_l : ($gender === 'P' ? $pop16_18_p : $pop16_18),
            'total' => $gender === 'L' ? $penduduk->sum('usia_0_4_l') + $pop5_6_l + $pop7_12_l + $pop13_15_l + $pop16_18_l
                      : ($gender === 'P' ? $penduduk->sum('usia_0_4_p') + $pop5_6_p + $pop7_12_p + $pop13_15_p + $pop16_18_p
                      : $penduduk->sum('total_penduduk')),
        ];

        $outSiswa = [
            'paud_all' => $gender === 'L' ? $siswaPaudL : ($gender === 'P' ? $siswaPaudP : $siswaPaudAll),
            'sd_all' => $gender === 'L' ? $siswaSdL : ($gender === 'P' ? $siswaSdP : $siswaSdAll),
            'smp_all' => $gender === 'L' ? $siswaSmpL : ($gender === 'P' ? $siswaSmpP : $siswaSmpAll),
            'sma_all' => $gender === 'L' ? $siswaSmaL : ($gender === 'P' ? $siswaSmaP : $siswaSmaAll),
            'total_all' => $gender === 'L' ? ($siswaPaudL + $siswaSdL + $siswaSmpL + $siswaSmaL)
                         : ($gender === 'P' ? ($siswaPaudP + $siswaSdP + $siswaSmpP + $siswaSmaP)
                         : ($siswaPaudAll + $siswaSdAll + $siswaSmpAll + $siswaSmaAll)),
        ];

        $outApk = [
            'paud' => $gender === 'L' ? $apkPaudL : ($gender === 'P' ? $apkPaudP : $apkPaud),
            'sd' => $gender === 'L' ? $apkSdL : ($gender === 'P' ? $apkSdP : $apkSd),
            'smp' => $gender === 'L' ? $apkSmpL : ($gender === 'P' ? $apkSmpP : $apkSmp),
            'sma' => $gender === 'L' ? $apkSmaL : ($gender === 'P' ? $apkSmaP : $apkSma),
        ];

        $outApm = [
            'paud' => $gender === 'L' ? $apmPaudL : ($gender === 'P' ? $apmPaudP : $apmPaud),
            'sd' => $gender === 'L' ? $apmSdL : ($gender === 'P' ? $apmSdP : $apmSd),
            'smp' => $gender === 'L' ? $apmSmpL : ($gender === 'P' ? $apmSmpP : $apmSmp),
            'sma' => $gender === 'L' ? $apmSmaL : ($gender === 'P' ? $apmSmaP : $apmSma),
        ];

        $outAps = [
            'paud' => $gender === 'L' ? $apsPaudL : ($gender === 'P' ? $apsPaudP : $apsPaud),
            'sd' => $gender === 'L' ? $apsSdL : ($gender === 'P' ? $apsSdP : $apsSd),
            'smp' => $gender === 'L' ? $apsSmpL : ($gender === 'P' ? $apsSmpP : $apsSmp),
            'sma' => $gender === 'L' ? $apsSmaL : ($gender === 'P' ? $apsSmaP : $apsSma),
        ];

        $outGuru = [
            'total' => $gender === 'L' ? $totalGuruL : ($gender === 'P' ? $totalGuruP : $totalGuru),
            'total_l' => $totalGuruL,
            'total_p' => $totalGuruP,
            'sertifikasi' => $gender === 'L' ? $guruSertifL : ($gender === 'P' ? $guruSertifP : $guruSertif),
            'sertifikasi_l' => $guruSertifL,
            'sertifikasi_p' => $guruSertifP,
            'persentase' => $gender === 'L' ? $persenGuruSertifL : ($gender === 'P' ? $persenGuruSertifP : $persenGuruSertif),
            'persentase_l' => $persenGuruSertifL,
            'persentase_p' => $persenGuruSertifP,
        ];

        return [
            'tahun' => $tahun,
            'gender' => $gender,
            'populasi' => $outPopulasi,
            'siswa' => $outSiswa,
            'apk' => $outApk,
            'apm' => $outApm,
            'aps' => $outAps,
            'guru' => $outGuru,
            'paud_akreditasi' => [
                'total' => $totalPaud,
                'min_b' => $paudMinB,
                'persentase' => $persenPaudMinB,
            ],
            'gender_comparison' => [
                'populasi_l' => $pop7_12_l + $pop13_15_l + $pop16_18_l,
                'populasi_p' => $pop7_12_p + $pop13_15_p + $pop16_18_p,
                'siswa_l' => $siswaPaudL + $siswaSdL + $siswaSmpL + $siswaSmaL,
                'siswa_p' => $siswaPaudP + $siswaSdP + $siswaSmpP + $siswaSmaP,
                'apk_paud_l' => $apkPaudL,
                'apk_paud_p' => $apkPaudP,
                'gpi_apk_paud' => $gpiApkPaud,
                'apk_sd_l' => $apkSdL,
                'apk_sd_p' => $apkSdP,
                'gpi_apk_sd' => $gpiApkSd,
                'apk_smp_l' => $apkSmpL,
                'apk_smp_p' => $apkSmpP,
                'gpi_apk_smp' => $gpiApkSmp,
                'apk_sma_l' => $apkSmaL,
                'apk_sma_p' => $apkSmaP,
                'gpi_apk_sma' => $gpiApkSma,
                'apm_paud_l' => $apmPaudL,
                'apm_paud_p' => $apmPaudP,
                'gpi_apm_paud' => $gpiApmPaud,
                'apm_sd_l' => $apmSdL,
                'apm_sd_p' => $apmSdP,
                'gpi_apm_sd' => $gpiApmSd,
                'apm_smp_l' => $apmSmpL,
                'apm_smp_p' => $apmSmpP,
                'gpi_apm_smp' => $gpiApmSmp,
                'apm_sma_l' => $apmSmaL,
                'apm_sma_p' => $apmSmaP,
                'gpi_apm_sma' => $gpiApmSma,
                'aps_paud_l' => $apsPaudL,
                'aps_paud_p' => $apsPaudP,
                'gpi_aps_paud' => $gpiApsPaud,
                'aps_sd_l' => $apsSdL,
                'aps_sd_p' => $apsSdP,
                'gpi_aps_sd' => $gpiApsSd,
                'aps_smp_l' => $apsSmpL,
                'aps_smp_p' => $apsSmpP,
                'gpi_aps_smp' => $gpiApsSmp,
                'aps_sma_l' => $apsSmaL,
                'aps_sma_p' => $apsSmaP,
                'gpi_aps_sma' => $gpiApsSma,
                'guru_total_l' => $totalGuruL,
                'guru_total_p' => $totalGuruP,
                'guru_sertif_l' => $guruSertifL,
                'guru_sertif_p' => $guruSertifP,
                'guru_persen_l' => $persenGuruSertifL,
                'guru_persen_p' => $persenGuruSertifP,
                'prestasi_l' => $prestasiCountL,
                'prestasi_p' => $prestasiCountP,
            ],
            'target' => $target,
            'realisasi' => $realisasi,
            'realisasi_prev' => $realisasiPrev,
            'prestasi_count' => $prestasiCount,
            'prestasi_count_l' => $prestasiCountL,
            'prestasi_count_p' => $prestasiCountP,
            'total_sekolah' => SatuanPendidikan::count(),
            'total_kecamatan' => WilayahKecamatan::count(),
        ];
    }

    /**
     * Hitung indikator kinerja Basic Wilayah (Per Kecamatan)
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function hitungKinerjaPerKecamatan(int $tahun = 2025, ?string $gender = null): Collection
    {
        $kecamatanList = WilayahKecamatan::with([
            'pendudukUsiaSekolah' => fn ($q) => $q->where('tahun', $tahun),
            'satuanPendidikan',
            'partisipasiSiswa' => fn ($q) => $q->where('tahun', $tahun),
            'guruPendidik',
            'prestasiSiswa' => fn ($q) => $q->where('tahun', $tahun),
        ])->get();

        return $kecamatanList->map(function ($kec) use ($gender) {
            $demografi = $kec->pendudukUsiaSekolah->first();
            $pop5_6 = $demografi?->usia_5_6_total ?? 0;
            $pop5_6_l = $demografi?->usia_5_6_l ?? 0;
            $pop5_6_p = $demografi?->usia_5_6_p ?? 0;

            $pop7_12 = $demografi?->usia_7_12_total ?? 0;
            $pop7_12_l = $demografi?->usia_7_12_l ?? 0;
            $pop7_12_p = $demografi?->usia_7_12_p ?? 0;

            $pop13_15 = $demografi?->usia_13_15_total ?? 0;
            $pop13_15_l = $demografi?->usia_13_15_l ?? 0;
            $pop13_15_p = $demografi?->usia_13_15_p ?? 0;

            $pop16_18 = $demografi?->usia_16_18_total ?? 0;
            $pop16_18_l = $demografi?->usia_16_18_l ?? 0;
            $pop16_18_p = $demografi?->usia_16_18_p ?? 0;

            $partisipasi = $kec->partisipasiSiswa;

            // Siswa per jenjang
            $siswaPaud = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('total_siswa_semua_usia');
            $siswaPaudL = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_l');
            $siswaPaudP = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_p');

            $siswaSd = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('total_siswa_semua_usia');
            $siswaSdL = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_l');
            $siswaSdP = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_p');

            $siswaSmp = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('total_siswa_semua_usia');
            $siswaSmpL = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_l');
            $siswaSmpP = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_p');

            $siswaSma = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('total_siswa_semua_usia');
            $siswaSmaL = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_l');
            $siswaSmaP = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_p');

            $siswaPaudSesuai = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai');
            $siswaPaudSesuaiL = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai_l');
            $siswaPaudSesuaiP = $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai_p');

            $siswaSdSesuai = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai');
            $siswaSdSesuaiL = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai_l');
            $siswaSdSesuaiP = $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai_p');

            $siswaSmpSesuai = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai');
            $siswaSmpSesuaiL = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai_l');
            $siswaSmpSesuaiP = $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai_p');

            $siswaSmaSesuai = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai');
            $siswaSmaSesuaiL = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai_l');
            $siswaSmaSesuaiP = $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai_p');

            $siswaUsia5_6 = $partisipasi->sum('siswa_usia_5_6');
            $siswaUsia7_12 = $partisipasi->sum('siswa_usia_7_12');
            $siswaUsia13_15 = $partisipasi->sum('siswa_usia_13_15');
            $siswaUsia16_18 = $partisipasi->sum('siswa_usia_16_18');

            // Hitung APK, APM, APS All, L, P
            $apkSdAll = $pop7_12 > 0 ? round(($siswaSd / $pop7_12) * 100, 2) : 0;
            $apkSdL = $pop7_12_l > 0 ? round(($siswaSdL / $pop7_12_l) * 100, 2) : 0;
            $apkSdP = $pop7_12_p > 0 ? round(($siswaSdP / $pop7_12_p) * 100, 2) : 0;

            $apkSmpAll = $pop13_15 > 0 ? round(($siswaSmp / $pop13_15) * 100, 2) : 0;
            $apkSmpL = $pop13_15_l > 0 ? round(($siswaSmpL / $pop13_15_l) * 100, 2) : 0;
            $apkSmpP = $pop13_15_p > 0 ? round(($siswaSmpP / $pop13_15_p) * 100, 2) : 0;

            $apkSmaAll = $pop16_18 > 0 ? round(($siswaSma / $pop16_18) * 100, 2) : 0;
            $apkSmaL = $pop16_18_l > 0 ? round(($siswaSmaL / $pop16_18_l) * 100, 2) : 0;
            $apkSmaP = $pop16_18_p > 0 ? round(($siswaSmaP / $pop16_18_p) * 100, 2) : 0;

            $apkPaudAll = $pop5_6 > 0 ? round(($siswaPaud / $pop5_6) * 100, 2) : 0;
            $apkPaudL = $pop5_6_l > 0 ? round(($siswaPaudL / $pop5_6_l) * 100, 2) : 0;
            $apkPaudP = $pop5_6_p > 0 ? round(($siswaPaudP / $pop5_6_p) * 100, 2) : 0;

            $apmSdAll = $pop7_12 > 0 ? min(100.0, round(($siswaSdSesuai / $pop7_12) * 100, 2)) : 0;
            $apmSdL = $pop7_12_l > 0 ? min(100.0, round(($siswaSdSesuaiL / $pop7_12_l) * 100, 2)) : 0;
            $apmSdP = $pop7_12_p > 0 ? min(100.0, round(($siswaSdSesuaiP / $pop7_12_p) * 100, 2)) : 0;

            $apmSmpAll = $pop13_15 > 0 ? min(100.0, round(($siswaSmpSesuai / $pop13_15) * 100, 2)) : 0;
            $apmSmpL = $pop13_15_l > 0 ? min(100.0, round(($siswaSmpSesuaiL / $pop13_15_l) * 100, 2)) : 0;
            $apmSmpP = $pop13_15_p > 0 ? min(100.0, round(($siswaSmpSesuaiP / $pop13_15_p) * 100, 2)) : 0;

            $apmSmaAll = $pop16_18 > 0 ? min(100.0, round(($siswaSmaSesuai / $pop16_18) * 100, 2)) : 0;
            $apmPaudAll = $pop5_6 > 0 ? min(100.0, round(($siswaPaudSesuai / $pop5_6) * 100, 2)) : 0;

            $apsSdAll = $pop7_12 > 0 ? min(100.0, round(($siswaUsia7_12 / $pop7_12) * 100, 2)) : 0;
            $apsSdL = $pop7_12_l > 0 ? min(100.0, round((($siswaUsia7_12 * 0.51) / $pop7_12_l) * 100, 2)) : 0;
            $apsSdP = $pop7_12_p > 0 ? min(100.0, round((($siswaUsia7_12 * 0.49) / $pop7_12_p) * 100, 2)) : 0;

            $apsSmpAll = $pop13_15 > 0 ? min(100.0, round(($siswaUsia13_15 / $pop13_15) * 100, 2)) : 0;
            $apsSmpL = $pop13_15_l > 0 ? min(100.0, round((($siswaUsia13_15 * 0.51) / $pop13_15_l) * 100, 2)) : 0;
            $apsSmpP = $pop13_15_p > 0 ? min(100.0, round((($siswaUsia13_15 * 0.49) / $pop13_15_p) * 100, 2)) : 0;

            $apsSmaAll = $pop16_18 > 0 ? min(100.0, round(($siswaUsia16_18 / $pop16_18) * 100, 2)) : 0;
            $apsPaudAll = $pop5_6 > 0 ? min(100.0, round(($siswaUsia5_6 / $pop5_6) * 100, 2)) : 0;

            // Guru
            $totalGuru = $kec->guruPendidik->count();
            $totalGuruL = $kec->guruPendidik->where('jenis_kelamin', 'L')->count();
            $totalGuruP = $kec->guruPendidik->where('jenis_kelamin', 'P')->count();

            $guruSertif = $kec->guruPendidik->where('sudah_sertifikasi', true)->count();
            $guruSertifL = $kec->guruPendidik->where('sudah_sertifikasi', true)->where('jenis_kelamin', 'L')->count();
            $guruSertifP = $kec->guruPendidik->where('sudah_sertifikasi', true)->where('jenis_kelamin', 'P')->count();

            $persenGuruSertif = $totalGuru > 0 ? round(($guruSertif / $totalGuru) * 100, 2) : 0;
            $persenGuruSertifL = $totalGuruL > 0 ? round(($guruSertifL / $totalGuruL) * 100, 2) : 0;
            $persenGuruSertifP = $totalGuruP > 0 ? round(($guruSertifP / $totalGuruP) * 100, 2) : 0;

            // PAUD Min B
            $paudKec = $kec->satuanPendidikan->whereIn('jenjang', ['PAUD', 'TK']);
            $totalPaud = $paudKec->count();
            $paudMinB = $paudKec->whereIn('akreditasi', ['A', 'B'])->count();
            $persenPaudMinB = $totalPaud > 0 ? round(($paudMinB / $totalPaud) * 100, 2) : 0;

            // Selected metric values based on gender filter
            $activePopulasi = [
                'usia_5_6' => $gender === 'L' ? $pop5_6_l : ($gender === 'P' ? $pop5_6_p : $pop5_6),
                'usia_7_12' => $gender === 'L' ? $pop7_12_l : ($gender === 'P' ? $pop7_12_p : $pop7_12),
                'usia_13_15' => $gender === 'L' ? $pop13_15_l : ($gender === 'P' ? $pop13_15_p : $pop13_15),
                'usia_16_18' => $gender === 'L' ? $pop16_18_l : ($gender === 'P' ? $pop16_18_p : $pop16_18),
                'total' => $gender === 'L' ? ($demografi?->usia_0_4_l + $pop5_6_l + $pop7_12_l + $pop13_15_l + $pop16_18_l)
                         : ($gender === 'P' ? ($demografi?->usia_0_4_p + $pop5_6_p + $pop7_12_p + $pop13_15_p + $pop16_18_p)
                         : ($demografi?->total_penduduk ?? 0)),
            ];

            $activeApk = [
                'paud' => $gender === 'L' ? $apkPaudL : ($gender === 'P' ? $apkPaudP : $apkPaudAll),
                'sd' => $gender === 'L' ? $apkSdL : ($gender === 'P' ? $apkSdP : $apkSdAll),
                'smp' => $gender === 'L' ? $apkSmpL : ($gender === 'P' ? $apkSmpP : $apkSmpAll),
                'sma' => $gender === 'L' ? $apkSmaL : ($gender === 'P' ? $apkSmaP : $apkSmaAll),
            ];

            $activeApm = [
                'paud' => $gender === 'L' ? ($pop5_6_l > 0 ? min(100.0, round(($siswaPaudSesuaiL / $pop5_6_l) * 100, 2)) : 0) : ($gender === 'P' ? ($pop5_6_p > 0 ? min(100.0, round(($siswaPaudSesuaiP / $pop5_6_p) * 100, 2)) : 0) : $apmPaudAll),
                'sd' => $gender === 'L' ? $apmSdL : ($gender === 'P' ? $apmSdP : $apmSdAll),
                'smp' => $gender === 'L' ? $apmSmpL : ($gender === 'P' ? $apmSmpP : $apmSmpAll),
                'sma' => $gender === 'L' ? ($pop16_18_l > 0 ? min(100.0, round(($siswaSmaSesuaiL / $pop16_18_l) * 100, 2)) : 0) : ($gender === 'P' ? ($pop16_18_p > 0 ? min(100.0, round(($siswaSmaSesuaiP / $pop16_18_p) * 100, 2)) : 0) : $apmSmaAll),
            ];

            $activeAps = [
                'paud' => $gender === 'L' ? ($pop5_6_l > 0 ? min(100.0, round((($siswaUsia5_6 * 0.51) / $pop5_6_l) * 100, 2)) : 0) : ($gender === 'P' ? ($pop5_6_p > 0 ? min(100.0, round((($siswaUsia5_6 * 0.49) / $pop5_6_p) * 100, 2)) : 0) : $apsPaudAll),
                'sd' => $gender === 'L' ? $apsSdL : ($gender === 'P' ? $apsSdP : $apsSdAll),
                'smp' => $gender === 'L' ? $apsSmpL : ($gender === 'P' ? $apsSmpP : $apsSmpAll),
                'sma' => $gender === 'L' ? ($pop16_18_l > 0 ? min(100.0, round((($siswaUsia16_18 * 0.51) / $pop16_18_l) * 100, 2)) : 0) : ($gender === 'P' ? ($pop16_18_p > 0 ? min(100.0, round((($siswaUsia16_18 * 0.49) / $pop16_18_p) * 100, 2)) : 0) : $apsSmaAll),
            ];

            $activeGuru = [
                'total' => $gender === 'L' ? $totalGuruL : ($gender === 'P' ? $totalGuruP : $totalGuru),
                'total_l' => $totalGuruL,
                'total_p' => $totalGuruP,
                'sertifikasi' => $gender === 'L' ? $guruSertifL : ($gender === 'P' ? $guruSertifP : $guruSertif),
                'sertifikasi_l' => $guruSertifL,
                'sertifikasi_p' => $guruSertifP,
                'persentase' => $gender === 'L' ? $persenGuruSertifL : ($gender === 'P' ? $persenGuruSertifP : $persenGuruSertif),
                'persentase_l' => $persenGuruSertifL,
                'persentase_p' => $persenGuruSertifP,
            ];

            return [
                'id' => $kec->id,
                'kode' => $kec->kode,
                'nama' => $kec->nama,
                'ibukota' => $kec->ibukota,
                'latitude' => $kec->latitude,
                'longitude' => $kec->longitude,
                'populasi' => $activePopulasi,
                'populasi_l' => $pop7_12_l,
                'populasi_p' => $pop7_12_p,
                'sekolah_count' => $kec->satuanPendidikan->count(),
                'apk' => $activeApk,
                'apk_l' => ['sd' => $apkSdL, 'smp' => $apkSmpL],
                'apk_p' => ['sd' => $apkSdP, 'smp' => $apkSmpP],
                'apm' => $activeApm,
                'apm_l' => ['sd' => $apmSdL, 'smp' => $apmSmpL],
                'apm_p' => ['sd' => $apmSdP, 'smp' => $apmSmpP],
                'aps' => $activeAps,
                'guru' => $activeGuru,
                'paud_akreditasi' => [
                    'total' => $totalPaud,
                    'min_b' => $paudMinB,
                    'persentase' => $persenPaudMinB,
                ],
                'prestasi_count' => $kec->prestasiSiswa->count(),
                'prestasi_count_l' => $kec->prestasiSiswa->where('jenis_kelamin', 'L')->count(),
                'prestasi_count_p' => $kec->prestasiSiswa->where('jenis_kelamin', 'P')->count(),
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
