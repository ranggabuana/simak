<?php

namespace App\Services;

use App\Models\GuruPendidik;
use App\Models\PartisipasiSiswa;
use App\Models\SatuanPendidikan;
use App\Models\WilayahKecamatan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
}
