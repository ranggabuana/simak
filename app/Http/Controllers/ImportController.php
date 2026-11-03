<?php

namespace App\Http\Controllers;

use App\Models\PendudukUsiaSekolah;
use App\Models\PrestasiSiswa;
use App\Models\SatuanPendidikan;
use App\Models\WilayahKecamatan;
use App\Services\ExcelExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ImportController extends Controller
{
    public function __construct(
        protected ExcelExporter $excelExporter
    ) {}

    /**
     * Dialihkan ke Tujuan 1 (Penduduk) karena menu dan form import telah diintegrasikan langsung ke masing-masing halaman tujuan.
     */
    public function index(Request $request): RedirectResponse
    {
        $params = $request->has('tahun') ? ['tahun' => $request->input('tahun')] : [];

        return redirect()->route('tujuan1.index', $params);
    }

    /**
     * Import Data Kependudukan (Excel .xlsx saja)
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

        if ($extension !== 'xlsx') {
            return back()->withErrors(['file_penduduk' => 'Format file tidak didukung. Modul ini hanya menerima file Microsoft Excel (.xlsx).']);
        }

        $count = $this->parseXlsxPenduduk($file->getRealPath(), $tahun);

        if ($count === 0) {
            return back()->with('info', 'File berhasil diproses, namun tidak ada baris data kecamatan yang cocok untuk tahun '.$tahun.'. Pastikan format kolom sesuai dengan template Excel.');
        }

        return back()->with('success', "Berhasil mengimpor data kependudukan untuk {$count} kecamatan pada Tahun Kinerja {$tahun}.");
    }

    /**
     * Import Data Prestasi Siswa (Excel .xlsx saja)
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

        if ($extension !== 'xlsx') {
            return back()->withErrors(['file_prestasi' => 'Format file tidak didukung. Modul ini hanya menerima file Microsoft Excel (.xlsx). Silakan gunakan template Excel yang telah disediakan.']);
        }

        $count = $this->parseXlsxPrestasi($file->getRealPath(), $tahunDefault);

        if ($count === 0) {
            return back()->with('info', 'Tidak ada baris data prestasi yang berhasil diimpor. Periksa kembali struktur kolom pada file Excel.');
        }

        return back()->with('success', "Berhasil mengimpor {$count} data prestasi siswa.");
    }

    /**
     * Unduh Template Excel (.xlsx) atau CSV Siap Pakai
     */
    public function downloadTemplate(string $type): StreamedResponse
    {
        if ($type === 'penduduk') {
            $headers = [
                'kode_kecamatan',
                'nama_kecamatan',
                'tahun',
                'usia_0_4_l',
                'usia_0_4_p',
                'usia_5_6_l',
                'usia_5_6_p',
                'usia_7_12_l',
                'usia_7_12_p',
                'usia_13_15_l',
                'usia_13_15_p',
                'usia_16_18_l',
                'usia_16_18_p',
                'usia_18_plus_l',
                'usia_18_plus_p',
            ];

            $kecamatan = WilayahKecamatan::orderBy('kode')->get();
            $tahunAktif = (int) date('Y');
            $rows = [];

            foreach ($kecamatan as $k) {
                $existing = PendudukUsiaSekolah::where('kecamatan_id', $k->id)
                    ->where('tahun', $tahunAktif)
                    ->first();

                $rows[] = [
                    $k->kode,
                    $k->nama,
                    $tahunAktif,
                    $existing ? $existing->usia_0_4_l : rand(1500, 2300),
                    $existing ? $existing->usia_0_4_p : rand(1500, 2200),
                    $existing ? $existing->usia_5_6_l : rand(600, 1000),
                    $existing ? $existing->usia_5_6_p : rand(600, 1000),
                    $existing ? $existing->usia_7_12_l : rand(1900, 2600),
                    $existing ? $existing->usia_7_12_p : rand(1900, 2600),
                    $existing ? $existing->usia_13_15_l : rand(1000, 1400),
                    $existing ? $existing->usia_13_15_p : rand(1000, 1400),
                    $existing ? $existing->usia_16_18_l : rand(900, 1300),
                    $existing ? $existing->usia_16_18_p : rand(900, 1300),
                    $existing ? $existing->usia_18_plus_l : rand(9000, 16000),
                    $existing ? $existing->usia_18_plus_p : rand(9000, 16000),
                ];
            }

            return $this->excelExporter->downloadXlsx(
                'template_penduduk_usia_sekolah_banjarnegara.xlsx',
                'Template Penduduk',
                $headers,
                $rows
            );
        }

        if ($type === 'prestasi') {
            $headers = [
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
            ];

            $rows = [
                [
                    'Budi Santoso',
                    '0081234567',
                    'L',
                    'Olimpiade Sains Nasional (OSN) Matematika',
                    'Sains & Akademik',
                    'Kabupaten',
                    'Juara 1',
                    (int) date('Y'),
                    'SMP Negeri 1 Banjarnegara',
                    'Balai Pengembangan Talenta Indonesia',
                    'Maju ke tingkat Provinsi',
                ],
                [
                    'Siti Aisyah',
                    '0097654321',
                    'P',
                    'Festival Lomba Seni Siswa Nasional (FLS2N) Tari Tradisional',
                    'Seni & Budaya',
                    'Provinsi',
                    'Juara 2',
                    (int) date('Y'),
                    'SD Negeri 1 Krandegan',
                    'Dinas Pendidikan Provinsi Jawa Tengah',
                    'Piala & Piagam Penghargaan',
                ],
            ];

            return $this->excelExporter->downloadXlsx(
                'template_prestasi_siswa_banjarnegara.xlsx',
                'Template Prestasi',
                $headers,
                $rows
            );
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($type === 'sekolah') {
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
        } else {
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

            $hasGenderCols = isset($headerMap['usia_7_12_l']) || isset($headerMap['usia_0_4_l']) || isset($headerMap['7_12_l']) || isset($headerMap['sd_l']);

            if ($hasGenderCols) {
                $u0_4_l = (int) ($row[$headerMap['usia_0_4_l'] ?? ($headerMap['0_4_l'] ?? -1)] ?? 0);
                $u0_4_p = (int) ($row[$headerMap['usia_0_4_p'] ?? ($headerMap['0_4_p'] ?? -1)] ?? 0);
                $u5_6_l = (int) ($row[$headerMap['usia_5_6_l'] ?? ($headerMap['5_6_l'] ?? ($headerMap['paud_l'] ?? -1))] ?? 0);
                $u5_6_p = (int) ($row[$headerMap['usia_5_6_p'] ?? ($headerMap['5_6_p'] ?? ($headerMap['paud_p'] ?? -1))] ?? 0);
                $u7_12_l = (int) ($row[$headerMap['usia_7_12_l'] ?? ($headerMap['7_12_l'] ?? ($headerMap['sd_l'] ?? -1))] ?? 0);
                $u7_12_p = (int) ($row[$headerMap['usia_7_12_p'] ?? ($headerMap['7_12_p'] ?? ($headerMap['sd_p'] ?? -1))] ?? 0);
                $u13_15_l = (int) ($row[$headerMap['usia_13_15_l'] ?? ($headerMap['13_15_l'] ?? ($headerMap['smp_l'] ?? -1))] ?? 0);
                $u13_15_p = (int) ($row[$headerMap['usia_13_15_p'] ?? ($headerMap['13_15_p'] ?? ($headerMap['smp_p'] ?? -1))] ?? 0);
                $u16_18_l = (int) ($row[$headerMap['usia_16_18_l'] ?? ($headerMap['16_18_l'] ?? ($headerMap['sma_l'] ?? -1))] ?? 0);
                $u16_18_p = (int) ($row[$headerMap['usia_16_18_p'] ?? ($headerMap['16_18_p'] ?? ($headerMap['sma_p'] ?? -1))] ?? 0);
                $u18p_l = (int) ($row[$headerMap['usia_18_plus_l'] ?? ($headerMap['18_plus_l'] ?? ($headerMap['18p_l'] ?? -1))] ?? 0);
                $u18p_p = (int) ($row[$headerMap['usia_18_plus_p'] ?? ($headerMap['18_plus_p'] ?? ($headerMap['18p_p'] ?? -1))] ?? 0);

                $u0_4 = $u0_4_l + $u0_4_p;
                $u5_6 = $u5_6_l + $u5_6_p;
                $u7_12 = $u7_12_l + $u7_12_p;
                $u13_15 = $u13_15_l + $u13_15_p;
                $u16_18 = $u16_18_l + $u16_18_p;
                $u18_plus = $u18p_l + $u18p_p;
            } else {
                $u0_4 = (int) ($row[$headerMap['usia_0_4'] ?? 3] ?? 0);
                $u5_6 = (int) ($row[$headerMap['usia_5_6'] ?? 4] ?? 0);
                $u7_12 = (int) ($row[$headerMap['usia_7_12'] ?? 5] ?? 0);
                $u13_15 = (int) ($row[$headerMap['usia_13_15'] ?? 6] ?? 0);
                $u16_18 = (int) ($row[$headerMap['usia_16_18'] ?? 7] ?? 0);
                $u18_plus = (int) ($row[$headerMap['usia_18_plus'] ?? 8] ?? 0);

                $u0_4_l = (int) round($u0_4 * 0.51);
                $u0_4_p = $u0_4 - $u0_4_l;
                $u5_6_l = (int) round($u5_6 * 0.51);
                $u5_6_p = $u5_6 - $u5_6_l;
                $u7_12_l = (int) round($u7_12 * 0.51);
                $u7_12_p = $u7_12 - $u7_12_l;
                $u13_15_l = (int) round($u13_15 * 0.51);
                $u13_15_p = $u13_15 - $u13_15_l;
                $u16_18_l = (int) round($u16_18 * 0.51);
                $u16_18_p = $u16_18 - $u16_18_l;
                $u18p_l = (int) round($u18_plus * 0.51);
                $u18p_p = $u18_plus - $u18p_l;
            }

            $total = $u0_4 + $u5_6 + $u7_12 + $u13_15 + $u16_18 + $u18_plus;

            PendudukUsiaSekolah::updateOrCreate(
                [
                    'kecamatan_id' => $kecamatan->id,
                    'tahun' => $rowTahun,
                ],
                [
                    'usia_0_4_l' => $u0_4_l,
                    'usia_0_4_p' => $u0_4_p,
                    'usia_0_4_total' => $u0_4,
                    'usia_5_6_l' => $u5_6_l,
                    'usia_5_6_p' => $u5_6_p,
                    'usia_5_6_total' => $u5_6,
                    'usia_7_12_l' => $u7_12_l,
                    'usia_7_12_p' => $u7_12_p,
                    'usia_7_12_total' => $u7_12,
                    'usia_13_15_l' => $u13_15_l,
                    'usia_13_15_p' => $u13_15_p,
                    'usia_13_15_total' => $u13_15,
                    'usia_16_18_l' => $u16_18_l,
                    'usia_16_18_p' => $u16_18_p,
                    'usia_16_18_total' => $u16_18,
                    'usia_18_plus_l' => $u18p_l,
                    'usia_18_plus_p' => $u18p_p,
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
     * Parser XLSX Penduduk (Mendukung Template SIMAK maupun Format DUKCAPIL/Kemendagri)
     */
    private function parseXlsxPenduduk(string $filepath, int $tahun): int
    {
        $zip = new ZipArchive;
        if ($zip->open($filepath) !== true) {
            return 0;
        }

        $sharedStrings = $this->extractSharedStrings($zip);

        // Cari sheet yang tersedia (biasanya sheet1 atau sheet2)
        $candidateSheets = ['xl/worksheets/sheet1.xml', 'xl/worksheets/sheet2.xml'];
        $count = 0;

        foreach ($candidateSheets as $sheetName) {
            if ($zip->locateName($sheetName) === false) {
                continue;
            }

            $sheetXml = simplexml_load_string($zip->getFromName($sheetName));
            if (! $sheetXml || ! isset($sheetXml->sheetData->row)) {
                continue;
            }

            $headerColMap = [];
            $sheetProcessed = 0;

            foreach ($sheetXml->sheetData->row as $row) {
                $rData = [];
                foreach ($row->c as $c) {
                    $ref = preg_replace('/[0-9]+/', '', (string) $c['r']);
                    $rData[$ref] = $this->getCellValue($c, $sharedStrings);
                }

                // Cek jika baris ini adalah baris header template
                if (empty($headerColMap)) {
                    $possibleHeader = false;
                    foreach ($rData as $colLetter => $val) {
                        $clean = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($val)));
                        $clean = trim(preg_replace('/_+/', '_', $clean), '_');
                        if (in_array($clean, ['kode_kecamatan', 'nama_kecamatan', 'usia_7_12', 'usia_7_12_l', 'usia_0_4', 'usia_0_4_l', 'kecamatan'])) {
                            $possibleHeader = true;
                            break;
                        }
                    }

                    if ($possibleHeader) {
                        foreach ($rData as $colLetter => $val) {
                            $clean = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($val)));
                            $clean = trim(preg_replace('/_+/', '_', $clean), '_');
                            $headerColMap[$clean] = $colLetter;
                        }

                        continue;
                    }
                }

                // 1. Mode Template SIMAK (jika terdeteksi header nama/kode)
                if (! empty($headerColMap) && (isset($headerColMap['kode_kecamatan']) || isset($headerColMap['nama_kecamatan']))) {
                    $colKode = $headerColMap['kode_kecamatan'] ?? null;
                    $colNama = $headerColMap['nama_kecamatan'] ?? ($headerColMap['kecamatan'] ?? null);

                    $kode = $colKode ? trim($rData[$colKode] ?? '') : '';
                    $nama = $colNama ? trim($rData[$colNama] ?? '') : '';

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

                    $colTahun = $headerColMap['tahun'] ?? null;
                    $rowTahun = $colTahun && ! empty($rData[$colTahun]) ? (int) $rData[$colTahun] : $tahun;
                    if ($rowTahun <= 0) {
                        $rowTahun = $tahun;
                    }

                    $hasGenderCols = isset($headerColMap['usia_7_12_l']) || isset($headerColMap['usia_0_4_l']) || isset($headerColMap['7_12_l']) || isset($headerColMap['sd_l']);

                    if ($hasGenderCols) {
                        $u0_4_l = (int) ($rData[$headerColMap['usia_0_4_l'] ?? ($headerColMap['0_4_l'] ?? '')] ?? 0);
                        $u0_4_p = (int) ($rData[$headerColMap['usia_0_4_p'] ?? ($headerColMap['0_4_p'] ?? '')] ?? 0);

                        $u5_6_l = (int) ($rData[$headerColMap['usia_5_6_l'] ?? ($headerColMap['5_6_l'] ?? ($headerColMap['paud_l'] ?? ''))] ?? 0);
                        $u5_6_p = (int) ($rData[$headerColMap['usia_5_6_p'] ?? ($headerColMap['5_6_p'] ?? ($headerColMap['paud_p'] ?? ''))] ?? 0);

                        $u7_12_l = (int) ($rData[$headerColMap['usia_7_12_l'] ?? ($headerColMap['7_12_l'] ?? ($headerColMap['sd_l'] ?? ''))] ?? 0);
                        $u7_12_p = (int) ($rData[$headerColMap['usia_7_12_p'] ?? ($headerColMap['7_12_p'] ?? ($headerColMap['sd_p'] ?? ''))] ?? 0);

                        $u13_15_l = (int) ($rData[$headerColMap['usia_13_15_l'] ?? ($headerColMap['13_15_l'] ?? ($headerColMap['smp_l'] ?? ''))] ?? 0);
                        $u13_15_p = (int) ($rData[$headerColMap['usia_13_15_p'] ?? ($headerColMap['13_15_p'] ?? ($headerColMap['smp_p'] ?? ''))] ?? 0);

                        $u16_18_l = (int) ($rData[$headerColMap['usia_16_18_l'] ?? ($headerColMap['16_18_l'] ?? ($headerColMap['sma_l'] ?? ''))] ?? 0);
                        $u16_18_p = (int) ($rData[$headerColMap['usia_16_18_p'] ?? ($headerColMap['16_18_p'] ?? ($headerColMap['sma_p'] ?? ''))] ?? 0);

                        $u18p_l = (int) ($rData[$headerColMap['usia_18_plus_l'] ?? ($headerColMap['18_plus_l'] ?? ($headerColMap['18p_l'] ?? ''))] ?? 0);
                        $u18p_p = (int) ($rData[$headerColMap['usia_18_plus_p'] ?? ($headerColMap['18_plus_p'] ?? ($headerColMap['18p_p'] ?? ''))] ?? 0);

                        $u0_4 = $u0_4_l + $u0_4_p;
                        $u5_6 = $u5_6_l + $u5_6_p;
                        $u7_12 = $u7_12_l + $u7_12_p;
                        $u13_15 = $u13_15_l + $u13_15_p;
                        $u16_18 = $u16_18_l + $u16_18_p;
                        $u18_plus = $u18p_l + $u18p_p;
                    } else {
                        $u0_4 = (int) ($rData[$headerColMap['usia_0_4'] ?? ''] ?? 0);
                        $u5_6 = (int) ($rData[$headerColMap['usia_5_6'] ?? ''] ?? 0);
                        $u7_12 = (int) ($rData[$headerColMap['usia_7_12'] ?? ''] ?? 0);
                        $u13_15 = (int) ($rData[$headerColMap['usia_13_15'] ?? ''] ?? 0);
                        $u16_18 = (int) ($rData[$headerColMap['usia_16_18'] ?? ''] ?? 0);
                        $u18_plus = (int) ($rData[$headerColMap['usia_18_plus'] ?? ''] ?? 0);

                        $u0_4_l = (int) round($u0_4 * 0.51);
                        $u0_4_p = $u0_4 - $u0_4_l;
                        $u5_6_l = (int) round($u5_6 * 0.51);
                        $u5_6_p = $u5_6 - $u5_6_l;
                        $u7_12_l = (int) round($u7_12 * 0.51);
                        $u7_12_p = $u7_12 - $u7_12_l;
                        $u13_15_l = (int) round($u13_15 * 0.51);
                        $u13_15_p = $u13_15 - $u13_15_l;
                        $u16_18_l = (int) round($u16_18 * 0.51);
                        $u16_18_p = $u16_18 - $u16_18_l;
                        $u18p_l = (int) round($u18_plus * 0.51);
                        $u18p_p = $u18_plus - $u18p_l;
                    }

                    $total = $u0_4 + $u5_6 + $u7_12 + $u13_15 + $u16_18 + $u18_plus;

                    PendudukUsiaSekolah::updateOrCreate(
                        [
                            'kecamatan_id' => $kecamatan->id,
                            'tahun' => $rowTahun,
                        ],
                        [
                            'usia_0_4_l' => $u0_4_l,
                            'usia_0_4_p' => $u0_4_p,
                            'usia_0_4_total' => $u0_4,
                            'usia_5_6_l' => $u5_6_l,
                            'usia_5_6_p' => $u5_6_p,
                            'usia_5_6_total' => $u5_6,
                            'usia_7_12_l' => $u7_12_l,
                            'usia_7_12_p' => $u7_12_p,
                            'usia_7_12_total' => $u7_12,
                            'usia_13_15_l' => $u13_15_l,
                            'usia_13_15_p' => $u13_15_p,
                            'usia_13_15_total' => $u13_15,
                            'usia_16_18_l' => $u16_18_l,
                            'usia_16_18_p' => $u16_18_p,
                            'usia_16_18_total' => $u16_18,
                            'usia_18_plus_l' => $u18p_l,
                            'usia_18_plus_p' => $u18p_p,
                            'usia_18_plus_total' => $u18_plus,
                            'total_penduduk' => $total,
                        ]
                    );

                    $sheetProcessed++;

                    continue;
                }

                // 2. Mode Format DUKCAPIL / Kemendagri Banjarnegara (Kode 3304xx di Kolom B)
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
                        $est18PlusL = (int) round($est18Plus * 0.505);
                        $est18PlusP = $est18Plus - $est18PlusL;

                        PendudukUsiaSekolah::updateOrCreate(
                            [
                                'kecamatan_id' => $kecamatan->id,
                                'tahun' => $tahun,
                            ],
                            [
                                'usia_0_4_l' => $p0_4_l,
                                'usia_0_4_p' => $p0_4_p,
                                'usia_0_4_total' => $p0_4_l + $p0_4_p,
                                'usia_5_6_l' => $p5_6_l,
                                'usia_5_6_p' => $p5_6_p,
                                'usia_5_6_total' => $p5_6_l + $p5_6_p,
                                'usia_7_12_l' => $p7_12_l,
                                'usia_7_12_p' => $p7_12_p,
                                'usia_7_12_total' => $p7_12_l + $p7_12_p,
                                'usia_13_15_l' => $p13_15_l,
                                'usia_13_15_p' => $p13_15_p,
                                'usia_13_15_total' => $p13_15_l + $p13_15_p,
                                'usia_16_18_l' => $p16_18_l,
                                'usia_16_18_p' => $p16_18_p,
                                'usia_16_18_total' => $p16_18_l + $p16_18_p,
                                'usia_18_plus_l' => $est18PlusL,
                                'usia_18_plus_p' => $est18PlusP,
                                'usia_18_plus_total' => $est18Plus,
                                'total_penduduk' => $estTotal,
                            ]
                        );

                        $sheetProcessed++;
                    }
                }
            }

            if ($sheetProcessed > 0) {
                $count = $sheetProcessed;
                break;
            }
        }

        $zip->close();

        return $count;
    }

    /**
     * Parser XLSX Prestasi Siswa
     */
    private function parseXlsxPrestasi(string $filepath, int $tahunDefault): int
    {
        $zip = new ZipArchive;
        if ($zip->open($filepath) !== true) {
            return 0;
        }

        $sharedStrings = $this->extractSharedStrings($zip);

        $sheetName = 'xl/worksheets/sheet1.xml';
        if ($zip->locateName($sheetName) === false) {
            $sheetName = 'xl/worksheets/sheet2.xml';
        }

        if ($zip->locateName($sheetName) === false) {
            $zip->close();

            return 0;
        }

        $sheetXml = simplexml_load_string($zip->getFromName($sheetName));
        if (! $sheetXml || ! isset($sheetXml->sheetData->row)) {
            $zip->close();

            return 0;
        }

        $headerColMap = [];
        $count = 0;
        $defaultKecamatan = WilayahKecamatan::first();

        foreach ($sheetXml->sheetData->row as $row) {
            $rData = [];
            foreach ($row->c as $c) {
                $ref = preg_replace('/[0-9]+/', '', (string) $c['r']);
                $rData[$ref] = $this->getCellValue($c, $sharedStrings);
            }

            // Temukan baris header
            if (empty($headerColMap)) {
                $possibleHeader = false;
                foreach ($rData as $colLetter => $val) {
                    $clean = strtolower(trim($val));
                    if (in_array($clean, ['nama_siswa', 'nama siswa', 'nisn', 'nama_kejuaraan', 'kejuaraan'])) {
                        $possibleHeader = true;
                        break;
                    }
                }

                if ($possibleHeader) {
                    foreach ($rData as $colLetter => $val) {
                        $clean = strtolower(trim($val));
                        $headerColMap[$clean] = $colLetter;
                    }

                    continue;
                }
            }

            // Ambil nilai kolom
            $colNamaSiswa = $headerColMap['nama_siswa'] ?? ($headerColMap['nama siswa'] ?? 'A');
            $colNisn = $headerColMap['nisn'] ?? 'B';
            $colJk = $headerColMap['jenis_kelamin'] ?? ($headerColMap['jk'] ?? 'C');
            $colKejuaraan = $headerColMap['nama_kejuaraan'] ?? ($headerColMap['kejuaraan'] ?? 'D');
            $colBidang = $headerColMap['bidang'] ?? 'E';
            $colTingkat = $headerColMap['tingkat'] ?? 'F';
            $colPeringkat = $headerColMap['peringkat'] ?? 'G';
            $colTahun = $headerColMap['tahun'] ?? 'H';
            $colSekolah = $headerColMap['nama_sekolah'] ?? ($headerColMap['sekolah'] ?? 'I');
            $colPenyelenggara = $headerColMap['penyelenggara'] ?? 'J';
            $colKeterangan = $headerColMap['keterangan'] ?? 'K';

            $namaSiswa = trim($rData[$colNamaSiswa] ?? '');
            $nisn = trim($rData[$colNisn] ?? '');
            $rawJk = strtoupper(trim($rData[$colJk] ?? 'L'));
            $jenisKelamin = in_array($rawJk, ['L', 'P']) ? $rawJk : 'L';
            $namaKejuaraan = trim($rData[$colKejuaraan] ?? '');
            $bidang = trim($rData[$colBidang] ?? 'Sains & Akademik');
            $tingkat = trim($rData[$colTingkat] ?? 'Kabupaten');
            $peringkat = trim($rData[$colPeringkat] ?? 'Juara 1');
            $tahun = ! empty($rData[$colTahun]) ? (int) $rData[$colTahun] : $tahunDefault;
            $namaSekolah = trim($rData[$colSekolah] ?? '');
            $penyelenggara = trim($rData[$colPenyelenggara] ?? 'Dinas Pendidikan');
            $keterangan = trim($rData[$colKeterangan] ?? '');

            // Validasi baris data
            if (empty($namaSiswa) || empty($namaKejuaraan) || strtolower($namaSiswa) === 'nama_siswa') {
                continue;
            }

            // Temukan atau buat satuan pendidikan
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

        $zip->close();

        return $count;
    }

    /**
     * Ambil nilai sel dari node SimpleXMLElement
     *
     * @param  array<int, string>  $sharedStrings
     */
    private function getCellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        if ($type === 's') {
            $idx = (int) $cell->v;

            return $sharedStrings[$idx] ?? '';
        }

        if ($type === 'inlineStr' && isset($cell->is->t)) {
            return (string) $cell->is->t;
        }

        if (isset($cell->v)) {
            return (string) $cell->v;
        }

        return '';
    }

    /**
     * Ekstrak tabel shared strings dari arsip OpenXML
     *
     * @return array<int, string>
     */
    private function extractSharedStrings(ZipArchive $zip): array
    {
        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $stringsXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            if ($stringsXml && isset($stringsXml->si)) {
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
        }

        return $sharedStrings;
    }
}
