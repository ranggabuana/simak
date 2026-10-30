<?php

namespace App\Http\Controllers;

use App\Models\GuruPendidik;
use App\Models\PartisipasiSiswa;
use App\Models\PendudukUsiaSekolah;
use App\Models\PrestasiSiswa;
use App\Models\SatuanPendidikan;
use App\Services\ExcelExporter;
use App\Services\KinerjaService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        protected KinerjaService $kinerjaService,
        protected ExcelExporter $excelExporter
    ) {}

    /**
     * Unduh Rekapitulasi Kinerja 20 Kecamatan dalam format Microsoft Excel (.xlsx)
     */
    public function unduhKecamatanCsv(Request $request): StreamedResponse
    {
        $tahun = $this->resolveTahun($request);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun);

        $filename = "rekap_kinerja_kecamatan_{$tahun}.xlsx";
        $sheetTitle = "Rekap Kecamatan {$tahun}";
        $reportTitle = 'REKAPITULASI CAPAIAN INDIKATOR KINERJA PENDIDIKAN KABUPATEN BANJARNEGARA';
        $reportSubtitle = "Tahun Anggaran {$tahun} • Sumber Data: SIMAK DINDIKPORA Banjarnegara • Diunduh: ".now()->format('d/m/Y H:i').' WIB';

        $headers = [
            'No',
            'Kode BPS',
            'Nama Kecamatan',
            'Ibukota Kecamatan',
            'Penduduk 5-6 Th',
            'Penduduk 7-12 Th',
            'Penduduk 13-15 Th',
            'Penduduk 16-18 Th',
            'Total Penduduk',
            'APK SD (%)',
            'APM SD (%)',
            'APS SD (%)',
            'APK SMP (%)',
            'APM SMP (%)',
            'APS SMP (%)',
            'APK SMA (%)',
            'APM SMA (%)',
            'Total Guru',
            'Guru Sertifikasi',
            '% Guru Sertifikasi',
            'Total PAUD',
            'PAUD Akr. Min B',
            '% PAUD Akr. Min B',
            'Jml Prestasi',
        ];

        $rows = [];
        $no = 1;
        foreach ($kecamatanList as $kec) {
            $rows[] = [
                $no++,
                (string) $kec['kode'],
                $kec['nama'],
                $kec['ibukota'] ?? '-',
                (int) $kec['populasi']['usia_5_6'],
                (int) $kec['populasi']['usia_7_12'],
                (int) $kec['populasi']['usia_13_15'],
                (int) $kec['populasi']['usia_16_18'],
                (int) $kec['populasi']['total'],
                (float) $kec['apk']['sd'],
                (float) $kec['apm']['sd'],
                (float) $kec['aps']['sd'],
                (float) $kec['apk']['smp'],
                (float) $kec['apm']['smp'],
                (float) $kec['aps']['smp'],
                (float) $kec['apk']['sma'],
                (float) $kec['apm']['sma'],
                (int) $kec['guru']['total'],
                (int) $kec['guru']['sertifikasi'],
                (float) $kec['guru']['persentase'],
                (int) $kec['paud_akreditasi']['total'],
                (int) $kec['paud_akreditasi']['min_b'],
                (float) $kec['paud_akreditasi']['persentase'],
                (int) $kec['prestasi_count'],
            ];
        }

        return $this->excelExporter->downloadXlsx(
            filename: $filename,
            sheetTitle: $sheetTitle,
            headers: $headers,
            rows: $rows,
            reportTitle: $reportTitle,
            reportSubtitle: $reportSubtitle
        );
    }

    /**
     * Unduh Data Demografi Penduduk Usia Sekolah dalam format Microsoft Excel (.xlsx)
     */
    public function unduhPendudukCsv(Request $request): StreamedResponse
    {
        $tahun = $this->resolveTahun($request);
        $gender = $request->input('gender', 'all');
        $pendudukList = PendudukUsiaSekolah::with('kecamatan')->where('tahun', $tahun)->get();

        $genderLabel = $gender === 'L' ? ' (LAKI-LAKI)' : ($gender === 'P' ? ' (PEREMPUAN)' : '');
        $filename = "data_penduduk_usia_sekolah_{$tahun}".($gender !== 'all' ? '_'.strtolower($gender) : '').'.xlsx';
        $sheetTitle = "Penduduk Usia Sekolah {$tahun}";
        $reportTitle = 'DATA DEMOGRAFI PENDUDUK USIA SEKOLAH'.$genderLabel.' KABUPATEN BANJARNEGARA';
        $reportSubtitle = "Tahun Anggaran {$tahun} • Sumber Data: SIMAK DINDIKPORA Banjarnegara • Diunduh: ".now()->format('d/m/Y H:i').' WIB';

        if ($gender === 'L') {
            $headers = [
                'No', 'Kode Wilayah', 'Kecamatan', 'Tahun',
                'Usia 0-4 (L)', 'Usia 5-6 (PAUD L)', 'Usia 7-12 (SD L)', 'Usia 13-15 (SMP L)', 'Usia 16-18 (SMA L)', 'Usia >18 Est. (L)', 'Total Penduduk (L)',
            ];
            $rows = [];
            $no = 1;
            foreach ($pendudukList as $p) {
                $u18p = (int) round($p->usia_18_plus_total * 0.505);
                $totL = $p->usia_0_4_l + $p->usia_5_6_l + $p->usia_7_12_l + $p->usia_13_15_l + $p->usia_16_18_l + $u18p;
                $rows[] = [
                    $no++, (string) ($p->kecamatan->kode ?? '-'), $p->kecamatan->nama ?? '-', $tahun,
                    (int) $p->usia_0_4_l, (int) $p->usia_5_6_l, (int) $p->usia_7_12_l, (int) $p->usia_13_15_l, (int) $p->usia_16_18_l, $u18p, $totL,
                ];
            }
        } elseif ($gender === 'P') {
            $headers = [
                'No', 'Kode Wilayah', 'Kecamatan', 'Tahun',
                'Usia 0-4 (P)', 'Usia 5-6 (PAUD P)', 'Usia 7-12 (SD P)', 'Usia 13-15 (SMP P)', 'Usia 16-18 (SMA P)', 'Usia >18 Est. (P)', 'Total Penduduk (P)',
            ];
            $rows = [];
            $no = 1;
            foreach ($pendudukList as $p) {
                $u18p = (int) round($p->usia_18_plus_total * 0.495);
                $totP = $p->usia_0_4_p + $p->usia_5_6_p + $p->usia_7_12_p + $p->usia_13_15_p + $p->usia_16_18_p + $u18p;
                $rows[] = [
                    $no++, (string) ($p->kecamatan->kode ?? '-'), $p->kecamatan->nama ?? '-', $tahun,
                    (int) $p->usia_0_4_p, (int) $p->usia_5_6_p, (int) $p->usia_7_12_p, (int) $p->usia_13_15_p, (int) $p->usia_16_18_p, $u18p, $totP,
                ];
            }
        } else {
            $headers = [
                'No',
                'Kode Wilayah',
                'Kecamatan',
                'Tahun',
                'Usia 0-4 (L)',
                'Usia 0-4 (P)',
                'Usia 0-4 Total',
                'Usia 5-6 (L)',
                'Usia 5-6 (P)',
                'Usia 5-6 (PAUD)',
                'Usia 7-12 (L)',
                'Usia 7-12 (P)',
                'Usia 7-12 (SD)',
                'Usia 13-15 (L)',
                'Usia 13-15 (P)',
                'Usia 13-15 (SMP)',
                'Usia 16-18 (L)',
                'Usia 16-18 (P)',
                'Usia 16-18 (SMA)',
                'Usia >18 Total',
                'Total Penduduk',
            ];

            $rows = [];
            $no = 1;
            foreach ($pendudukList as $p) {
                $rows[] = [
                    $no++,
                    (string) ($p->kecamatan->kode ?? '-'),
                    $p->kecamatan->nama ?? '-',
                    $tahun,
                    (int) $p->usia_0_4_l,
                    (int) $p->usia_0_4_p,
                    (int) $p->usia_0_4_total,
                    (int) $p->usia_5_6_l,
                    (int) $p->usia_5_6_p,
                    (int) $p->usia_5_6_total,
                    (int) $p->usia_7_12_l,
                    (int) $p->usia_7_12_p,
                    (int) $p->usia_7_12_total,
                    (int) $p->usia_13_15_l,
                    (int) $p->usia_13_15_p,
                    (int) $p->usia_13_15_total,
                    (int) $p->usia_16_18_l,
                    (int) $p->usia_16_18_p,
                    (int) $p->usia_16_18_total,
                    (int) $p->usia_18_plus_total,
                    (int) $p->total_penduduk,
                ];
            }
        }

        return $this->excelExporter->downloadXlsx(
            filename: $filename,
            sheetTitle: $sheetTitle,
            headers: $headers,
            rows: $rows,
            reportTitle: $reportTitle,
            reportSubtitle: $reportSubtitle
        );
    }

    /**
     * Unduh Data Capaian Prestasi Siswa dalam format Microsoft Excel (.xlsx)
     */
    public function unduhPrestasiCsv(Request $request): StreamedResponse
    {
        $tahun = $this->resolveTahun($request);
        $jenisKelamin = $request->input('jenis_kelamin');

        $query = PrestasiSiswa::with(['satuanPendidikan.kecamatan', 'kecamatan'])
            ->where('tahun', $tahun);

        if ($jenisKelamin) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        $prestasiList = $query->get();

        $genderLabel = $jenisKelamin === 'L' ? ' (LAKI-LAKI)' : ($jenisKelamin === 'P' ? ' (PEREMPUAN)' : '');
        $filename = "rekap_prestasi_siswa_{$tahun}".($jenisKelamin ? '_'.strtolower($jenisKelamin) : '').'.xlsx';
        $sheetTitle = "Prestasi Siswa {$tahun}";
        $reportTitle = 'REKAPITULASI PRESTASI SISWA'.$genderLabel.' KABUPATEN BANJARNEGARA';
        $reportSubtitle = "Tahun Anggaran {$tahun} • Sumber Data: SIMAK DINDIKPORA Banjarnegara • Diunduh: ".now()->format('d/m/Y H:i').' WIB';

        $headers = [
            'No',
            'Nama Siswa',
            'L/P',
            'NISN',
            'Satuan Pendidikan',
            'Kecamatan',
            'Nama Kejuaraan / Lomba',
            'Bidang',
            'Tingkat Kejuaraan',
            'Peringkat / Juara',
            'Tahun',
            'Penyelenggara',
            'Keterangan',
        ];

        $rows = [];
        foreach ($prestasiList as $idx => $p) {
            $rows[] = [
                $idx + 1,
                $p->nama_siswa,
                $p->jenis_kelamin === 'P' ? 'P (Perempuan)' : 'L (Laki-laki)',
                $p->nisn ? (string) $p->nisn : '-',
                $p->satuanPendidikan->nama_sekolah ?? '-',
                $p->kecamatan->nama ?? ($p->satuanPendidikan->kecamatan->nama ?? '-'),
                $p->nama_kejuaraan,
                $p->bidang,
                $p->tingkat,
                $p->peringkat,
                $p->tahun,
                $p->penyelenggara ?? '-',
                $p->keterangan ?? '-',
            ];
        }

        return $this->excelExporter->downloadXlsx(
            filename: $filename,
            sheetTitle: $sheetTitle,
            headers: $headers,
            rows: $rows,
            reportTitle: $reportTitle,
            reportSubtitle: $reportSubtitle
        );
    }

    /**
     * Unduh Data Satuan Pendidikan (Sekolah) dalam format Microsoft Excel (.xlsx)
     */
    public function unduhSekolahCsv(Request $request): StreamedResponse
    {
        $sekolahList = SatuanPendidikan::with('kecamatan')->orderBy('nama_sekolah')->get();

        $filename = 'data_satuan_pendidikan_banjarnegara.xlsx';
        $sheetTitle = 'Satuan Pendidikan';
        $reportTitle = 'DATA SATUAN PENDIDIKAN KABUPATEN BANJARNEGARA';
        $reportSubtitle = 'Sumber Data: SIMAK DINDIKPORA Banjarnegara • Diunduh: '.now()->format('d/m/Y H:i').' WIB';

        $headers = [
            'No',
            'NPSN',
            'Nama Satuan Pendidikan',
            'Jenjang',
            'Status',
            'Akreditasi',
            'Kecamatan',
            'Kode Kecamatan',
            'Alamat',
            'Kepala Sekolah',
            'Telepon',
        ];

        $rows = [];
        $no = 1;
        foreach ($sekolahList as $s) {
            $rows[] = [
                $no++,
                (string) $s->npsn,
                $s->nama_sekolah,
                $s->jenjang,
                $s->status,
                $s->akreditasi,
                $s->kecamatan->nama ?? '-',
                (string) ($s->kecamatan->kode ?? '-'),
                $s->alamat ?? '-',
                $s->kepala_sekolah ?? '-',
                $s->telepon ? (string) $s->telepon : '-',
            ];
        }

        return $this->excelExporter->downloadXlsx(
            filename: $filename,
            sheetTitle: $sheetTitle,
            headers: $headers,
            rows: $rows,
            reportTitle: $reportTitle,
            reportSubtitle: $reportSubtitle
        );
    }

    /**
     * Unduh Data Partisipasi Siswa (Dapodik) dalam format Microsoft Excel (.xlsx)
     */
    public function unduhSiswaCsv(Request $request): StreamedResponse
    {
        $tahun = $this->resolveTahun($request);
        $gender = $request->input('gender', 'all');

        $siswaList = PartisipasiSiswa::with(['satuanPendidikan.kecamatan'])
            ->where('tahun', $tahun)
            ->get();

        $genderLabel = $gender === 'L' ? ' (LAKI-LAKI)' : ($gender === 'P' ? ' (PEREMPUAN)' : '');
        $filename = "data_partisipasi_siswa_{$tahun}".($gender !== 'all' ? '_'.strtolower($gender) : '').'.xlsx';
        $sheetTitle = "Partisipasi Siswa {$tahun}";
        $reportTitle = 'DATA PARTISIPASI SISWA (DAPODIK)'.$genderLabel.' KABUPATEN BANJARNEGARA';
        $reportSubtitle = "Tahun Anggaran {$tahun} • Sumber Data: SIMAK DINDIKPORA Banjarnegara • Diunduh: ".now()->format('d/m/Y H:i').' WIB';

        if ($gender === 'L') {
            $headers = [
                'No', 'NPSN', 'Nama Sekolah', 'Jenjang', 'Kecamatan', 'Tahun Ajaran', 'Tahun',
                'Siswa Laki-laki (L)', 'Usia Sesuai (L)', 'Jumlah Rombel',
            ];
            $rows = [];
            $no = 1;
            foreach ($siswaList as $s) {
                $rows[] = [
                    $no++,
                    isset($s->satuanPendidikan->npsn) ? (string) $s->satuanPendidikan->npsn : '-',
                    $s->satuanPendidikan->nama_sekolah ?? '-',
                    $s->jenjang,
                    $s->satuanPendidikan->kecamatan->nama ?? '-',
                    $s->tahun_ajaran,
                    $s->tahun,
                    (int) $s->siswa_l,
                    (int) $s->siswa_usia_sesuai_l,
                    (int) $s->jumlah_rombel,
                ];
            }
        } elseif ($gender === 'P') {
            $headers = [
                'No', 'NPSN', 'Nama Sekolah', 'Jenjang', 'Kecamatan', 'Tahun Ajaran', 'Tahun',
                'Siswi Perempuan (P)', 'Usia Sesuai (P)', 'Jumlah Rombel',
            ];
            $rows = [];
            $no = 1;
            foreach ($siswaList as $s) {
                $rows[] = [
                    $no++,
                    isset($s->satuanPendidikan->npsn) ? (string) $s->satuanPendidikan->npsn : '-',
                    $s->satuanPendidikan->nama_sekolah ?? '-',
                    $s->jenjang,
                    $s->satuanPendidikan->kecamatan->nama ?? '-',
                    $s->tahun_ajaran,
                    $s->tahun,
                    (int) $s->siswa_p,
                    (int) $s->siswa_usia_sesuai_p,
                    (int) $s->jumlah_rombel,
                ];
            }
        } else {
            $headers = [
                'No',
                'NPSN',
                'Nama Sekolah',
                'Jenjang',
                'Kecamatan',
                'Tahun Ajaran',
                'Tahun',
                'Total Siswa Semua Usia',
                'Siswa (L)',
                'Siswa (P)',
                'Siswa Usia Sesuai Rombel',
                'Usia Sesuai (L)',
                'Usia Sesuai (P)',
                'Usia 5-6 Th',
                'Usia 7-12 Th',
                'Usia 13-15 Th',
                'Usia 16-18 Th',
                'Jumlah Rombel',
            ];

            $rows = [];
            $no = 1;
            foreach ($siswaList as $s) {
                $rows[] = [
                    $no++,
                    isset($s->satuanPendidikan->npsn) ? (string) $s->satuanPendidikan->npsn : '-',
                    $s->satuanPendidikan->nama_sekolah ?? '-',
                    $s->jenjang,
                    $s->satuanPendidikan->kecamatan->nama ?? '-',
                    $s->tahun_ajaran,
                    $s->tahun,
                    (int) $s->total_siswa_semua_usia,
                    (int) $s->siswa_l,
                    (int) $s->siswa_p,
                    (int) $s->siswa_usia_sesuai,
                    (int) $s->siswa_usia_sesuai_l,
                    (int) $s->siswa_usia_sesuai_p,
                    (int) $s->siswa_usia_5_6,
                    (int) $s->siswa_usia_7_12,
                    (int) $s->siswa_usia_13_15,
                    (int) $s->siswa_usia_16_18,
                    (int) $s->jumlah_rombel,
                ];
            }
        }

        return $this->excelExporter->downloadXlsx(
            filename: $filename,
            sheetTitle: $sheetTitle,
            headers: $headers,
            rows: $rows,
            reportTitle: $reportTitle,
            reportSubtitle: $reportSubtitle
        );
    }

    /**
     * Unduh Data Guru & Pendidik dalam format Microsoft Excel (.xlsx)
     */
    public function unduhGuruCsv(Request $request): StreamedResponse
    {
        $query = GuruPendidik::with(['satuanPendidikan.kecamatan'])->orderBy('nama');

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->input('jenis_kelamin'));
        }
        if ($request->filled('kecamatan_id')) {
            $query->where('kecamatan_id', $request->input('kecamatan_id'));
        }
        if ($request->filled('jenjang')) {
            $jenjang = $request->input('jenjang');
            $query->whereHas('satuanPendidikan', fn ($q) => $q->where('jenjang', $jenjang));
        }
        if ($request->filled('status_kepegawaian')) {
            $query->where('status_kepegawaian', $request->input('status_kepegawaian'));
        }
        if ($request->filled('sertifikasi')) {
            $query->where('sudah_sertifikasi', (bool) $request->input('sertifikasi'));
        }

        $guruList = $query->get();

        $genderLabel = $request->input('jenis_kelamin') === 'L' ? ' Laki-laki' : ($request->input('jenis_kelamin') === 'P' ? ' Perempuan' : '');
        $filename = 'data_guru_pendidik_banjarnegara'.($request->filled('jenis_kelamin') ? '_'.strtolower($request->input('jenis_kelamin')) : '').'.xlsx';
        $sheetTitle = 'Guru & Pendidik';
        $reportTitle = 'DATA GURU & TENAGA PENDIDIK'.strtoupper($genderLabel).' KABUPATEN BANJARNEGARA';
        $reportSubtitle = 'Sumber Data: SIMAK DINDIKPORA Banjarnegara • Diunduh: '.now()->format('d/m/Y H:i').' WIB';

        $headers = [
            'No',
            'Nama Lengkap',
            'NIP',
            'NUPTK',
            'L/P',
            'Satuan Pendidikan',
            'NPSN',
            'Jenjang',
            'Kecamatan',
            'Status Kepegawaian',
            'Sertifikasi Pendidik',
            'Bidang Studi',
        ];

        $rows = [];
        $no = 1;
        foreach ($guruList as $g) {
            $rows[] = [
                $no++,
                $g->nama,
                $g->nip ? (string) $g->nip : '-',
                $g->nuptk ? (string) $g->nuptk : '-',
                $g->jenis_kelamin,
                $g->satuanPendidikan->nama_sekolah ?? '-',
                isset($g->satuanPendidikan->npsn) ? (string) $g->satuanPendidikan->npsn : '-',
                $g->satuanPendidikan->jenjang ?? '-',
                $g->satuanPendidikan->kecamatan->nama ?? '-',
                $g->status_kepegawaian,
                $g->sudah_sertifikasi ? 'Sudah Sertifikasi' : 'Belum Sertifikasi',
                $g->bidang_studi ?? '-',
            ];
        }

        return $this->excelExporter->downloadXlsx(
            filename: $filename,
            sheetTitle: $sheetTitle,
            headers: $headers,
            rows: $rows,
            reportTitle: $reportTitle,
            reportSubtitle: $reportSubtitle
        );
    }
}
