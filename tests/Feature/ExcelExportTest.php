<?php

namespace Tests\Feature;

use App\Models\GuruPendidik;
use App\Models\SatuanPendidikan;
use App\Models\User;
use App\Models\WilayahKecamatan;
use App\Services\ExcelExporter;
use Tests\TestCase;
use ZipArchive;

class ExcelExportTest extends TestCase
{
    protected function getAuthenticatedUser(): User
    {
        return User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
    }

    public function test_unduh_kecamatan_excel_returns_valid_xlsx(): void
    {
        $user = $this->getAuthenticatedUser();

        $response = $this->actingAs($user)->get(route('export.kecamatan'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('rekap_kinerja_kecamatan_', $response->headers->get('Content-Disposition'));
        $this->assertStringEndsWith('.xlsx"', $response->headers->get('Content-Disposition'));

        // Verify it is a valid Zip containing OpenXML files
        $content = $response->streamedContent();
        $tmp = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tmp, $content);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);
        $this->assertNotFalse($zip->locateName('xl/workbook.xml'));
        $this->assertNotFalse($zip->locateName('xl/worksheets/sheet1.xml'));
        $this->assertNotFalse($zip->locateName('xl/styles.xml'));

        // Ensure no scientific notation E+ in sheet1.xml
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringNotContainsString('E+', $sheetXml);

        $zip->close();
        @unlink($tmp);
    }

    public function test_unduh_guru_excel_preserves_nip_and_nuptk_as_text_without_scientific_notation(): void
    {
        $user = $this->getAuthenticatedUser();

        // Create a teacher with an 18-digit NIP and 16-digit NUPTK
        $kec = WilayahKecamatan::first() ?? WilayahKecamatan::factory()->create();
        $sekolah = SatuanPendidikan::first() ?? SatuanPendidikan::create([
            'npsn' => '20300101',
            'nama_sekolah' => 'SD Negeri 1 Batur',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'akreditasi' => 'A',
            'kecamatan_id' => $kec->id,
        ]);

        $testNip = '199001012015011001';
        $testNuptk = '1990010120150110';

        GuruPendidik::create([
            'satuan_pendidikan_id' => $sekolah->id,
            'kecamatan_id' => $kec->id,
            'nama' => 'Test Guru Identitas',
            'nip' => $testNip,
            'nuptk' => $testNuptk,
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'sudah_sertifikasi' => true,
            'bidang_studi' => 'Guru Kelas SD',
        ]);

        $response = $this->actingAs($user)->get(route('export.guru'));

        $response->assertStatus(200);
        $this->assertStringContainsString('data_guru_pendidik_banjarnegara.xlsx', $response->headers->get('Content-Disposition'));

        $content = $response->streamedContent();
        $tmp = tempnam(sys_get_temp_dir(), 'test_zip_guru_');
        file_put_contents($tmp, $content);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

        // 1. Assert that scientific notation (e.g. 1.99E+17) does NOT exist anywhere in sheet
        $this->assertStringNotContainsString('E+', $sheetXml);

        // 2. Assert that testNip and testNuptk exist intact as text inside inlineStr
        $this->assertStringContainsString('<t>'.$testNip.'</t>', $sheetXml);
        $this->assertStringContainsString('<t>'.$testNuptk.'</t>', $sheetXml);

        // 3. Assert that testNip is NOT rendered as numeric <v>...</v>
        $this->assertStringNotContainsString('<v>'.$testNip.'</v>', $sheetXml);

        $zip->close();
        @unlink($tmp);
    }

    public function test_excel_exporter_helper_text_detection(): void
    {
        $this->assertTrue(ExcelExporter::isTextIdentifierHeader('NIP'));
        $this->assertTrue(ExcelExporter::isTextIdentifierHeader('NUPTK'));
        $this->assertTrue(ExcelExporter::isTextIdentifierHeader('NPSN'));
        $this->assertTrue(ExcelExporter::isTextIdentifierHeader('NISN'));
        $this->assertTrue(ExcelExporter::isTextIdentifierHeader('Kode Wilayah'));
        $this->assertTrue(ExcelExporter::isTextIdentifierHeader('Telepon'));

        $this->assertFalse(ExcelExporter::isTextIdentifierHeader('Total Guru'));
        $this->assertFalse(ExcelExporter::isTextIdentifierHeader('APK SD (%)'));

        // An 18-digit NIP must never be rendered as a number
        $this->assertFalse(ExcelExporter::shouldRenderAsNumber('199001012015011001', false));
        // A 16-digit NUPTK must never be rendered as a number
        $this->assertFalse(ExcelExporter::shouldRenderAsNumber('1990010120150110', false));
        // An 8-digit NPSN under text column must not be number
        $this->assertFalse(ExcelExporter::shouldRenderAsNumber('20300101', true));
        // Standard count 350 under numeric column is a number
        $this->assertTrue(ExcelExporter::shouldRenderAsNumber(350, false));
        // Percentage 98.5 under numeric column is a number
        $this->assertTrue(ExcelExporter::shouldRenderAsNumber(98.50, false));
    }
}
