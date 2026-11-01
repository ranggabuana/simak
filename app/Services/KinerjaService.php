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
    public function hitungKinerjaKabupaten(?int $tahun = null, ?string $gender = null): array
    {
        $tahun ??= (int) date('Y');

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
    public function hitungKinerjaPerKecamatan(?int $tahun = null, ?string $gender = null): Collection
    {
        $tahun ??= (int) date('Y');

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
     * Hitung data indikator resmi (sesuai format tabel file XLSX APK, APM, dan APS)
     * Meliputi 3 jenjang: PAUD (usia 5-6), SD (usia 7-12), dan SMP (usia 13-15)
     *
     * @param  string  $tipe  'apk' | 'apm' | 'aps'
     * @return array<string, mixed>
     */
    public function getIndikatorOfficialData(string $tipe, ?int $tahun = null): array
    {
        $tahun ??= (int) date('Y');
        $tipe = strtolower($tipe);

        $kecamatanList = WilayahKecamatan::with([
            'pendudukUsiaSekolah' => fn ($q) => $q->where('tahun', $tahun),
            'partisipasiSiswa' => fn ($q) => $q->where('tahun', $tahun),
        ])->orderBy('nama')->get();

        $labels = [
            'apk' => [
                'title' => 'APK',
                'full_title' => 'ANGKA PARTISIPASI KASAR (APK)',
                'paud_title' => "APK PAUD  TAHUN {$tahun}",
                'sd_title' => "APK SD Sederajat TAHUN {$tahun}",
                'smp_title' => "APK SMP SEDERAJAT  TAHUN {$tahun}",
                'sma_title' => "APK SMA/SMK Sederajat TAHUN {$tahun}",
                'paud_penduduk' => 'Jumlah anak usia 5-6 tahun',
                'sd_penduduk' => 'Jumlah anak usia 7-12 tahun',
                'smp_penduduk' => 'Jumlah anak usia 13-15',
                'sma_penduduk' => 'Jumlah anak usia 16-18 tahun',
                'paud_siswa' => 'Jumlah siswa jenjang PAUD sedang belajar di satuan PAUD',
                'sd_siswa' => 'Jumlah Siswa jenjang SD/sederajat yang sedang belajar di sekolah dasar/sederajat',
                'smp_siswa' => 'Jumlah Siswa jenjang SMP Sederajat sedang belajar di sekolah menengah pertama sederajat',
                'sma_siswa' => 'Jumlah Siswa jenjang SMA/SMK/sederajat sedang belajar di sekolah menengah atas/kejuruan',
                'paud_capaian' => 'Angka Partisipasi Kasar  (APK) PAUD',
                'sd_capaian' => 'APK SD/Sederajat',
                'smp_capaian' => 'Angka Partisipasi Kasar SMP/Sederajat',
                'sma_capaian' => 'Angka Partisipasi Kasar SMA/SMK',
            ],
            'apm' => [
                'title' => 'APM',
                'full_title' => 'ANGKA PARTISIPASI MURNI (APM)',
                'paud_title' => "APM  PAUD TAHUN {$tahun}",
                'sd_title' => "APM  SD TAHUN {$tahun}",
                'smp_title' => "APM  SMP TAHUN {$tahun}",
                'sma_title' => "APM  SMA/SMK TAHUN {$tahun}",
                'paud_penduduk' => 'Jumlah anak usia 5-6 tahun',
                'sd_penduduk' => 'Jumlah anak usia 7-12 tahun',
                'smp_penduduk' => 'Jumlah anak usia 13-15',
                'sma_penduduk' => 'Jumlah anak usia 16-18 tahun',
                'paud_siswa' => 'Jumlah anak usia 5-6 tahun yang sedang belajar di satuan PAUD',
                'sd_siswa' => 'Jumlah anak usia 7-12 tahun yang  sedang belajar di sekolah dasar',
                'smp_siswa' => 'Jumlah anak usia 13-15 tahun yang sedang belajar di sekolah menengah pertama',
                'sma_siswa' => 'Jumlah anak usia 16-18 tahun yang sedang belajar di SMA/SMK/sederajat',
                'paud_capaian' => 'Tingkat partisipasi murni warga negara usia 5-6 tahun yang berpartisipasi dalam PAUD',
                'sd_capaian' => 'Tingkat partisipasi murni warga negara usia 7-12 tahun yang berpartisipasi dalam pendidikan dasar',
                'smp_capaian' => 'Tingkat partisipasi murni warga negara usia 13-15 tahun yang berpartisipasi dalam pendidikan menengah pertama',
                'sma_capaian' => 'Tingkat partisipasi murni warga negara usia 16-18 tahun yang berpartisipasi dalam pendidikan menengah',
            ],
            'aps' => [
                'title' => 'APS',
                'full_title' => 'ANGKA PARTISIPASI SEKOLAH (APS)',
                'paud_title' => "APS PAUD TAHUN {$tahun}",
                'sd_title' => "APS SD TAHUN {$tahun}",
                'smp_title' => "APS SMP TAHUN {$tahun}",
                'sma_title' => "APS SMA/SMK TAHUN {$tahun}",
                'paud_penduduk' => 'Jumlah anak usia 5-6 tahun',
                'sd_penduduk' => 'Jumlah anak usia 7-12 tahun',
                'smp_penduduk' => 'Jumlah anak usia 13 -15',
                'sma_penduduk' => 'Jumlah anak usia 16-18 tahun',
                'paud_siswa' => 'Jumlah anak usia 5-6 tahun yang sudah tamat atau sedang belajar di satuan PAUD',
                'sd_siswa' => 'Jumlah anak usia 7-12 tahun yang sudah tamat atau sedang belajar di sekolah dasar',
                'smp_siswa' => 'Jumlah anak usia 13-15 tahun yang sudah tamat atau sedang belajar di sekolah menengah pertama',
                'sma_siswa' => 'Jumlah anak usia 16-18 tahun yang sudah tamat atau sedang belajar di SMA/SMK/sederajat',
                'paud_capaian' => 'Tingkat partisipasi warga negara usia 5-6 tahun yang berpartisipasi dalam PAUD',
                'sd_capaian' => 'Tingkat partisipasi warga negara usia 7-12 tahun yang berpartisipasi dalam pendidikan dasar',
                'smp_capaian' => 'Tingkat partisipasi warga negara usia 13-15 tahun yang berpartisipasi dalam pendidikan menengah pertama',
                'sma_capaian' => 'Tingkat partisipasi warga negara usia 16-18 tahun yang berpartisipasi dalam pendidikan menengah',
            ],
        ];

        $currentLabels = $labels[$tipe] ?? $labels['apk'];

        $rows = [];
        $no = 1;

        $total = [
            'paud' => [
                'penduduk' => ['l' => 0, 'p' => 0, 'jml' => 0],
                'siswa' => ['l' => 0, 'p' => 0, 'jml' => 0],
                'capaian' => ['l' => 0.0, 'p' => 0.0, 'jml' => 0.0],
            ],
            'sd' => [
                'penduduk' => ['l' => 0, 'p' => 0, 'jml' => 0],
                'siswa' => ['l' => 0, 'p' => 0, 'jml' => 0],
                'capaian' => ['l' => 0.0, 'p' => 0.0, 'jml' => 0.0],
            ],
            'smp' => [
                'penduduk' => ['l' => 0, 'p' => 0, 'jml' => 0],
                'siswa' => ['l' => 0, 'p' => 0, 'jml' => 0],
                'capaian' => ['l' => 0.0, 'p' => 0.0, 'jml' => 0.0],
            ],
            'sma' => [
                'penduduk' => ['l' => 0, 'p' => 0, 'jml' => 0],
                'siswa' => ['l' => 0, 'p' => 0, 'jml' => 0],
                'capaian' => ['l' => 0.0, 'p' => 0.0, 'jml' => 0.0],
            ],
        ];

        foreach ($kecamatanList as $kec) {
            $demografi = $kec->pendudukUsiaSekolah->first();
            $partisipasi = $kec->partisipasiSiswa;

            // 1. Populasi Penduduk
            $pop5_6_l = (int) ($demografi?->usia_5_6_l ?? 0);
            $pop5_6_p = (int) ($demografi?->usia_5_6_p ?? 0);
            $pop5_6_jml = (int) ($demografi?->usia_5_6_total ?? ($pop5_6_l + $pop5_6_p));

            $pop7_12_l = (int) ($demografi?->usia_7_12_l ?? 0);
            $pop7_12_p = (int) ($demografi?->usia_7_12_p ?? 0);
            $pop7_12_jml = (int) ($demografi?->usia_7_12_total ?? ($pop7_12_l + $pop7_12_p));

            $pop13_15_l = (int) ($demografi?->usia_13_15_l ?? 0);
            $pop13_15_p = (int) ($demografi?->usia_13_15_p ?? 0);
            $pop13_15_jml = (int) ($demografi?->usia_13_15_total ?? ($pop13_15_l + $pop13_15_p));

            $pop16_18_l = (int) ($demografi?->usia_16_18_l ?? 0);
            $pop16_18_p = (int) ($demografi?->usia_16_18_p ?? 0);
            $pop16_18_jml = (int) ($demografi?->usia_16_18_total ?? ($pop16_18_l + $pop16_18_p));

            // 2. Hitung Siswa per Blok
            if ($tipe === 'apk') {
                $siswaPaudL = (int) $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_l');
                $siswaPaudP = (int) $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_p');
                $siswaPaudJml = (int) $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('total_siswa_semua_usia');

                $siswaSdL = (int) $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_l');
                $siswaSdP = (int) $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_p');
                $siswaSdJml = (int) $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('total_siswa_semua_usia');

                $siswaSmpL = (int) $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_l');
                $siswaSmpP = (int) $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_p');
                $siswaSmpJml = (int) $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('total_siswa_semua_usia');

                $siswaSmaL = (int) $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_l');
                $siswaSmaP = (int) $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_p');
                $siswaSmaJml = (int) $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('total_siswa_semua_usia');
            } elseif ($tipe === 'apm') {
                $siswaPaudL = (int) $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai_l');
                $siswaPaudP = (int) $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai_p');
                $siswaPaudJml = (int) $partisipasi->whereIn('jenjang', ['PAUD', 'TK'])->sum('siswa_usia_sesuai');

                $siswaSdL = (int) $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai_l');
                $siswaSdP = (int) $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai_p');
                $siswaSdJml = (int) $partisipasi->whereIn('jenjang', ['SD', 'MI'])->sum('siswa_usia_sesuai');

                $siswaSmpL = (int) $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai_l');
                $siswaSmpP = (int) $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai_p');
                $siswaSmpJml = (int) $partisipasi->whereIn('jenjang', ['SMP', 'MTS'])->sum('siswa_usia_sesuai');

                $siswaSmaL = (int) $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai_l');
                $siswaSmaP = (int) $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai_p');
                $siswaSmaJml = (int) $partisipasi->whereIn('jenjang', ['SMA', 'SMK', 'MA'])->sum('siswa_usia_sesuai');
            } else {
                $siswaPaudJml = (int) $partisipasi->sum('siswa_usia_5_6');
                $siswaPaudL = $pop5_6_jml > 0 ? (int) round($siswaPaudJml * ($pop5_6_l / $pop5_6_jml)) : (int) round($siswaPaudJml * 0.51);
                $siswaPaudP = $siswaPaudJml - $siswaPaudL;

                $siswaSdJml = (int) $partisipasi->sum('siswa_usia_7_12');
                $siswaSdL = $pop7_12_jml > 0 ? (int) round($siswaSdJml * ($pop7_12_l / $pop7_12_jml)) : (int) round($siswaSdJml * 0.51);
                $siswaSdP = $siswaSdJml - $siswaSdL;

                $siswaSmpJml = (int) $partisipasi->sum('siswa_usia_13_15');
                $siswaSmpL = $pop13_15_jml > 0 ? (int) round($siswaSmpJml * ($pop13_15_l / $pop13_15_jml)) : (int) round($siswaSmpJml * 0.51);
                $siswaSmpP = $siswaSmpJml - $siswaSmpL;

                $siswaSmaJml = (int) $partisipasi->sum('siswa_usia_16_18');
                $siswaSmaL = $pop16_18_jml > 0 ? (int) round($siswaSmaJml * ($pop16_18_l / $pop16_18_jml)) : (int) round($siswaSmaJml * 0.51);
                $siswaSmaP = $siswaSmaJml - $siswaSmaL;
            }

            // 3. Hitung Capaian Indikator (%)
            $capaianPaudL = $pop5_6_l > 0 ? round(($siswaPaudL / $pop5_6_l) * 100, 2) : 0.0;
            $capaianPaudP = $pop5_6_p > 0 ? round(($siswaPaudP / $pop5_6_p) * 100, 2) : 0.0;
            $capaianPaudJml = $pop5_6_jml > 0 ? round(($siswaPaudJml / $pop5_6_jml) * 100, 2) : 0.0;

            $capaianSdL = $pop7_12_l > 0 ? round(($siswaSdL / $pop7_12_l) * 100, 2) : 0.0;
            $capaianSdP = $pop7_12_p > 0 ? round(($siswaSdP / $pop7_12_p) * 100, 2) : 0.0;
            $capaianSdJml = $pop7_12_jml > 0 ? round(($siswaSdJml / $pop7_12_jml) * 100, 2) : 0.0;

            $capaianSmpL = $pop13_15_l > 0 ? round(($siswaSmpL / $pop13_15_l) * 100, 2) : 0.0;
            $capaianSmpP = $pop13_15_p > 0 ? round(($siswaSmpP / $pop13_15_p) * 100, 2) : 0.0;
            $capaianSmpJml = $pop13_15_jml > 0 ? round(($siswaSmpJml / $pop13_15_jml) * 100, 2) : 0.0;

            $capaianSmaL = $pop16_18_l > 0 ? round(($siswaSmaL / $pop16_18_l) * 100, 2) : 0.0;
            $capaianSmaP = $pop16_18_p > 0 ? round(($siswaSmaP / $pop16_18_p) * 100, 2) : 0.0;
            $capaianSmaJml = $pop16_18_jml > 0 ? round(($siswaSmaJml / $pop16_18_jml) * 100, 2) : 0.0;

            // 4. Akumulasi Total
            $total['paud']['penduduk']['l'] += $pop5_6_l;
            $total['paud']['penduduk']['p'] += $pop5_6_p;
            $total['paud']['penduduk']['jml'] += $pop5_6_jml;
            $total['paud']['siswa']['l'] += $siswaPaudL;
            $total['paud']['siswa']['p'] += $siswaPaudP;
            $total['paud']['siswa']['jml'] += $siswaPaudJml;

            $total['sd']['penduduk']['l'] += $pop7_12_l;
            $total['sd']['penduduk']['p'] += $pop7_12_p;
            $total['sd']['penduduk']['jml'] += $pop7_12_jml;
            $total['sd']['siswa']['l'] += $siswaSdL;
            $total['sd']['siswa']['p'] += $siswaSdP;
            $total['sd']['siswa']['jml'] += $siswaSdJml;

            $total['smp']['penduduk']['l'] += $pop13_15_l;
            $total['smp']['penduduk']['p'] += $pop13_15_p;
            $total['smp']['penduduk']['jml'] += $pop13_15_jml;
            $total['smp']['siswa']['l'] += $siswaSmpL;
            $total['smp']['siswa']['p'] += $siswaSmpP;
            $total['smp']['siswa']['jml'] += $siswaSmpJml;

            $total['sma']['penduduk']['l'] += $pop16_18_l;
            $total['sma']['penduduk']['p'] += $pop16_18_p;
            $total['sma']['penduduk']['jml'] += $pop16_18_jml;
            $total['sma']['siswa']['l'] += $siswaSmaL;
            $total['sma']['siswa']['p'] += $siswaSmaP;
            $total['sma']['siswa']['jml'] += $siswaSmaJml;

            $rows[] = [
                'no' => $no++,
                'id' => $kec->id,
                'kode' => $kec->kode,
                'nama' => $kec->nama,
                'paud' => [
                    'penduduk' => ['l' => $pop5_6_l, 'p' => $pop5_6_p, 'jml' => $pop5_6_jml],
                    'siswa' => ['l' => $siswaPaudL, 'p' => $siswaPaudP, 'jml' => $siswaPaudJml],
                    'capaian' => ['l' => $capaianPaudL, 'p' => $capaianPaudP, 'jml' => $capaianPaudJml],
                ],
                'sd' => [
                    'penduduk' => ['l' => $pop7_12_l, 'p' => $pop7_12_p, 'jml' => $pop7_12_jml],
                    'siswa' => ['l' => $siswaSdL, 'p' => $siswaSdP, 'jml' => $siswaSdJml],
                    'capaian' => ['l' => $capaianSdL, 'p' => $capaianSdP, 'jml' => $capaianSdJml],
                ],
                'smp' => [
                    'penduduk' => ['l' => $pop13_15_l, 'p' => $pop13_15_p, 'jml' => $pop13_15_jml],
                    'siswa' => ['l' => $siswaSmpL, 'p' => $siswaSmpP, 'jml' => $siswaSmpJml],
                    'capaian' => ['l' => $capaianSmpL, 'p' => $capaianSmpP, 'jml' => $capaianSmpJml],
                ],
                'sma' => [
                    'penduduk' => ['l' => $pop16_18_l, 'p' => $pop16_18_p, 'jml' => $pop16_18_jml],
                    'siswa' => ['l' => $siswaSmaL, 'p' => $siswaSmaP, 'jml' => $siswaSmaJml],
                    'capaian' => ['l' => $capaianSmaL, 'p' => $capaianSmaP, 'jml' => $capaianSmaJml],
                ],
            ];
        }

        // 5. Hitung Capaian Rata-Rata Kabupaten
        $total['paud']['capaian']['l'] = $total['paud']['penduduk']['l'] > 0 ? round(($total['paud']['siswa']['l'] / $total['paud']['penduduk']['l']) * 100, 2) : 0.0;
        $total['paud']['capaian']['p'] = $total['paud']['penduduk']['p'] > 0 ? round(($total['paud']['siswa']['p'] / $total['paud']['penduduk']['p']) * 100, 2) : 0.0;
        $total['paud']['capaian']['jml'] = $total['paud']['penduduk']['jml'] > 0 ? round(($total['paud']['siswa']['jml'] / $total['paud']['penduduk']['jml']) * 100, 2) : 0.0;

        $total['sd']['capaian']['l'] = $total['sd']['penduduk']['l'] > 0 ? round(($total['sd']['siswa']['l'] / $total['sd']['penduduk']['l']) * 100, 2) : 0.0;
        $total['sd']['capaian']['p'] = $total['sd']['penduduk']['p'] > 0 ? round(($total['sd']['siswa']['p'] / $total['sd']['penduduk']['p']) * 100, 2) : 0.0;
        $total['sd']['capaian']['jml'] = $total['sd']['penduduk']['jml'] > 0 ? round(($total['sd']['siswa']['jml'] / $total['sd']['penduduk']['jml']) * 100, 2) : 0.0;

        $total['smp']['capaian']['l'] = $total['smp']['penduduk']['l'] > 0 ? round(($total['smp']['siswa']['l'] / $total['smp']['penduduk']['l']) * 100, 2) : 0.0;
        $total['smp']['capaian']['p'] = $total['smp']['penduduk']['p'] > 0 ? round(($total['smp']['siswa']['p'] / $total['smp']['penduduk']['p']) * 100, 2) : 0.0;
        $total['smp']['capaian']['jml'] = $total['smp']['penduduk']['jml'] > 0 ? round(($total['smp']['siswa']['jml'] / $total['smp']['penduduk']['jml']) * 100, 2) : 0.0;

        $total['sma']['capaian']['l'] = $total['sma']['penduduk']['l'] > 0 ? round(($total['sma']['siswa']['l'] / $total['sma']['penduduk']['l']) * 100, 2) : 0.0;
        $total['sma']['capaian']['p'] = $total['sma']['penduduk']['p'] > 0 ? round(($total['sma']['siswa']['p'] / $total['sma']['penduduk']['p']) * 100, 2) : 0.0;
        $total['sma']['capaian']['jml'] = $total['sma']['penduduk']['jml'] > 0 ? round(($total['sma']['siswa']['jml'] / $total['sma']['penduduk']['jml']) * 100, 2) : 0.0;

        return [
            'tipe' => $tipe,
            'tahun' => $tahun,
            'labels' => $currentLabels,
            'rows' => $rows,
            'total' => $total,
            'sumber' => 'Sumber data : DISDUKCAPIL, DAPODIK KEMENDIKBUDRISTEK, EMIS - KEMENAG',
        ];
    }

    /**
     * Hitung indikator Basic Sekolah
     */
    public function hitungKinerjaSekolah(?int $kecamatanId = null, ?string $jenjang = null, ?int $tahun = null)
    {
        $tahun ??= (int) date('Y');

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
    public function hitungEarlyWarningSystem(?int $tahun = null): array
    {
        $tahun ??= (int) date('Y');

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
