<?php

namespace App\Http\Controllers;

use App\Models\PendudukUsiaSekolah;
use App\Models\PrestasiSiswa;
use App\Models\SatuanPendidikan;
use App\Models\WilayahKecamatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ImportController extends Controller
{
    /**
     * Dialihkan ke Tujuan 1 (Penduduk) karena menu dan form import telah diintegrasikan langsung ke masing-masing halaman tujuan.
     */
    public function index(Request $request): RedirectResponse
    {
        $params = $request->has('tahun') ? ['tahun' => $request->input('tahun')] : [];

        return redirect()->route('tujuan1.index', $params);
    }

    /**
     * Import Data Kependudukan (Excel .xlsx atau .csv)
     */
    public function importPenduduk(Request $request): RedirectResponse
    {
        $request->validate([
            'file_penduduk' => 'required|file|max:25600',
            'tahun' => 'required|integer|min:2020|max:2035',
        ]);

        $file = $request->file('file_penduduk');
        $tahun = (int) $request->input('tahun');
        $extension = strtolower($file->getClientOriginalExtension());
        $count = 0;

        if ($extension === 'csv' || $extension === 'txt') {
            $count = $this->parseCsvPenduduk($file->getRealPath(), $tahun);
        } elseif ($extension === 'xlsx') {
            $count = $this->parseXlsxPenduduk($file->getRealPath(), $tahun);
        } else {
            return back()->withErrors(['file_penduduk' => 'Format file harus berupa Excel (.xlsx) atau CSV (.csv).']);
        }

        if ($count === 0) {
            return back()->with('info', 'File berhasil diproses, namun tidak ada baris data kecamatan yang cocok untuk tahun '.$tahun.'. Pastikan format kolom sesuai dengan template.');
        }

        return back()->with('success', "Berhasil mengimpor data kependudukan untuk {$count} kecamatan pada Tahun Kinerja {$tahun}.");
    }

    /**
     * Import Data Prestasi Siswa (CSV atau XLSX)
     */
    public function importPrestasi(Request $request): RedirectResponse
    {
        $request->validate([
            'file_prestasi' => 'required|file|max:20480',
            'tahun' => 'required|integer|min:2020|max:2035',
        ]);

        $file = $request->file('file_prestasi');
        $tahunDefault = (int) $request->input('tahun');
        $extension = strtolower($file->getClientOriginalExtension());
        $count = 0;

        if ($extension === 'csv' || $extension === 'txt') {
            $count = $this->parseCsvPrestasi($file->getRealPath(), $tahunDefault);
        } else {
            return back()->withErrors(['file_prestasi' => 'Format file prestasi saat ini mendukung CSV (.csv). Silakan gunakan template CSV yang telah disediakan.']);
        }

        if ($count === 0) {
            return back()->with('info', 'Tidak ada baris data prestasi yang berhasil diimpor. Periksa kembali struktur kolom pada file.');
        }

        return back()->with('success', "Berhasil mengimpor {$count} data prestasi siswa.");
    }

    /**
     * Unduh Template CSV Siap Pakai
     */
    public function downloadTemplate(string $type): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($type === 'penduduk') {
            $filename = 'template_penduduk_usia_sekolah_banjarnegara.csv';
            $callback = function () {
                $output = fopen('php://output', 'w');
                // UTF-8 BOM for Excel compatibility
                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, [
                    'kode_kecamatan',
                    'nama_kecamatan',
                    'tahun',
                    'usia_0_4',
                    'usia_5_6',
                    'usia_7_12',
                    'usia_13_15',
                    'usia_16_18',
                    'usia_18_plus',
                ]);

                $kecamatan = WilayahKecamatan::orderBy('kode')->get();
                foreach ($kecamatan as $k) {
                    fputcsv($output, [
                        $k->kode,
                        $k->nama,
                        date('Y'),
                        rand(3000, 4500),
                        rand(1200, 2000),
                        rand(3800, 5200),
                        rand(2000, 2800),
                        rand(1800, 2600),
                        rand(18000, 32000),
                    ]);
                }

                fclose($output);
            };
        } elseif ($type === 'sekolah') {
            $filename = 'template_satuan_pendidikan_banjarnegara.csv';
            $callback = function () {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, [
                    'npsn',
                    'nama_sekolah',
                    'jenjang',
                    'status',
                    'akreditasi',
                    'nama_kecamatan',
                    'alamat',
                    'kepala_sekolah',
                    'telepon',
                ]);

                // Sample rows
                fputcsv($output, [
                    '20300101',
                    'SD Negeri 1 Banjarnegara',
                    'SD',
                    'Negeri',
                    'A',
                    'Banjarnegara',
                    'Jl. Pemuda No. 12',
                    'Drs. H. Sugiyanto, M.Pd.',
                    '0286-591234',
                ]);
                fputcsv($output, [
                    '20300102',
                    'SMP Negeri 1 Bawang',
                    'SMP',
                    'Negeri',
                    'A',
                    'Bawang',
                    'Jl. Raya Bawang No. 45',
                    'Siti Rahayu, S.Pd., M.M.',
                    '0286-592345',
                ]);
                fputcsv($output, [
                    '20300103',
                    'TK Pertiwi Susukan',
                    'TK',
                    'Swasta',
                    'B',
                    'Susukan',
                    'Jl. Desa Susukan',
                    'Sri Wahyuni, S.Pd.',
                    '0286-593456',
                ]);

                fclose($output);
            };
        } elseif ($type === 'siswa') {
            $filename = 'template_partisipasi_siswa_dapodik.csv';
            $callback = function () {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, [
                    'npsn',
                    'nama_sekolah',
                    'tahun_ajaran',
                    'tahun',
                    'total_siswa_semua_usia',
                    'siswa_usia_sesuai',
                    'jumlah_rombel',
                ]);

                fputcsv($output, [
                    '20300101',
                    'SD Negeri 1 Banjarnegara',
                    '2024/2025',
                    date('Y'),
                    '240',
                    '225',
                    '8',
                ]);
                fputcsv($output, [
                    '20300102',
                    'SMP Negeri 1 Bawang',
                    '2024/2025',
                    date('Y'),
                    '420',
                    '395',
                    '14',
                ]);

                fclose($output);
            };
        } elseif ($type === 'guru') {
            $filename = 'template_guru_pendidik_banjarnegara.csv';
            $callback = function () {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, [
                    'nama',
                    'npsn',
                    'nama_sekolah',
                    'nip',
                    'nuptk',
                    'jenis_kelamin',
                    'status_kepegawaian',
                    'sudah_sertifikasi',
                    'bidang_studi',
                ]);

                fputcsv($output, [
                    'Ahmad Fauzi, S.Pd.',
                    '20300101',
                    'SD Negeri 1 Banjarnegara',
                    '198504122010011012',
                    '8451763665200023',
                    'L',
                    'PNS',
                    'Ya',
                    'Guru Kelas',
                ]);
                fputcsv($output, [
                    'Dewi Sartika, S.Pd.Gr.',
                    '20300102',
                    'SMP Negeri 1 Bawang',
                    '199208152022212008',
                    '9234764666300012',
                    'P',
                    'PPPK',
                    'Ya',
                    'Matematika',
                ]);
                fputcsv($output, [
                    'Bambang Triyono, S.Kom.',
                    '20300102',
                    'SMP Negeri 1 Bawang',
                    '',
                    '1234765667100034',
                    'L',
                    'GTT',
                    'Tidak',
                    'Informatika',
                ]);

                fclose($output);
            };
        } else {
            $filename = 'template_prestasi_siswa_banjarnegara.csv';
            $callback = function () {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, [
                    'nama_siswa',
                    'nisn',
                    'jenis_kelamin',
                    'nama_kejuaraan',
                    'bidang',
                    'tingkat',
                    'peringkat',
                    'tahun',
                    'nama_sekolah',
                    'penyelenggara',
                    'keterangan',
                ]);

                // Contoh baris
                fputcsv($output, [
                    'Budi Santoso',
                    '0081234567',
                    'L',
                    'Olimpiade Sains Nasional (OSN) Matematika',
                    'Sains & Akademik',
                    'Kabupaten',
                    'Juara 1',
                    date('Y'),
                    'SMP Negeri 1 Banjarnegara',
                    'Balai Pengembangan Talenta Indonesia',
                    'Maju ke tingkat Provinsi',
                ]);
                fputcsv($output, [
                    'Siti Aisyah',
                    '0097654321',
                    'P',
                    'Festival Lomba Seni Siswa Nasional (FLS2N) Tari Tradisional',
                    'Seni & Budaya',
                    'Provinsi',
                    'Juara 2',
                    date('Y'),
                    'SD Negeri 1 Krandegan',
                    'Dinas Pendidikan Provinsi Jawa Tengah',
                    'Piala & Piagam Penghargaan',
                ]);

                fclose($output);
            };
        }

        $headers['Content-Disposition'] = "attachment; filename=\"{$filename}\"";

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Parser CSV Penduduk
     */
    private function parseCsvPenduduk(string $filepath, int $tahun): int
    {
        $handle = fopen($filepath, 'r');
        if (! $handle) {
            return 0;
        }

        // Detect BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 0, ',');
        if (! $header || count($header) < 4) {
            rewind($handle);
            $header = fgetcsv($handle, 0, ';'); // try semicolon
            $delimiter = ';';
        } else {
            $delimiter = ',';
        }

        $headerMap = [];
        foreach ($header as $idx => $col) {
            $clean = strtolower(trim($col));
            $headerMap[$clean] = $idx;
        }

        $count = 0;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($row) || count($row) < 3) {
                continue;
            }

            $kode = trim($row[$headerMap['kode_kecamatan'] ?? 0] ?? '');
            $nama = trim($row[$headerMap['nama_kecamatan'] ?? 1] ?? '');

            $kecamatan = null;
            if (! empty($kode)) {
                $kecamatan = WilayahKecamatan::where('kode', $kode)->first();
            }
            if (! $kecamatan && ! empty($nama)) {
                $kecamatan = WilayahKecamatan::where('nama', 'like', "%{$nama}%")->first();
            }

            if (! $kecamatan) {
                continue;
            }

            $rowTahun = isset($headerMap['tahun']) ? (int) ($row[$headerMap['tahun']] ?? $tahun) : $tahun;
            if ($rowTahun <= 0) {
                $rowTahun = $tahun;
            }

            $u0_4 = (int) ($row[$headerMap['usia_0_4'] ?? 3] ?? 0);
            $u5_6 = (int) ($row[$headerMap['usia_5_6'] ?? 4] ?? 0);
            $u7_12 = (int) ($row[$headerMap['usia_7_12'] ?? 5] ?? 0);
            $u13_15 = (int) ($row[$headerMap['usia_13_15'] ?? 6] ?? 0);
            $u16_18 = (int) ($row[$headerMap['usia_16_18'] ?? 7] ?? 0);
            $u18_plus = (int) ($row[$headerMap['usia_18_plus'] ?? 8] ?? 0);

            $total = $u0_4 + $u5_6 + $u7_12 + $u13_15 + $u16_18 + $u18_plus;

            PendudukUsiaSekolah::updateOrCreate(
                [
                    'kecamatan_id' => $kecamatan->id,
                    'tahun' => $rowTahun,
                ],
                [
                    'usia_0_4_total' => $u0_4,
                    'usia_5_6_total' => $u5_6,
                    'usia_7_12_total' => $u7_12,
                    'usia_13_15_total' => $u13_15,
                    'usia_16_18_total' => $u16_18,
                    'usia_18_plus_total' => $u18_plus,
                    'total_penduduk' => $total,
                ]
            );

            $count++;
        }

        fclose($handle);

        return $count;
    }

    /**
     * Parser XLSX Penduduk (Kompatibel dengan penduduk.xlsx format BPS/DUKCAPIL maupun Excel standar)
     */
    private function parseXlsxPenduduk(string $filepath, int $tahun): int
    {
        $zip = new ZipArchive;
        if ($zip->open($filepath) !== true) {
            return 0;
        }

        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $stringsXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            foreach ($stringsXml->si as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string) $si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $r) {
                        $text .= (string) $r->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }

        // Cari sheet yang berisi data
        $sheetName = 'xl/worksheets/sheet2.xml';
        if ($zip->locateName($sheetName) === false) {
            $sheetName = 'xl/worksheets/sheet1.xml';
        }

        if ($zip->locateName($sheetName) === false) {
            $zip->close();

            return 0;
        }

        $sheetXml = simplexml_load_string($zip->getFromName($sheetName));
        $count = 0;

        foreach ($sheetXml->sheetData->row as $row) {
            $rData = [];
            foreach ($row->c as $c) {
                $ref = preg_replace('/[0-9]+/', '', (string) $c['r']);
                $type = (string) $c['t'];
                $val = (string) $c->v;
                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $val = $sharedStrings[(int) $val];
                }
                $rData[$ref] = $val;
            }

            // Pola format penduduk.xlsx Kemendagri / DUKCAPIL Banjarnegara (Kode 3304xx di Kolom B)
            $kode = trim($rData['B'] ?? '');
            $nama = trim($rData['C'] ?? '');

            if (preg_match('/^3304\d{2}$/', $kode)) {
                $kecamatan = WilayahKecamatan::where('kode', $kode)->first();
                if (! $kecamatan && ! empty($nama)) {
                    $kecamatan = WilayahKecamatan::where('nama', 'like', "%{$nama}%")->first();
                }

                if ($kecamatan) {
                    $p0_4_l = (int) ($rData['NC'] ?? 0);
                    $p0_4_p = (int) ($rData['ND'] ?? 0);
                    $p5_6_l = (int) ($rData['NE'] ?? 0);
                    $p5_6_p = (int) ($rData['NF'] ?? 0);
                    $p7_12_l = (int) ($rData['NG'] ?? 0);
                    $p7_12_p = (int) ($rData['NH'] ?? 0);
                    $p13_15_l = (int) ($rData['NI'] ?? 0);
                    $p13_15_p = (int) ($rData['NJ'] ?? 0);
                    $p16_18_l = (int) ($rData['NK'] ?? 0);
                    $p16_18_p = (int) ($rData['NL'] ?? 0);

                    $totUsiaSekolah = ($p0_4_l + $p0_4_p) + ($p5_6_l + $p5_6_p) + ($p7_12_l + $p7_12_p) + ($p13_15_l + $p13_15_p) + ($p16_18_l + $p16_18_p);
                    $estTotal = (int) ($totUsiaSekolah * 3.4);
                    $est18Plus = max(0, $estTotal - $totUsiaSekolah);

                    PendudukUsiaSekolah::updateOrCreate(
                        [
                            'kecamatan_id' => $kecamatan->id,
                            'tahun' => $tahun,
                        ],
                        [
                            'usia_0_4_total' => $p0_4_l + $p0_4_p,
                            'usia_5_6_total' => $p5_6_l + $p5_6_p,
                            'usia_7_12_total' => $p7_12_l + $p7_12_p,
                            'usia_13_15_total' => $p13_15_l + $p13_15_p,
                            'usia_16_18_total' => $p16_18_l + $p16_18_p,
                            'usia_18_plus_total' => $est18Plus,
                            'total_penduduk' => $estTotal,
                        ]
                    );

                    $count++;
                }
            }
        }

        $zip->close();

        return $count;
    }

    /**
     * Parser CSV Prestasi Siswa
     */
    private function parseCsvPrestasi(string $filepath, int $tahunDefault): int
    {
        $handle = fopen($filepath, 'r');
        if (! $handle) {
            return 0;
        }

        // BOM detection
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 0, ',');
        if (! $header || count($header) < 4) {
            rewind($handle);
            $header = fgetcsv($handle, 0, ';');
            $delimiter = ';';
        } else {
            $delimiter = ',';
        }

        $headerMap = [];
        foreach ($header as $idx => $col) {
            $headerMap[strtolower(trim($col))] = $idx;
        }

        $count = 0;
        $defaultKecamatan = WilayahKecamatan::first();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($row) || count($row) < 3) {
                continue;
            }

            $namaSiswa = trim($row[$headerMap['nama_siswa'] ?? 0] ?? '');
            $nisn = trim($row[$headerMap['nisn'] ?? 1] ?? '');
            $rawJk = isset($headerMap['jenis_kelamin']) ? strtoupper(trim($row[$headerMap['jenis_kelamin']])) : (isset($headerMap['jk']) ? strtoupper(trim($row[$headerMap['jk']])) : 'L');
            $jenisKelamin = in_array($rawJk, ['L', 'P']) ? $rawJk : 'L';
            $namaKejuaraan = trim($row[$headerMap['nama_kejuaraan'] ?? $headerMap['kejuaraan'] ?? 3] ?? '');
            $bidang = trim($row[$headerMap['bidang'] ?? 4] ?? 'Sains & Akademik');
            $tingkat = trim($row[$headerMap['tingkat'] ?? 5] ?? 'Kabupaten');
            $peringkat = trim($row[$headerMap['peringkat'] ?? 6] ?? 'Juara 1');
            $tahun = isset($headerMap['tahun']) ? (int) ($row[$headerMap['tahun']] ?? $tahunDefault) : $tahunDefault;
            $namaSekolah = trim($row[$headerMap['nama_sekolah'] ?? $headerMap['sekolah'] ?? 8] ?? '');
            $penyelenggara = trim($row[$headerMap['penyelenggara'] ?? 9] ?? 'Dinas Pendidikan');
            $keterangan = trim($row[$headerMap['keterangan'] ?? 10] ?? '');

            if (empty($namaSiswa) || empty($namaKejuaraan)) {
                continue;
            }

            // Find or create school
            $sekolah = null;
            if (! empty($namaSekolah)) {
                $sekolah = SatuanPendidikan::where('nama_sekolah', 'like', "%{$namaSekolah}%")->first();
            }

            if (! $sekolah) {
                $sekolah = SatuanPendidikan::firstOrCreate(
                    ['nama_sekolah' => ! empty($namaSekolah) ? $namaSekolah : 'Sekolah Terdaftar Banjarnegara'],
                    [
                        'npsn' => 'AUTO'.rand(100000, 999999),
                        'bentuk_pendidikan' => 'SMP',
                        'status' => 'Negeri',
                        'kecamatan_id' => $defaultKecamatan ? $defaultKecamatan->id : 1,
                        'akreditasi' => 'A',
                    ]
                );
            }

            PrestasiSiswa::create([
                'nama_siswa' => $namaSiswa,
                'nisn' => $nisn,
                'jenis_kelamin' => $jenisKelamin,
                'nama_kejuaraan' => $namaKejuaraan,
                'bidang' => $bidang,
                'tingkat' => $tingkat,
                'peringkat' => $peringkat,
                'tahun' => $tahun > 0 ? $tahun : $tahunDefault,
                'satuan_pendidikan_id' => $sekolah->id,
                'kecamatan_id' => $sekolah->kecamatan_id,
                'penyelenggara' => $penyelenggara,
                'keterangan' => $keterangan,
            ]);

            $count++;
        }

        fclose($handle);

        return $count;
    }
}
