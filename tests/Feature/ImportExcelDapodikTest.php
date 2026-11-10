<?php

namespace Tests\Feature;

use App\Models\PartisipasiSiswa;
use App\Models\SatuanPendidikan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportExcelDapodikTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
    }

    public function test_route_siswa_displays_import_excel_dapodik_button(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get(route('siswa.index'));

        $response->assertStatus(200);
        $response->assertSee('Import Excel Dapodik');
        $response->assertSee('importExcelDapodikModal');
    }

    public function test_import_excel_dapodik_updates_existing_siswa_data_by_npsn(): void
    {
        $user = $this->getAdminUser();
        $excelPath = base_path('DATA SISWA PER USIA- DAPODIK SEMUA JENJANG 9 NOVEMBER 2026.xlsx');

        if (! file_exists($excelPath)) {
            $this->markTestSkipped('File Excel Dapodik tidak ditemukan.');
        }

        // Ambil salah satu sekolah yang ada di Excel untuk verifikasi update, misal NPSN 69883142 (KB ARGO ARUM)
        $targetNpsn = '69883142';
        $sekolah = SatuanPendidikan::where('npsn', $targetNpsn)->first();

        $tahunTest = 2026;

        if ($sekolah) {
            // Set data awal berbeda untuk memastikan bahwa import berhasil menggantikan (meng-update) nilainya
            PartisipasiSiswa::updateOrCreate(
                [
                    'satuan_pendidikan_id' => $sekolah->id,
                    'tahun' => $tahunTest,
                ],
                [
                    'kecamatan_id' => $sekolah->kecamatan_id,
                    'tahun_ajaran' => "{$tahunTest}/".($tahunTest + 1),
                    'jenjang' => $sekolah->jenjang,
                    'total_siswa_semua_usia' => 10, // data awal sengaja diset 10
                    'siswa_l' => 5,
                    'siswa_p' => 5,
                    'siswa_usia_sesuai' => 10,
                    'siswa_usia_sesuai_l' => 5,
                    'siswa_usia_sesuai_p' => 5,
                    'siswa_usia_5_6' => 10,
                    'siswa_usia_7_12' => 0,
                    'siswa_usia_13_15' => 0,
                    'siswa_usia_16_18' => 0,
                    'siswa_usia_lainnya' => 0,
                    'jumlah_rombel' => 2,
                ]
            );
        }

        $uploadedFile = new UploadedFile(
            $excelPath,
            'DATA SISWA PER USIA- DAPODIK SEMUA JENJANG 9 NOVEMBER 2026.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($user)->post(route('siswa.import_dapodik_excel'), [
            'file_excel' => $uploadedFile,
            'tahun' => $tahunTest,
        ]);

        $response->assertRedirect(route('siswa.index', ['tahun' => $tahunTest]));
        $response->assertSessionHas('success');

        // Di Excel, KB ARGO ARUM (NPSN 69883142) memiliki:
        // PD_Total = 32, PD_L = 20, PD_P = 12
        // U5 L = 8, U5 P = 4, U6 L = 6, U6 P = 2 -> U5_6 = 20 (L: 14, P: 6)
        $sekolahAfter = SatuanPendidikan::where('npsn', $targetNpsn)->first();
        $this->assertNotNull($sekolahAfter);

        $partisipasiAfter = PartisipasiSiswa::where('satuan_pendidikan_id', $sekolahAfter->id)
            ->where('tahun', $tahunTest)
            ->first();

        $this->assertNotNull($partisipasiAfter);
        $this->assertEquals(32, $partisipasiAfter->total_siswa_semua_usia);
        $this->assertEquals(20, $partisipasiAfter->siswa_l);
        $this->assertEquals(12, $partisipasiAfter->siswa_p);
        $this->assertEquals(20, $partisipasiAfter->siswa_usia_5_6);
        $this->assertEquals(20, $partisipasiAfter->siswa_usia_sesuai); // Jenjang PAUD usia sesuai adalah 5-6
    }
}
