<?php

namespace Database\Seeders;

use App\Models\GuruPendidik;
use App\Models\PartisipasiSiswa;
use App\Models\PendudukUsiaSekolah;
use App\Models\PrestasiSiswa;
use App\Models\RealisasiKinerja;
use App\Models\SatuanPendidikan;
use App\Models\TargetRenstra;
use App\Models\User;
use App\Models\WilayahKecamatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use ZipArchive;

class SimakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Users Internal Dinas Pendidikan
        $users = [
            [
                'name' => 'Dr. H. Teguh Handoko, S.Sos., M.Si.',
                'email' => 'pimpinan@dindikpora.banjarnegarakab.go.id',
                'password' => Hash::make('password'),
                'role' => 'pimpinan',
                'nip' => '196807151992031005',
                'jabatan' => 'Kepala Dinas Pendidikan, Kepemudaan dan Olahraga',
            ],
            [
                'name' => 'Subbag Perencanaan & Evaluasi (Admin)',
                'email' => 'admin@dindikpora.banjarnegarakab.go.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nip' => '197905122005011008',
                'jabatan' => 'Kasubbag Program dan Evaluasi Kinerja',
            ],
            [
                'name' => 'Analis Akuntabilitas Kinerja (Perencana)',
                'email' => 'perencana@dindikpora.banjarnegarakab.go.id',
                'password' => Hash::make('password'),
                'role' => 'perencana',
                'nip' => '198503202010012014',
                'jabatan' => 'Perencana Ahli Muda / Analis SAKIP',
            ],
            [
                'name' => 'Operator Korwilcam Banjarnegara',
                'email' => 'operator@dindikpora.banjarnegarakab.go.id',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'nip' => '198811052014021002',
                'jabatan' => 'Staf Operator Data Pendidikan Wilayah',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }

        // 2. Kecamatan Coordinates Banjarnegara (Matched to Official GeoJSON Centroids)
        $coords = [
            '330401' => ['lat' => -7.4981, 'lng' => 109.4056, 'desa' => 15, 'luas' => 48.87], // SUSUKAN
            '330402' => ['lat' => -7.4783, 'lng' => 109.4507, 'desa' => 8, 'luas' => 26.23],  // PURWAREJA KLAMPOK
            '330403' => ['lat' => -7.4772, 'lng' => 109.4985, 'desa' => 16, 'luas' => 52.61], // MANDIRAJA
            '330404' => ['lat' => -7.4701, 'lng' => 109.5662, 'desa' => 13, 'luas' => 51.72], // PURWANEGARA
            '330405' => ['lat' => -7.4297, 'lng' => 109.6336, 'desa' => 18, 'luas' => 38.32], // BAWANG
            '330406' => ['lat' => -7.4126, 'lng' => 109.7130, 'desa' => 13, 'luas' => 26.24], // BANJARNEGARA
            '330407' => ['lat' => -7.4156, 'lng' => 109.7650, 'desa' => 15, 'luas' => 39.56], // SIGALUH
            '330408' => ['lat' => -7.3620, 'lng' => 109.7311, 'desa' => 12, 'luas' => 43.57], // MADUKARA
            '330409' => ['lat' => -7.3372, 'lng' => 109.6789, 'desa' => 17, 'luas' => 46.21], // BANJARMANGU
            '330410' => ['lat' => -7.3652, 'lng' => 109.6575, 'desa' => 11, 'luas' => 28.28], // WANADADI
            '330411' => ['lat' => -7.4176, 'lng' => 109.5402, 'desa' => 11, 'luas' => 32.45], // RAKIT
            '330412' => ['lat' => -7.3303, 'lng' => 109.5946, 'desa' => 17, 'luas' => 102.83], // PUNGGELAN
            '330413' => ['lat' => -7.2896, 'lng' => 109.7022, 'desa' => 15, 'luas' => 39.07], // KARANGKOBAR
            '330414' => ['lat' => -7.3138, 'lng' => 109.7915, 'desa' => 16, 'luas' => 46.19], // PAGENTAN
            '330415' => ['lat' => -7.2373, 'lng' => 109.8164, 'desa' => 17, 'luas' => 52.25], // PEJAWARAN
            '330416' => ['lat' => -7.2128, 'lng' => 109.8509, 'desa' => 8, 'luas' => 47.17],  // BATUR
            '330417' => ['lat' => -7.2549, 'lng' => 109.7610, 'desa' => 11, 'luas' => 41.43], // WANAYASA
            '330418' => ['lat' => -7.2386, 'lng' => 109.6624, 'desa' => 16, 'luas' => 49.78], // KALIBENING
            '330419' => ['lat' => -7.2481, 'lng' => 109.5838, 'desa' => 8, 'luas' => 24.38],  // PANDANARUM
            '330420' => ['lat' => -7.4396, 'lng' => 109.6925, 'desa' => 9, 'luas' => 50.55],  // PAGEDONGAN
        ];

        // 3. Import Data Demografi dari penduduk.xlsx
        $xlsxPath = base_path('penduduk.xlsx');
        $kecamatanModels = [];

        if (file_exists($xlsxPath)) {
            $zip = new ZipArchive;
            if ($zip->open($xlsxPath) === true) {
                $sharedStrings = [];
                if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                    $sst = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
                    foreach ($sst->si as $si) {
                        $t = '';
                        if (isset($si->t)) {
                            $t = (string) $si->t;
                        } elseif (isset($si->r)) {
                            foreach ($si->r as $r) {
                                $t .= (string) $r->t;
                            }
                        }
                        $sharedStrings[] = $t;
                    }
                }

                if ($zip->locateName('xl/worksheets/sheet2.xml') !== false) {
                    $sheetXml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet2.xml'));
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

                        $kode = $rData['B'] ?? '';
                        $nama = $rData['C'] ?? '';
                        if ($kode === '330402' || str_contains(strtoupper($nama), 'KLAMPOK') || str_contains(strtoupper($nama), 'KLEMPOK')) {
                            $nama = 'PURWAREJA KLAMPOK';
                        }

                        if (preg_match('/^3304\d{2}$/', $kode)) {
                            $coord = $coords[$kode] ?? ['lat' => -7.3957, 'lng' => 109.6967, 'desa' => 10, 'luas' => 45.0];
                            $kec = WilayahKecamatan::updateOrCreate(
                                ['kode' => $kode],
                                [
                                    'nama' => strtoupper($nama),
                                    'ibukota' => 'Kec. '.ucwords(strtolower($nama)),
                                    'luas_wilayah' => $coord['luas'],
                                    'jumlah_desa' => $coord['desa'],
                                    'latitude' => $coord['lat'],
                                    'longitude' => $coord['lng'],
                                ]
                            );

                            $kecamatanModels[$kode] = $kec;

                            // Parse demographics
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
                            $p16_30_l = (int) ($rData['NM'] ?? 0);
                            $p16_30_p = (int) ($rData['NN'] ?? 0);

                            $tot0_18 = ($p0_4_l + $p0_4_p) + ($p5_6_l + $p5_6_p) + ($p7_12_l + $p7_12_p) + ($p13_15_l + $p13_15_p) + ($p16_18_l + $p16_18_p);
                            $estTotal = (int) ($tot0_18 * 3.4); // Estimasi total populasi riil kecamatan
                            $est18Plus = $estTotal - $tot0_18;

                            // Tahun 2025
                            PendudukUsiaSekolah::updateOrCreate(
                                ['kecamatan_id' => $kec->id, 'tahun' => 2025],
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
                                    'usia_16_30_l' => $p16_30_l,
                                    'usia_16_30_p' => $p16_30_p,
                                    'usia_16_30_total' => $p16_30_l + $p16_30_p,
                                    'usia_18_plus_total' => $est18Plus,
                                    'total_penduduk' => $estTotal,
                                ]
                            );

                            // Tahun 2024 (Tahun N-1)
                            PendudukUsiaSekolah::updateOrCreate(
                                ['kecamatan_id' => $kec->id, 'tahun' => 2024],
                                [
                                    'usia_0_4_l' => (int) ($p0_4_l * 0.99),
                                    'usia_0_4_p' => (int) ($p0_4_p * 0.99),
                                    'usia_0_4_total' => (int) (($p0_4_l + $p0_4_p) * 0.99),
                                    'usia_5_6_l' => (int) ($p5_6_l * 0.99),
                                    'usia_5_6_p' => (int) ($p5_6_p * 0.99),
                                    'usia_5_6_total' => (int) (($p5_6_l + $p5_6_p) * 0.99),
                                    'usia_7_12_l' => (int) ($p7_12_l * 0.99),
                                    'usia_7_12_p' => (int) ($p7_12_p * 0.99),
                                    'usia_7_12_total' => (int) (($p7_12_l + $p7_12_p) * 0.99),
                                    'usia_13_15_l' => (int) ($p13_15_l * 0.99),
                                    'usia_13_15_p' => (int) ($p13_15_p * 0.99),
                                    'usia_13_15_total' => (int) (($p13_15_l + $p13_15_p) * 0.99),
                                    'usia_16_18_l' => (int) ($p16_18_l * 0.99),
                                    'usia_16_18_p' => (int) ($p16_18_p * 0.99),
                                    'usia_16_18_total' => (int) (($p16_18_l + $p16_18_p) * 0.99),
                                    'usia_16_30_l' => (int) ($p16_30_l * 0.99),
                                    'usia_16_30_p' => (int) ($p16_30_p * 0.99),
                                    'usia_16_30_total' => (int) (($p16_30_l + $p16_30_p) * 0.99),
                                    'usia_18_plus_total' => (int) ($est18Plus * 0.99),
                                    'total_penduduk' => (int) ($estTotal * 0.99),
                                ]
                            );
                        }
                    }
                }
                $zip->close();
            }
        }

        // 4. Target Renstra 2025 - 2029
        $targetRenstra = [
            [
                'tahun' => 2025,
                'target_apk_paud' => 78.50,
                'target_apk_sd' => 101.80,
                'target_apk_smp' => 97.50,
                'target_apk_sma' => 84.20,
                'target_apm_paud' => 65.20,
                'target_apm_sd' => 98.40,
                'target_apm_smp' => 86.80,
                'target_apm_sma' => 72.50,
                'target_aps_paud' => 70.10,
                'target_aps_sd' => 99.20,
                'target_aps_smp' => 95.80,
                'target_aps_sma' => 78.90,
                'target_guru_sertifikasi' => 72.00,
                'target_paud_akreditasi_b' => 68.00,
                'target_akip' => 82.50,
                'target_rls' => 7.35,
                'target_hls' => 12.45,
                'target_ikk_renstra' => 88.00,
            ],
            [
                'tahun' => 2026,
                'target_apk_paud' => 80.00,
                'target_apk_sd' => 102.00,
                'target_apk_smp' => 98.20,
                'target_apk_sma' => 85.50,
                'target_apm_paud' => 67.00,
                'target_apm_sd' => 98.70,
                'target_apm_smp' => 88.00,
                'target_apm_sma' => 74.00,
                'target_aps_paud' => 72.00,
                'target_aps_sd' => 99.40,
                'target_aps_smp' => 96.30,
                'target_aps_sma' => 80.50,
                'target_guru_sertifikasi' => 75.50,
                'target_paud_akreditasi_b' => 72.00,
                'target_akip' => 84.00,
                'target_rls' => 7.48,
                'target_hls' => 12.60,
                'target_ikk_renstra' => 90.00,
            ],
            [
                'tahun' => 2027,
                'target_apk_paud' => 82.00,
                'target_apk_sd' => 102.20,
                'target_apk_smp' => 98.80,
                'target_apk_sma' => 87.00,
                'target_apm_paud' => 69.00,
                'target_apm_sd' => 99.00,
                'target_apm_smp' => 89.20,
                'target_apm_sma' => 75.80,
                'target_aps_paud' => 74.00,
                'target_aps_sd' => 99.60,
                'target_aps_smp' => 97.00,
                'target_aps_sma' => 82.00,
                'target_guru_sertifikasi' => 79.00,
                'target_paud_akreditasi_b' => 76.00,
                'target_akip' => 85.50,
                'target_rls' => 7.60,
                'target_hls' => 12.75,
                'target_ikk_renstra' => 92.50,
            ],
            [
                'tahun' => 2028,
                'target_apk_paud' => 84.00,
                'target_apk_sd' => 102.50,
                'target_apk_smp' => 99.20,
                'target_apk_sma' => 88.50,
                'target_apm_paud' => 71.00,
                'target_apm_sd' => 99.20,
                'target_apm_smp' => 90.50,
                'target_apm_sma' => 77.50,
                'target_aps_paud' => 76.00,
                'target_aps_sd' => 99.70,
                'target_aps_smp' => 97.50,
                'target_aps_sma' => 83.80,
                'target_guru_sertifikasi' => 82.50,
                'target_paud_akreditasi_b' => 80.00,
                'target_akip' => 86.80,
                'target_rls' => 7.72,
                'target_hls' => 12.90,
                'target_ikk_renstra' => 94.00,
            ],
            [
                'tahun' => 2029,
                'target_apk_paud' => 86.00,
                'target_apk_sd' => 102.50,
                'target_apk_smp' => 99.50,
                'target_apk_sma' => 90.00,
                'target_apm_paud' => 73.00,
                'target_apm_sd' => 99.50,
                'target_apm_smp' => 91.80,
                'target_apm_sma' => 79.20,
                'target_aps_paud' => 78.00,
                'target_aps_sd' => 99.80,
                'target_aps_smp' => 98.00,
                'target_aps_sma' => 85.50,
                'target_guru_sertifikasi' => 86.00,
                'target_paud_akreditasi_b' => 85.00,
                'target_akip' => 88.00,
                'target_rls' => 7.85,
                'target_hls' => 13.05,
                'target_ikk_renstra' => 96.00,
            ],
        ];

        foreach ($targetRenstra as $tr) {
            TargetRenstra::updateOrCreate(['tahun' => $tr['tahun']], $tr);
        }

        // 5. Realisasi Kinerja (Tahun N-1 dan Tahun N)
        RealisasiKinerja::updateOrCreate(
            ['tahun' => 2024],
            [
                'capaian_ikk_renstra' => 86.75,
                'capaian_akip_n' => 81.65,
                'capaian_akip_prev' => 79.80,
                'capaian_rls_n' => 7.28,
                'capaian_rls_prev' => 7.15,
                'capaian_hls_n' => 12.35,
                'capaian_hls_prev' => 12.20,
                'evaluasi_status' => 'Baik',
                'catatan_evaluasi' => 'Peningkatan indeks AKIP DINDIKPORA mencapai kategori A (81.65), angka putus sekolah jenjang SMP mulai teratasi di wilayah selatan Banjarnegara.',
                'rekomendasi' => 'Perkuat program beasiswa siswa kurang mampu dan pemerataan sertifikasi guru kejuruan serta penguatan akreditasi PAUD.',
            ]
        );

        RealisasiKinerja::updateOrCreate(
            ['tahun' => 2025],
            [
                'capaian_ikk_renstra' => 89.20,
                'capaian_akip_n' => 83.10,
                'capaian_akip_prev' => 81.65,
                'capaian_rls_n' => 7.39,
                'capaian_rls_prev' => 7.28,
                'capaian_hls_n' => 12.52,
                'capaian_hls_prev' => 12.35,
                'evaluasi_status' => 'Sangat Baik',
                'catatan_evaluasi' => 'Target AKIP dan RLS tahun 2025 terlampaui. Partisipasi PAUD meningkat 4.2% berkat program PAUD Holistik Integratif.',
                'rekomendasi' => 'Fokuskan intervensi APM SMA/SMK pada daerah dataran tinggi (Batur, Pejawaran, Wanayasa) melalui fasilitas transportasi sekolah gratis dan PKBM desa.',
            ]
        );

        // 6. Satuan Pendidikan dan Partisipasi Siswa Sampel Representatif di 20 Kecamatan
        $jenjangList = ['PAUD', 'SD', 'SMP', 'SMA', 'SMK'];
        $akreditasiList = ['A', 'B', 'C', 'Belum Terakreditasi'];

        $schoolTemplates = [
            'PAUD' => ['TK Pertiwi', 'TK Aisyiyah Bustanul Athfal', 'KB Harapan Bangsa', 'TK Dharma Wanita', 'PAUD Tunas Harapan'],
            'SD' => ['SD Negeri 1', 'SD Negeri 2', 'SD Negeri 3', 'MI Ma\'arif', 'SD Islam Terpadu'],
            'SMP' => ['SMP Negeri 1', 'SMP Negeri 2', 'MTs Negeri 1', 'SMP Muhammadiyah 1'],
            'SMA' => ['SMA Negeri 1', 'SMA Muhammadiyah 1'],
            'SMK' => ['SMK Negeri 1', 'SMK Cokroaminoto 1', 'SMK Panca Bhakti'],
        ];

        $npsnCounter = 20300001;

        foreach ($kecamatanModels as $kode => $kec) {
            $demografi = PendudukUsiaSekolah::where('kecamatan_id', $kec->id)->where('tahun', 2025)->first();

            foreach ($schoolTemplates as $jenjang => $names) {
                foreach ($names as $idx => $tName) {
                    $schoolName = $tName.' '.ucwords(strtolower($kec->nama));
                    $npsn = (string) ($npsnCounter++);
                    $akr = ($jenjang === 'PAUD') ? ($idx % 3 === 0 ? 'A' : ($idx % 3 === 1 ? 'B' : 'C')) : ($idx === 0 ? 'A' : 'B');

                    $sekolah = SatuanPendidikan::updateOrCreate(
                        ['npsn' => $npsn],
                        [
                            'nama_sekolah' => $schoolName,
                            'jenjang' => $jenjang,
                            'status' => str_contains($schoolName, 'Negeri') ? 'Negeri' : 'Swasta',
                            'akreditasi' => $akr,
                            'kecamatan_id' => $kec->id,
                            'alamat' => 'Jl. Raya '.ucwords(strtolower($kec->nama)).' No. '.(10 + $idx),
                            'kepala_sekolah' => 'Drs. Supriyanto, M.Pd.',
                            'telepon' => '0286-'.(591000 + $idx),
                            'latitude' => $kec->latitude + (mt_rand(-50, 50) / 10000),
                            'longitude' => $kec->longitude + (mt_rand(-50, 50) / 10000),
                        ]
                    );

                    // Partisipasi Siswa per sekolah
                    $factor = ($jenjang === 'SD') ? 220 : (($jenjang === 'SMP') ? 340 : (($jenjang === 'SMA' || $jenjang === 'SMK') ? 400 : 55));
                    $totalSiswa = (int) ($factor + mt_rand(-15, 25));
                    $siswaSesuai = (int) ($totalSiswa * 0.94);

                    PartisipasiSiswa::updateOrCreate(
                        [
                            'satuan_pendidikan_id' => $sekolah->id,
                            'tahun_ajaran' => '2024/2025',
                        ],
                        [
                            'kecamatan_id' => $kec->id,
                            'tahun' => 2025,
                            'jenjang' => $jenjang,
                            'total_siswa_semua_usia' => $totalSiswa,
                            'siswa_usia_sesuai' => $siswaSesuai,
                            'siswa_usia_5_6' => ($jenjang === 'PAUD' ? $siswaSesuai : ($jenjang === 'SD' ? 8 : 0)),
                            'siswa_usia_7_12' => ($jenjang === 'SD' ? $siswaSesuai : ($jenjang === 'SMP' ? 6 : 0)),
                            'siswa_usia_13_15' => ($jenjang === 'SMP' ? $siswaSesuai : ($jenjang === 'SMA' || $jenjang === 'SMK' ? 12 : 0)),
                            'siswa_usia_16_18' => ($jenjang === 'SMA' || $jenjang === 'SMK' ? $siswaSesuai : 0),
                            'siswa_usia_lainnya' => max(0, $totalSiswa - $siswaSesuai),
                            'jumlah_rombel' => max(2, (int) ($totalSiswa / 28)),
                        ]
                    );

                    // Guru & Tendik (Rata-rata 4 s.d. 12 guru per sekolah)
                    $guruCount = ($jenjang === 'PAUD') ? 4 : (($jenjang === 'SD') ? 10 : 20);
                    for ($g = 1; $g <= $guruCount; $g++) {
                        $isSertif = ($g % 4 !== 0); // 75% sertifikasi
                        GuruPendidik::create([
                            'satuan_pendidikan_id' => $sekolah->id,
                            'kecamatan_id' => $kec->id,
                            'nama' => 'Guru '.$jenjang.' '.$kec->nama.' '.$g,
                            'nip' => '1982'.str_pad((string) mt_rand(10000000000, 99999999999), 14, '0'),
                            'nuptk' => (string) mt_rand(1000000000000000, 9999999999999999),
                            'jenis_kelamin' => ($g % 2 === 0 ? 'L' : 'P'),
                            'status_kepegawaian' => ($g % 3 === 0 ? 'PPPK' : ($g % 4 === 0 ? 'Honor' : 'PNS')),
                            'sudah_sertifikasi' => $isSertif,
                            'bidang_studi' => ($jenjang === 'SD') ? 'Guru Kelas' : 'Matematika/IPA/Bahasa',
                        ]);
                    }
                }
            }
        }

        // 7. Prestasi Siswa (Tujuan 2)
        $samplePrestasi = [
            [
                'nama_siswa' => 'Ahmad Fauzan Pratama',
                'nisn' => '0089123451',
                'nama_kejuaraan' => 'Olimpiade Sains Nasional (OSN) Bidang Matematika',
                'bidang' => 'Sains & Akademik',
                'tingkat' => 'Nasional',
                'peringkat' => 'Juara 1',
                'tahun' => 2025,
                'penyelenggara' => 'BPTI Kemendikbudristek',
                'keterangan' => 'Meraih Medali Emas OSN Tingkat Nasional mewakili Kabupaten Banjarnegara',
            ],
            [
                'nama_siswa' => 'Nabila Putri Cahyani',
                'nisn' => '0098432112',
                'nama_kejuaraan' => 'Festival dan Lomba Seni Siswa Nasional (FLS2N) Tari Tradisional',
                'bidang' => 'Seni & Budaya',
                'tingkat' => 'Provinsi',
                'peringkat' => 'Juara 1',
                'tahun' => 2025,
                'penyelenggara' => 'Dinas Pendidikan Provinsi Jawa Tengah',
                'keterangan' => 'Juara 1 Tingkat Jawa Tengah cabang Tari Dawet Ayu Kontemporer',
            ],
            [
                'nama_siswa' => 'Rizky Kurniawan',
                'nisn' => '0076543219',
                'nama_kejuaraan' => 'Olimpiade Olahraga Siswa Nasional (O2SN) Cabang Pencak Silat',
                'bidang' => 'Olahraga',
                'tingkat' => 'Nasional',
                'peringkat' => 'Juara 2',
                'tahun' => 2025,
                'penyelenggara' => 'Kemendikbudristek & IPSI',
                'keterangan' => 'Medali Perak Kategori Tanding Kelas C Putra',
            ],
            [
                'nama_siswa' => 'Dimas Arya Pamungkas',
                'nisn' => '0091122334',
                'nama_kejuaraan' => 'Kontes Robotika Pintar Pelajar',
                'bidang' => 'Teknologi & Robotik',
                'tingkat' => 'Nasional',
                'peringkat' => 'Juara 1',
                'tahun' => 2024,
                'penyelenggara' => 'Institut Teknologi Bandung',
                'keterangan' => 'Inovasi Robot Line Follower Pemilah Sampah Sekolah',
            ],
            [
                'nama_siswa' => 'Siti Aisyah Wardani',
                'nisn' => '0102233445',
                'nama_kejuaraan' => 'Musabaqah Tilawatil Quran (MTQ) Pelajar',
                'bidang' => 'Keagamaan',
                'tingkat' => 'Kabupaten',
                'peringkat' => 'Juara 1',
                'tahun' => 2025,
                'penyelenggara' => 'LPTQ & Dindikpora Banjarnegara',
                'keterangan' => 'Juara 1 Cabang Tilawah Jenjang SMP Kabupaten Banjarnegara',
            ],
            [
                'nama_siswa' => 'Bintang Gilang Ramadhan',
                'nisn' => '0085566778',
                'nama_kejuaraan' => 'Pekan Olahraga Pelajar Daerah (POPDA) Cabang Bulutangkis',
                'bidang' => 'Olahraga',
                'tingkat' => 'Provinsi',
                'peringkat' => 'Juara 3',
                'tahun' => 2025,
                'penyelenggara' => 'Disporapar Jateng',
                'keterangan' => 'Medali Perunggu Tunggal Putra SMP',
            ],
            [
                'nama_siswa' => 'Zahra Khairunnisa',
                'nisn' => '0113344556',
                'nama_kejuaraan' => 'Lomba Cipta Puisi & Menulis Esai Pendidikan Karakter',
                'bidang' => 'Seni & Budaya',
                'tingkat' => 'Kabupaten',
                'peringkat' => 'Juara 1',
                'tahun' => 2025,
                'penyelenggara' => 'Dindikpora Banjarnegara',
                'keterangan' => 'Pemenang Karya Terbaik Hari Pendidikan Nasional',
            ],
        ];

        $firstSchools = SatuanPendidikan::take(count($samplePrestasi))->get();
        foreach ($samplePrestasi as $i => $prestasi) {
            $sch = $firstSchools[$i] ?? SatuanPendidikan::first();
            PrestasiSiswa::create(array_merge($prestasi, [
                'satuan_pendidikan_id' => $sch->id,
                'kecamatan_id' => $sch->kecamatan_id,
            ]));
        }
    }
}
