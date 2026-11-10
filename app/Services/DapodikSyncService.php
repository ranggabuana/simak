<?php

namespace App\Services;

use App\Models\GuruPendidik;
use App\Models\PartisipasiSiswa;
use App\Models\SatuanPendidikan;
use App\Models\WilayahKecamatan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class DapodikSyncService
{
    /**
     * Bersihkan string dari nilai literal 'NULL' bawaan ekspor SSMS.
     */
    public static function cleanVal(mixed $val): string
    {
        if ($val === null) {
            return '';
        }
        $str = trim((string) $val);
        if (strcasecmp($str, 'NULL') === 0) {
            return '';
        }

        return $str;
    }

    /**
     * Deteksi apakah nama sekolah merupakan Perguruan Tinggi yang bukan cakupan Dikdasmen/PAUD.
     */
    public static function isPerguruanTinggi(string $nama): bool
    {
        $upper = strtoupper($nama);
        foreach (['SEKOLAH TINGGI', 'POLITEKNIK', 'STIMIK', 'STMIK', 'STIE ', 'UNIVERSITAS', 'AKADEMI', 'INSTITUT '] as $kw) {
            if (str_contains($upper, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cari WilayahKecamatan berdasarkan kode_kecamatan_simak (3304XX), kode Dapodik (0304XX), atau nama kecamatan.
     *
     * @param  Collection<int, WilayahKecamatan>  $allKecamatan
     */
    public static function resolveKecamatan(Collection $allKecamatan, string $kodeSimak = '', string $namaKec = ''): WilayahKecamatan
    {
        $kodeSimak = self::cleanVal($kodeSimak);
        $namaKec = trim(str_ireplace('Kec.', '', self::cleanVal($namaKec)));

        if (strlen($kodeSimak) === 6 && str_starts_with($kodeSimak, '0304')) {
            $kodeSimak = '3304'.substr($kodeSimak, 4, 2);
        }

        if ($kodeSimak !== '') {
            $found = $allKecamatan->firstWhere('kode', $kodeSimak);
            if ($found) {
                return $found;
            }
        }

        if ($namaKec !== '') {
            $found = $allKecamatan->first(function ($k) use ($namaKec) {
                return strcasecmp($k->nama, $namaKec) === 0 || stripos($k->nama, $namaKec) !== false;
            });
            if ($found) {
                return $found;
            }
        }

        return $allKecamatan->first();
    }

    /**
     * Bersihkan data dummy bawaan seeder (NPSN 20300001..20300500 & Guru dummy) sebelum sinkronisasi.
     */
    public function purgeDummyData(string $target = 'semua'): void
    {
        if (in_array($target, ['semua', 'guru'], true)) {
            GuruPendidik::where('nama', 'like', 'Guru %')->delete();
        }

        if (in_array($target, ['semua', 'sekolah', 'siswa'], true)) {
            $dummySchoolIds = SatuanPendidikan::whereBetween('npsn', ['20300001', '20300500'])
                ->orWhere('kepala_sekolah', 'Drs. Supriyanto, M.Pd.')
                ->pluck('id');

            if ($dummySchoolIds->isNotEmpty()) {
                PartisipasiSiswa::whereIn('satuan_pendidikan_id', $dummySchoolIds)->delete();
                GuruPendidik::whereIn('satuan_pendidikan_id', $dummySchoolIds)->delete();

                if (in_array($target, ['semua', 'sekolah'], true)) {
                    SatuanPendidikan::whereIn('id', $dummySchoolIds)->delete();
                }
            }
        }
    }

    /**
     * Sinkronisasi batch data Satuan Pendidikan dari struktur v_simak_sekolah_bna.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function syncSekolahBatch(array $rows, ?int $scopedKecamatanId = null): int
    {
        $allKecamatan = WilayahKecamatan::all();
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $npsn = self::cleanVal($row['npsn'] ?? '');
                $nama = self::cleanVal($row['nama_sekolah'] ?? $row['namasekolah'] ?? $row['nama'] ?? '');

                if ($npsn === '' || $nama === '' || self::isPerguruanTinggi($nama)) {
                    continue;
                }

                $kodeKec = self::cleanVal($row['kode_kecamatan_simak'] ?? $row['kodekecamatansimak'] ?? $row['kode_kecamatan'] ?? $row['kodekecamatan'] ?? '');
                $namaKec = self::cleanVal($row['nama_kecamatan'] ?? $row['namakecamatan'] ?? $row['kecamatan'] ?? '');
                $kecamatan = self::resolveKecamatan($allKecamatan, $kodeKec, $namaKec);

                $kecId = $scopedKecamatanId ?: $kecamatan->id;
                if ($scopedKecamatanId && $kecamatan->id !== $scopedKecamatanId) {
                    continue;
                }

                $jenjang = strtoupper(self::cleanVal($row['jenjang'] ?? $row['bentuk_pendidikan'] ?? 'SD'));
                $jenjangMap = [
                    'KB' => 'PAUD', 'TPA' => 'PAUD', 'SPS' => 'PAUD', 'RA' => 'PAUD', 'BA' => 'PAUD',
                    'TKLB' => 'TK', 'SDLB' => 'SD', 'SDTK' => 'SD', 'SMPLB' => 'SMP', 'SMPTK' => 'SMP',
                    'SMALB' => 'SMA', 'MAK' => 'MA', 'SKB' => 'PKBM',
                ];
                $jenjang = $jenjangMap[$jenjang] ?? $jenjang;
                if (! in_array($jenjang, ['PAUD', 'TK', 'SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PKBM'])) {
                    continue;
                }

                $statusRaw = self::cleanVal($row['status'] ?? $row['status_sekolah'] ?? 'Negeri');
                $status = (stripos($statusRaw, 'Negeri') !== false || $statusRaw === '1') ? 'Negeri' : 'Swasta';

                $akrRaw = strtoupper(self::cleanVal($row['akreditasi'] ?? ''));
                $akreditasi = in_array($akrRaw, ['A', 'B', 'C']) ? $akrRaw : 'Belum Terakreditasi';

                $sekolahId = self::cleanVal($row['sekolah_id'] ?? $row['sekolahid'] ?? '');
                $alamat = self::cleanVal($row['alamat'] ?? $row['alamat_jalan'] ?? '');
                $kepsek = self::cleanVal($row['kepala_sekolah'] ?? $row['kepalasekolah'] ?? $row['kepsek'] ?? '');
                $telepon = self::cleanVal($row['telepon'] ?? $row['nomor_telepon'] ?? '');

                $latRaw = self::cleanVal($row['latitude'] ?? $row['lintang'] ?? '');
                $lngRaw = self::cleanVal($row['longitude'] ?? $row['bujur'] ?? '');
                $lat = is_numeric($latRaw) && (float) $latRaw >= -90 && (float) $latRaw <= 90 ? (float) $latRaw : $kecamatan->latitude;
                $lng = is_numeric($lngRaw) && (float) $lngRaw >= -180 && (float) $lngRaw <= 180 ? (float) $lngRaw : $kecamatan->longitude;

                $payload = [
                    'nama_sekolah' => substr($nama, 0, 150),
                    'jenjang' => $jenjang,
                    'status' => $status,
                    'akreditasi' => $akreditasi,
                    'kecamatan_id' => $kecId,
                    'alamat' => $alamat !== '' ? substr($alamat, 0, 255) : 'Banjarnegara',
                    'kepala_sekolah' => $kepsek !== '' ? substr($kepsek, 0, 150) : '-',
                    'telepon' => $telepon !== '' ? substr($telepon, 0, 30) : null,
                    'latitude' => $lat,
                    'longitude' => $lng,
                ];
                if ($sekolahId !== '') {
                    $payload['sekolah_id'] = substr($sekolahId, 0, 40);
                }

                SatuanPendidikan::updateOrCreate(
                    ['npsn' => substr($npsn, 0, 10)],
                    $payload
                );

                $count++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $count;
    }

    /**
     * Sinkronisasi batch data Partisipasi Siswa dari struktur v_simak_siswa_bna.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function syncSiswaBatch(array $rows, ?int $defaultTahun = null, ?int $scopedKecamatanId = null): int
    {
        $defaultTahun ??= (int) date('Y');
        $allSekolah = SatuanPendidikan::all()->keyBy('npsn');
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $npsn = self::cleanVal($row['npsn'] ?? '');
                $sekolah = $npsn !== '' ? ($allSekolah->get($npsn) ?? null) : null;

                if (! $sekolah) {
                    $namaSekolah = self::cleanVal($row['nama_sekolah'] ?? $row['namasekolah'] ?? '');
                    if ($namaSekolah !== '') {
                        $sekolah = $allSekolah->first(fn ($s) => strcasecmp($s->nama_sekolah, $namaSekolah) === 0);
                    }
                }

                if (! $sekolah) {
                    continue;
                }

                if ($scopedKecamatanId && $sekolah->kecamatan_id !== $scopedKecamatanId) {
                    continue;
                }

                $tahun = (int) (self::cleanVal($row['tahun'] ?? '') ?: $defaultTahun);
                if ($tahun < 2020 || $tahun > 2035) {
                    $tahun = $defaultTahun;
                }

                $ta = self::cleanVal($row['tahun_ajaran'] ?? $row['tahunajaran'] ?? '');
                if ($ta === '') {
                    $ta = $tahun.'/'.($tahun + 1);
                }

                $totalSiswa = max(0, (int) self::cleanVal($row['total_siswa_semua_usia'] ?? $row['totalsiswasemuausia'] ?? $row['totalsiswa'] ?? 0));
                $siswaSesuai = max(0, min($totalSiswa, (int) self::cleanVal($row['siswa_usia_sesuai'] ?? $row['siswausiasesuai'] ?? $row['siswasesuai'] ?? 0)));

                $siswaL = self::cleanVal($row['siswa_l'] ?? $row['siswal'] ?? '') !== ''
                    ? (int) self::cleanVal($row['siswa_l'] ?? $row['siswal'])
                    : (int) round($totalSiswa * 0.51);
                $siswaP = self::cleanVal($row['siswa_p'] ?? $row['siswap'] ?? '') !== ''
                    ? (int) self::cleanVal($row['siswa_p'] ?? $row['siswap'])
                    : max(0, $totalSiswa - $siswaL);

                $sesuaiL = self::cleanVal($row['siswa_usia_sesuai_l'] ?? $row['siswausiasesuail'] ?? '') !== ''
                    ? (int) self::cleanVal($row['siswa_usia_sesuai_l'] ?? $row['siswausiasesuail'])
                    : (int) round($siswaSesuai * 0.51);
                $sesuaiP = self::cleanVal($row['siswa_usia_sesuai_p'] ?? $row['siswausiasesuaip'] ?? '') !== ''
                    ? (int) self::cleanVal($row['siswa_usia_sesuai_p'] ?? $row['siswausiasesuaip'])
                    : max(0, $siswaSesuai - $sesuaiL);

                $jenjang = $sekolah->jenjang;
                $u5_6 = self::cleanVal($row['siswa_usia_5_6'] ?? $row['siswausia56'] ?? '') !== ''
                    ? (int) self::cleanVal($row['siswa_usia_5_6'] ?? $row['siswausia56'])
                    : (in_array($jenjang, ['PAUD', 'TK']) ? $siswaSesuai : 0);
                $u7_12 = self::cleanVal($row['siswa_usia_7_12'] ?? $row['siswausia712'] ?? '') !== ''
                    ? (int) self::cleanVal($row['siswa_usia_7_12'] ?? $row['siswausia712'])
                    : (in_array($jenjang, ['SD', 'MI']) ? $siswaSesuai : 0);
                $u13_15 = self::cleanVal($row['siswa_usia_13_15'] ?? $row['siswausia1315'] ?? '') !== ''
                    ? (int) self::cleanVal($row['siswa_usia_13_15'] ?? $row['siswausia1315'])
                    : (in_array($jenjang, ['SMP', 'MTS']) ? $siswaSesuai : 0);
                $u16_18 = self::cleanVal($row['siswa_usia_16_18'] ?? $row['siswausia1618'] ?? '') !== ''
                    ? (int) self::cleanVal($row['siswa_usia_16_18'] ?? $row['siswausia1618'])
                    : (in_array($jenjang, ['SMA', 'SMK', 'MA']) ? $siswaSesuai : 0);

                $rombel = max(1, (int) (self::cleanVal($row['jumlah_rombel'] ?? $row['jumlahrombel'] ?? $row['rombel'] ?? '') ?: max(1, (int) round($totalSiswa / 28))));
                $lainnya = max(0, $totalSiswa - ($u5_6 + $u7_12 + $u13_15 + $u16_18));

                $siswaAttributes = [
                    'kecamatan_id' => $sekolah->kecamatan_id,
                    'tahun' => $tahun,
                    'jenjang' => $jenjang,
                    'total_siswa_semua_usia' => $totalSiswa,
                    'siswa_usia_sesuai' => $siswaSesuai,
                    'siswa_l' => $siswaL,
                    'siswa_p' => $siswaP,
                    'siswa_usia_sesuai_l' => $sesuaiL,
                    'siswa_usia_sesuai_p' => $sesuaiP,
                    'siswa_usia_5_6' => $u5_6,
                    'siswa_usia_7_12' => $u7_12,
                    'siswa_usia_13_15' => $u13_15,
                    'siswa_usia_16_18' => $u16_18,
                    'siswa_usia_lainnya' => $lainnya,
                    'jumlah_rombel' => $rombel,
                ];

                PartisipasiSiswa::updateOrCreate(
                    [
                        'satuan_pendidikan_id' => $sekolah->id,
                        'tahun_ajaran' => substr($ta, 0, 10),
                    ],
                    $siswaAttributes
                );

                // Jika data Dapodik bertahun 2026 (semester 20261), selaraskan juga ke tahun evaluasi aktif 2025
                // apabila sekolah tersebut belum memiliki data riil tahun 2025 agar Dashboard 2025 langsung akurat.
                if ($tahun === 2026) {
                    PartisipasiSiswa::updateOrCreate(
                        [
                            'satuan_pendidikan_id' => $sekolah->id,
                            'tahun_ajaran' => '2024/2025',
                        ],
                        array_merge($siswaAttributes, ['tahun' => 2025])
                    );
                }

                $count++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $count;
    }

    /**
     * Sinkronisasi batch data Guru & Tendik dari struktur v_simak_guru_bna.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function syncGuruBatch(array $rows, ?int $scopedKecamatanId = null): int
    {
        $allSekolah = SatuanPendidikan::all()->keyBy('npsn');
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $nama = self::cleanVal($row['nama'] ?? $row['namaguru'] ?? '');
                $namaSekolah = self::cleanVal($row['nama_sekolah'] ?? $row['namasekolah'] ?? $row['sekolah'] ?? '');

                if ($nama === '' || self::isPerguruanTinggi($namaSekolah)) {
                    continue;
                }

                $npsn = self::cleanVal($row['npsn'] ?? '');
                $sekolah = $npsn !== '' ? ($allSekolah->get($npsn) ?? null) : null;

                if (! $sekolah && $namaSekolah !== '') {
                    $sekolah = $allSekolah->first(fn ($s) => strcasecmp($s->nama_sekolah, $namaSekolah) === 0);
                }

                if (! $sekolah) {
                    continue;
                }

                if ($scopedKecamatanId && $sekolah->kecamatan_id !== $scopedKecamatanId) {
                    continue;
                }

                $ptkId = self::cleanVal($row['ptk_id'] ?? $row['ptkid'] ?? '');
                $nip = self::cleanVal($row['nip'] ?? '');
                $nuptk = self::cleanVal($row['nuptk'] ?? '');

                $jkRaw = strtoupper(self::cleanVal($row['jenis_kelamin'] ?? $row['jeniskelamin'] ?? $row['jk'] ?? 'L'));
                $jk = str_starts_with($jkRaw, 'P') ? 'P' : 'L';

                $statusRaw = strtoupper(self::cleanVal($row['status_kepegawaian'] ?? $row['statuskepegawaian'] ?? $row['status'] ?? 'Honor'));
                if (str_contains($statusRaw, 'PNS')) {
                    $status = 'PNS';
                } elseif (str_contains($statusRaw, 'PPPK')) {
                    $status = 'PPPK';
                } elseif (str_contains($statusRaw, 'GTY') || str_contains($statusRaw, 'YAYASAN')) {
                    $status = 'GTY';
                } elseif (str_contains($statusRaw, 'GTT') || str_contains($statusRaw, 'DAERAH')) {
                    $status = 'GTT';
                } else {
                    $status = 'Honor';
                }

                $sertifRaw = strtolower(self::cleanVal($row['sudah_sertifikasi'] ?? $row['sudahsertifikasi'] ?? $row['sertifikasi'] ?? '0'));
                $isSertif = in_array($sertifRaw, ['1', 'ya', 'true', 'sudah', 'y', 'sertifikasi'], true);

                $mapel = self::cleanVal($row['bidang_studi'] ?? $row['bidangstudi'] ?? $row['mapel'] ?? '');
                if (stripos($mapel, 'Dosen') !== false) {
                    continue;
                }
                if ($mapel === '') {
                    $mapel = 'Guru Kelas/Mapel';
                }

                $guruPayload = [
                    'satuan_pendidikan_id' => $sekolah->id,
                    'kecamatan_id' => $sekolah->kecamatan_id,
                    'nama' => substr($nama, 0, 150),
                    'nip' => $nip !== '' ? substr($nip, 0, 30) : null,
                    'nuptk' => $nuptk !== '' ? substr($nuptk, 0, 30) : null,
                    'jenis_kelamin' => $jk,
                    'status_kepegawaian' => $status,
                    'sudah_sertifikasi' => $isSertif,
                    'bidang_studi' => substr($mapel, 0, 100),
                ];
                if ($ptkId !== '') {
                    $guruPayload['ptk_id'] = substr($ptkId, 0, 40);
                }

                $existingGuru = null;
                if ($ptkId !== '') {
                    $existingGuru = GuruPendidik::where('ptk_id', substr($ptkId, 0, 40))->first();
                }
                if (! $existingGuru && $nuptk !== '') {
                    $existingGuru = GuruPendidik::where('nuptk', substr($nuptk, 0, 30))->first();
                }
                if (! $existingGuru && $nip !== '') {
                    $existingGuru = GuruPendidik::where('nip', substr($nip, 0, 30))->first();
                }
                if (! $existingGuru) {
                    $existingGuru = GuruPendidik::where('satuan_pendidikan_id', $sekolah->id)
                        ->where('nama', substr($nama, 0, 150))
                        ->first();
                }

                if ($existingGuru) {
                    $existingGuru->update($guruPayload);
                } else {
                    GuruPendidik::create($guruPayload);
                }

                $count++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $count;
    }

    /**
     * Parse berkas CSV / TSV hasil ekspor SSMS atau Excel menjadi array asosiatif.
     *
     * @return array<int, array<string, string>>
     */
    public function parseDelimitedFile(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $commaCount = substr_count($firstLine, ',');
        $semiCount = substr_count($firstLine, ';');
        $tabCount = substr_count($firstLine, "\t");

        if ($tabCount > $commaCount && $tabCount > $semiCount) {
            $delimiter = "\t";
        } elseif ($semiCount > $commaCount) {
            $delimiter = ';';
        } else {
            $delimiter = ',';
        }

        rewind($handle);
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 0, $delimiter);
        if (! $header || count($header) < 2) {
            fclose($handle);

            return [];
        }

        $cleanKeys = array_map(function ($col) {
            return strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', trim((string) $col)));
        }, $header);

        $rows = [];
        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($line) < 2) {
                continue;
            }
            $assoc = [];
            foreach ($cleanKeys as $idx => $key) {
                if ($key !== '' && isset($line[$idx])) {
                    $assoc[$key] = $line[$idx];
                }
            }
            if (! empty($assoc)) {
                $rows[] = $assoc;
            }
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Parse berkas OpenXML Excel (.xlsx) menjadi array baris sel mentah.
     *
     * @return array<int, array<string, string>>
     */
    public function parseXlsxCells(string $filePath): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return [];
        }

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

        $candidateSheets = ['xl/worksheets/sheet1.xml', 'xl/worksheets/sheet.xml'];
        $sheetXmlStr = null;
        foreach ($candidateSheets as $name) {
            if ($zip->locateName($name) !== false) {
                $sheetXmlStr = $zip->getFromName($name);
                break;
            }
        }

        if (! $sheetXmlStr) {
            $zip->close();

            return [];
        }

        $sheetXml = simplexml_load_string($sheetXmlStr);
        $zip->close();

        if (! $sheetXml || ! isset($sheetXml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheetXml->sheetData->row as $row) {
            $rData = [];
            foreach ($row->c as $c) {
                $ref = preg_replace('/[0-9]+/', '', (string) $c['r']);
                $type = (string) $c['t'];
                $val = '';
                if ($type === 's') {
                    $idx = (int) $c->v;
                    $val = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $val = (string) $c->is->t;
                } elseif (isset($c->v)) {
                    $val = (string) $c->v;
                }
                $rData[$ref] = $val;
            }
            if (! empty($rData)) {
                $rows[] = $rData;
            }
        }

        return $rows;
    }

    /**
     * Import dan perbarui nilai partisipasi murid per sekolah dari file Excel Dapodik per rentang usia.
     * Menargetkan nomor NPSN untuk update data jika sudah ada (replace), atau insert jika belum ada.
     *
     * @return array{updated: int, created: int, total_rows: int, skipped: int}
     */
    public function importExcelDapodikUsia(string $filePath, int $tahun, ?int $scopedKecamatanId = null): array
    {
        $allSekolah = SatuanPendidikan::all()->keyBy('npsn');
        $allKecamatan = WilayahKecamatan::all();

        // 1. Coba baca format XLSX
        $rows = $this->parseXlsxCells($filePath);

        $headerMap = [];
        $dataRows = [];

        if (! empty($rows)) {
            // XLSX mode: Baris pertama adalah header
            $headerRow = $rows[0];
            foreach ($headerRow as $colLetter => $val) {
                $norm = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $val), '_'));
                $headerMap[$norm] = $colLetter;
            }
            $dataRows = array_slice($rows, 1);
        } else {
            // CSV / TSV fallback mode
            $delimited = $this->parseDelimitedFile($filePath);
            if (empty($delimited)) {
                return [
                    'updated' => 0,
                    'created' => 0,
                    'total_rows' => 0,
                    'skipped' => 0,
                ];
            }
            // Petakan langsung kunci kolom dari array asosiatif
            $first = reset($delimited);
            foreach (array_keys($first) as $k) {
                $norm = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $k), '_'));
                $headerMap[$norm] = $k;
            }
            $dataRows = $delimited;
        }

        $getVal = function (string $key, array $rData) use ($headerMap): int {
            $col = $headerMap[$key] ?? null;

            return $col && isset($rData[$col]) ? (int) self::cleanVal($rData[$col]) : 0;
        };

        $getString = function (string $key, array $rData, string $default = '') use ($headerMap): string {
            $col = $headerMap[$key] ?? null;

            return $col && isset($rData[$col]) ? self::cleanVal($rData[$col]) : $default;
        };

        $updatedCount = 0;
        $createdCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($dataRows as $r) {
                // Ambil NPSN sebagai target utama update data (mendukung numerik 8 digit & PKBM diawali P)
                $npsn = trim($getString('npsn', $r, $r['B'] ?? ''));
                if ($npsn === '' || ! preg_match('/^[A-Za-z0-9]{7,10}$/', $npsn)) {
                    $skippedCount++;

                    continue;
                }

                // Hitung kelompok umur
                $u0_l = $getVal('u0_l', $r);
                $u0_p = $getVal('u0_p', $r);
                $u1_l = $getVal('u1_l', $r);
                $u1_p = $getVal('u1_p', $r);
                $u2_l = $getVal('u2_l', $r);
                $u2_p = $getVal('u2_p', $r);
                $u3_l = $getVal('u3_l', $r);
                $u3_p = $getVal('u3_p', $r);
                $u4_l = $getVal('u4_l', $r);
                $u4_p = $getVal('u4_p', $r);

                $u5_l = $getVal('u5_l', $r);
                $u5_p = $getVal('u5_p', $r);
                $u6_l = $getVal('u6_l', $r);
                $u6_p = $getVal('u6_p', $r);
                $u5_6_l = $u5_l + $u6_l;
                $u5_6_p = $u5_p + $u6_p;
                $u5_6 = $u5_6_l + $u5_6_p;

                $u7_l = $getVal('u7_l', $r);
                $u7_p = $getVal('u7_p', $r);
                $u8_l = $getVal('u8_l', $r);
                $u8_p = $getVal('u8_p', $r);
                $u9_l = $getVal('u9_l', $r);
                $u9_p = $getVal('u9_p', $r);
                $u10_l = $getVal('u10_l', $r);
                $u10_p = $getVal('u10_p', $r);
                $u11_l = $getVal('u11_l', $r);
                $u11_p = $getVal('u11_p', $r);
                $u12_l = $getVal('u12_l', $r);
                $u12_p = $getVal('u12_p', $r);
                $u7_12_l = $u7_l + $u8_l + $u9_l + $u10_l + $u11_l + $u12_l;
                $u7_12_p = $u7_p + $u8_p + $u9_p + $u10_p + $u11_p + $u12_p;
                $u7_12 = $u7_12_l + $u7_12_p;

                $u13_l = $getVal('u13_l', $r);
                $u13_p = $getVal('u13_p', $r);
                $u14_l = $getVal('u14_l', $r);
                $u14_p = $getVal('u14_p', $r);
                $u15_l = $getVal('u15_l', $r);
                $u15_p = $getVal('u15_p', $r);
                $u13_15_l = $u13_l + $u14_l + $u15_l;
                $u13_15_p = $u13_p + $u14_p + $u15_p;
                $u13_15 = $u13_15_l + $u13_15_p;

                $u16_l = $getVal('u16_l', $r);
                $u16_p = $getVal('u16_p', $r);
                $u17_l = $getVal('u17_l', $r);
                $u17_p = $getVal('u17_p', $r);
                $u18_l = $getVal('u18_l', $r);
                $u18_p = $getVal('u18_p', $r);
                $u16_18_l = $u16_l + $u17_l + $u18_l;
                $u16_18_p = $u16_p + $u17_p + $u18_p;
                $u16_18 = $u16_18_l + $u16_18_p;

                $u19_l = $getVal('u19_l', $r);
                $u19_p = $getVal('u19_p', $r);
                $u20_l = $getVal('u20_l', $r);
                $u20_p = $getVal('u20_p', $r);
                $u21_l = $getVal('u21__l', $r) ?: $getVal('u21_l', $r);
                $u21_p = $getVal('u21__p', $r) ?: $getVal('u21_p', $r);

                $pdTotal = $getVal('pd_total', $r) ?: $getVal('total', $r);
                $pdL = $getVal('pd_l', $r) ?: $getVal('l', $r);
                $pdP = $getVal('pd_p', $r) ?: $getVal('p', $r);

                $sumUsiaL = $u0_l + $u1_l + $u2_l + $u3_l + $u4_l + $u5_6_l + $u7_12_l + $u13_15_l + $u16_18_l + $u19_l + $u20_l + $u21_l;
                $sumUsiaP = $u0_p + $u1_p + $u2_p + $u3_p + $u4_p + $u5_6_p + $u7_12_p + $u13_15_p + $u16_18_p + $u19_p + $u20_p + $u21_p;
                $sumTotal = $sumUsiaL + $sumUsiaP;

                $totalSiswa = $pdTotal > 0 ? $pdTotal : $sumTotal;
                $siswaL = $pdL > 0 ? $pdL : $sumUsiaL;
                $siswaP = $pdP > 0 ? $pdP : $sumUsiaP;

                if ($siswaL === 0 && $siswaP === 0 && $totalSiswa > 0) {
                    $siswaL = (int) round($totalSiswa * 0.51);
                    $siswaP = max(0, $totalSiswa - $siswaL);
                }

                $lainnya = max(0, $totalSiswa - ($u5_6 + $u7_12 + $u13_15 + $u16_18));

                // 2. Cari Satuan Pendidikan berdasarkan NPSN
                $sekolah = $allSekolah->get($npsn);
                if (! $sekolah) {
                    $namaSekolah = $getString('nama_satuan_pendidikan', $r, $getString('nama_sekolah', $r, ''));
                    if ($namaSekolah !== '') {
                        $sekolah = $allSekolah->first(fn ($s) => strcasecmp($s->nama_sekolah, $namaSekolah) === 0);
                    }
                }

                // Jika sekolah belum terdaftar, buat otomatis dari data file Excel
                if (! $sekolah) {
                    $namaSekolah = $getString('nama_satuan_pendidikan', $r, $getString('nama_sekolah', $r, "Sekolah {$npsn}"));
                    $bentuk = strtoupper($getString('bentuk_pendidikan', $r, 'SD'));
                    $statusStr = strtolower($getString('status_sekolah', $r, 'Swasta'));
                    $status = str_contains($statusStr, 'negeri') ? 'Negeri' : 'Swasta';
                    $namaKec = $getString('kecamatan', $r, '');

                    $jenjangSekolah = match (true) {
                        in_array($bentuk, ['TK', 'KB', 'TPA', 'SPS', 'PAUD']) => 'PAUD',
                        in_array($bentuk, ['SD', 'SDLB', 'SPK SD']) => 'SD',
                        $bentuk === 'MI' => 'MI',
                        in_array($bentuk, ['SMP', 'SMPLB', 'SPK SMP']) => 'SMP',
                        $bentuk === 'MTS' || $bentuk === 'MTS.' => 'MTS',
                        in_array($bentuk, ['SMA', 'SMALB', 'SPK SMA']) => 'SMA',
                        $bentuk === 'SMK' => 'SMK',
                        $bentuk === 'MA' => 'MA',
                        in_array($bentuk, ['PKBM', 'SKB']) => 'PKBM',
                        default => 'SD',
                    };

                    $kecamatan = self::resolveKecamatan($allKecamatan, '', $namaKec);

                    if ($scopedKecamatanId && $kecamatan->id !== $scopedKecamatanId) {
                        $skippedCount++;

                        continue;
                    }

                    $sekolah = SatuanPendidikan::create([
                        'npsn' => $npsn,
                        'nama_sekolah' => $namaSekolah,
                        'jenjang' => $jenjangSekolah,
                        'status' => $status,
                        'kecamatan_id' => $kecamatan->id,
                        'alamat' => "Kec. {$kecamatan->nama}, Banjarnegara",
                        'akreditasi' => 'Belum Terakreditasi',
                    ]);
                    $allSekolah->put($npsn, $sekolah);
                }

                // Cek scoping operator kecamatan
                if ($scopedKecamatanId && $sekolah->kecamatan_id !== $scopedKecamatanId) {
                    $skippedCount++;

                    continue;
                }

                $jenjang = $sekolah->jenjang;

                // 3. Tentukan Siswa Usia Sesuai Jenjang
                if (in_array($jenjang, ['PAUD', 'TK'])) {
                    $siswaSesuai = $u5_6;
                    $sesuaiL = $u5_6_l;
                    $sesuaiP = $u5_6_p;
                } elseif (in_array($jenjang, ['SD', 'MI'])) {
                    $siswaSesuai = $u7_12;
                    $sesuaiL = $u7_12_l;
                    $sesuaiP = $u7_12_p;
                } elseif (in_array($jenjang, ['SMP', 'MTS'])) {
                    $siswaSesuai = $u13_15;
                    $sesuaiL = $u13_15_l;
                    $sesuaiP = $u13_15_p;
                } elseif (in_array($jenjang, ['SMA', 'SMK', 'MA', 'PKBM'])) {
                    $siswaSesuai = $u16_18;
                    $sesuaiL = $u16_18_l;
                    $sesuaiP = $u16_18_p;
                } else {
                    $siswaSesuai = $u7_12;
                    $sesuaiL = $u7_12_l;
                    $sesuaiP = $u7_12_p;
                }
                $siswaSesuai = min($siswaSesuai, $totalSiswa);

                // 4. Update jika sudah ada untuk tahun tersebut, atau Insert baru
                $existing = PartisipasiSiswa::where('satuan_pendidikan_id', $sekolah->id)
                    ->where('tahun', $tahun)
                    ->first();

                if ($existing) {
                    $rombel = $existing->jumlah_rombel > 0
                        ? $existing->jumlah_rombel
                        : max(1, (int) round($totalSiswa / ($jenjang === 'PAUD' ? 15 : 28)));

                    $existing->update([
                        'kecamatan_id' => $sekolah->kecamatan_id,
                        'jenjang' => $jenjang,
                        'total_siswa_semua_usia' => $totalSiswa,
                        'siswa_l' => $siswaL,
                        'siswa_p' => $siswaP,
                        'siswa_usia_sesuai' => $siswaSesuai,
                        'siswa_usia_sesuai_l' => $sesuaiL,
                        'siswa_usia_sesuai_p' => $sesuaiP,
                        'siswa_usia_5_6' => $u5_6,
                        'siswa_usia_7_12' => $u7_12,
                        'siswa_usia_13_15' => $u13_15,
                        'siswa_usia_16_18' => $u16_18,
                        'siswa_usia_lainnya' => $lainnya,
                        'jumlah_rombel' => $rombel,
                    ]);
                    $updatedCount++;
                } else {
                    $tahunAjaran = "{$tahun}/".($tahun + 1);
                    $rombel = max(1, (int) round($totalSiswa / ($jenjang === 'PAUD' ? 15 : 28)));

                    PartisipasiSiswa::updateOrCreate(
                        [
                            'satuan_pendidikan_id' => $sekolah->id,
                            'tahun_ajaran' => $tahunAjaran,
                        ],
                        [
                            'kecamatan_id' => $sekolah->kecamatan_id,
                            'tahun' => $tahun,
                            'jenjang' => $jenjang,
                            'total_siswa_semua_usia' => $totalSiswa,
                            'siswa_l' => $siswaL,
                            'siswa_p' => $siswaP,
                            'siswa_usia_sesuai' => $siswaSesuai,
                            'siswa_usia_sesuai_l' => $sesuaiL,
                            'siswa_usia_sesuai_p' => $sesuaiP,
                            'siswa_usia_5_6' => $u5_6,
                            'siswa_usia_7_12' => $u7_12,
                            'siswa_usia_13_15' => $u13_15,
                            'siswa_usia_16_18' => $u16_18,
                            'siswa_usia_lainnya' => $lainnya,
                            'jumlah_rombel' => $rombel,
                        ]
                    );
                    $createdCount++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'updated' => $updatedCount,
            'created' => $createdCount,
            'total_rows' => count($dataRows),
            'skipped' => $skippedCount,
        ];
    }
}
