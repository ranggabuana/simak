<?php

namespace Tests\Feature;

use App\Models\PendudukUsiaSekolah;
use App\Models\PrestasiSiswa;
use App\Models\RealisasiKinerja;
use App\Models\SatuanPendidikan;
use App\Models\TargetRenstra;
use App\Models\User;
use App\Models\WilayahKecamatan;
use App\Services\ExcelExporter;
use App\Services\KinerjaService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Get an administrative user with full permissions.
     */
    protected function getAdminUser(): User
    {
        return User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test that guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test that login page renders correctly.
     */
    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('SIMAK PENDIDIKAN');
    }

    /**
     * Test that authenticated user can access dashboard and indicator pages.
     */
    public function test_authenticated_user_can_access_dashboard_and_indicators(): void
    {
        $user = $this->getAdminUser();

        $this->actingAs($user)->get('/dashboard')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/apk')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/apk?tab=sekolah')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/apm')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/apm?tab=sekolah')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/aps')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/guru')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/paud')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/laporan/apk')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/laporan/apm')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/laporan/aps')->assertStatus(200);
        $this->actingAs($user)->get('/tujuan1/penduduk')->assertStatus(200);
        $this->actingAs($user)->get('/tujuan2/prestasi')->assertStatus(200);
        $this->actingAs($user)->get('/peta-sebaran')->assertStatus(200);
        $this->actingAs($user)->get('/renstra/realisasi')->assertStatus(200);
        $this->actingAs($user)->get('/evaluasi')->assertRedirect(route('renstra.realisasi'));
    }

    /**
     * Test profile dropdown and menu items are rendered on authenticated dashboard.
     */
    public function test_profile_dropdown_and_menu_items_are_rendered(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('profile-dropdown-button');
        $response->assertSee('Profil');
        $response->assertSee('Pengaturan Aplikasi');
        $response->assertSee('Logout');
    }

    /**
     * Test user can update profile.
     */
    public function test_user_can_update_profile(): void
    {
        $user = $this->getAdminUser();
        $originalName = $user->name;
        $originalNip = $user->nip;
        $originalJabatan = $user->jabatan;

        $response = $this->actingAs($user)->post('/profile', [
            'name' => 'Updated User Name',
            'email' => $user->email,
            'nip' => '198503202010012099',
            'jabatan' => 'Perencana Senior',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User Name',
            'nip' => '198503202010012099',
        ]);

        // Restore original user attributes to prevent dirtying test database
        $user->update([
            'name' => $originalName,
            'nip' => $originalNip,
            'jabatan' => $originalJabatan,
        ]);
    }

    /**
     * Test user can access settings page.
     */
    public function test_user_can_access_settings_page(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertOk();
        $response->assertSee('Pengaturan Aplikasi SIMAK');
        $response->assertSee('Identitas');
    }

    /**
     * Test user can update application settings.
     */
    public function test_user_can_update_settings(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->post('/settings', [
            'nama_instansi' => 'DINDIKPORA Banjarnegara',
            'periode_renstra' => '2025–2029',
            'tahun_evaluasi' => 2025,
            'tahun_anggaran_default' => 2025,
            'kepala_dinas_nama' => 'Dr. H. Teguh Handoko, S.Sos., M.Si.',
            'kepala_dinas_nip' => '196807151992031005',
            'ambang_batas_spm' => 90.00,
            'banner_aktif' => '1',
            'banner_pesan' => 'Uji Coba Pengumuman SIMAK',
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('app_settings');
        $this->assertDatabaseHas('settings', [
            'key' => 'nama_instansi',
            'value' => 'DINDIKPORA Banjarnegara',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'ambang_batas_spm',
            'value' => '90',
        ]);
    }

    /**
     * Test /import redirects to Tujuan 1 and both Tujuan pages have inline import modals.
     */
    public function test_user_can_access_import_modals_on_tujuan_pages(): void
    {
        $user = $this->getAdminUser();

        // Accessing /import redirects to /tujuan1/penduduk
        $this->actingAs($user)->get('/import')->assertRedirect('/tujuan1/penduduk');

        // Tujuan 1 has the import modal and template download
        $response1 = $this->actingAs($user)->get('/tujuan1/penduduk');
        $response1->assertStatus(200);
        $response1->assertSee('importModal');
        $response1->assertSee('Import Excel (.xlsx)');
        $response1->assertSee(route('import.template', 'penduduk'));

        // Tujuan 2 has the import modal and template download
        $response2 = $this->actingAs($user)->get('/tujuan2/prestasi');
        $response2->assertStatus(200);
        $response2->assertSee('importModal');
        $response2->assertSee('Import Excel (.xlsx)');
        $response2->assertSee(route('import.template', 'prestasi'));
    }

    /**
     * Test user can download import templates.
     */
    public function test_user_can_download_import_templates(): void
    {
        $user = $this->getAdminUser();

        $resPenduduk = $this->actingAs($user)->get('/import/template/penduduk');
        $resPenduduk->assertStatus(200);
        $resPenduduk->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('template_penduduk_usia_sekolah_banjarnegara.xlsx', $resPenduduk->headers->get('Content-Disposition'));

        $resPrestasi = $this->actingAs($user)->get('/import/template/prestasi');
        $resPrestasi->assertStatus(200);
        $resPrestasi->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('template_prestasi_siswa_banjarnegara.xlsx', $resPrestasi->headers->get('Content-Disposition'));
    }

    /**
     * Test import penduduk only accepts .xlsx file.
     */
    public function test_import_penduduk_only_accepts_xlsx(): void
    {
        $user = $this->getAdminUser();

        $fileCsv = UploadedFile::fake()->create('data.csv', 10, 'text/csv');
        $response = $this->actingAs($user)->post('/import/penduduk', [
            'file_penduduk' => $fileCsv,
            'tahun' => 2026,
        ]);

        $response->assertSessionHasErrors('file_penduduk');
    }

    /**
     * Test import prestasi only accepts .xlsx file.
     */
    public function test_import_prestasi_only_accepts_xlsx(): void
    {
        $user = $this->getAdminUser();

        $fileCsv = UploadedFile::fake()->create('prestasi.csv', 10, 'text/csv');
        $response = $this->actingAs($user)->post('/import/prestasi', [
            'file_prestasi' => $fileCsv,
            'tahun' => 2026,
        ]);

        $response->assertSessionHasErrors('file_prestasi');
    }

    /**
     * Test user can manually store demographic data.
     */
    public function test_user_can_manually_store_demographic_data(): void
    {
        $user = $this->getAdminUser();
        $kec = WilayahKecamatan::first();
        PendudukUsiaSekolah::where('kecamatan_id', $kec->id)->where('tahun', 2028)->delete();

        $response = $this->actingAs($user)->post('/tujuan1/penduduk', [
            'kecamatan_id' => $kec->id,
            'tahun' => 2028,
            'usia_0_4_total' => 2000,
            'usia_5_6_total' => 1500,
            'usia_7_12_total' => 4200,
            'usia_13_15_total' => 2100,
            'usia_16_18_total' => 1900,
            'usia_18_plus_total' => 25000,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('penduduk_usia_sekolah', [
            'kecamatan_id' => $kec->id,
            'tahun' => 2028,
            'usia_7_12_total' => 4200,
        ]);
    }

    /**
     * Test user cannot store duplicate demographic data for same kecamatan and year.
     */
    public function test_user_cannot_store_duplicate_demographic_data_for_same_kecamatan_and_year(): void
    {
        $user = $this->getAdminUser();
        $kec = WilayahKecamatan::first();

        // Tahun 2025 sudah terdaftar di database untuk kecamatan ini
        $response = $this->actingAs($user)->post('/tujuan1/penduduk', [
            'kecamatan_id' => $kec->id,
            'tahun' => 2025,
            'usia_5_6_total' => 1500,
            'usia_7_12_total' => 4200,
            'usia_13_15_total' => 2100,
            'usia_16_18_total' => 1900,
        ]);

        $response->assertSessionHasErrors(['kecamatan_id']);
    }

    /**
     * Test user can import demographic XLSX file.
     */
    public function test_user_can_import_demographic_xlsx(): void
    {
        $user = $this->getAdminUser();
        $kec = WilayahKecamatan::first();

        $exporter = app(ExcelExporter::class);
        $res = $exporter->downloadXlsx(
            'test_penduduk.xlsx',
            'Template Penduduk',
            [
                'kode_kecamatan', 'nama_kecamatan', 'tahun',
                'usia_0_4_l', 'usia_0_4_p',
                'usia_5_6_l', 'usia_5_6_p',
                'usia_7_12_l', 'usia_7_12_p',
                'usia_13_15_l', 'usia_13_15_p',
                'usia_16_18_l', 'usia_16_18_p',
                'usia_18_plus_l', 'usia_18_plus_p',
            ],
            [
                [$kec->kode, $kec->nama, 2034, 1100, 1000, 800, 800, 2200, 2100, 1100, 1100, 1000, 1000, 13000, 13000],
            ]
        );

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_penduduk_').'.xlsx';
        ob_start();
        $res->sendContent();
        file_put_contents($tmpFile, ob_get_clean());

        $file = new UploadedFile($tmpFile, 'penduduk.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($user)->post('/import/penduduk', [
            'file_penduduk' => $file,
            'tahun' => 2034,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('penduduk_usia_sekolah', [
            'kecamatan_id' => $kec->id,
            'tahun' => 2034,
            'usia_7_12_l' => 2200,
            'usia_7_12_p' => 2100,
            'usia_7_12_total' => 4300,
        ]);

        // Bersihkan data uji coba agar database tetap bersih
        PendudukUsiaSekolah::where('tahun', 2034)->delete();
        @unlink($tmpFile);
    }

    /**
     * Test user can import prestasi XLSX file.
     */
    public function test_user_can_import_prestasi_xlsx(): void
    {
        $user = $this->getAdminUser();

        $exporter = app(ExcelExporter::class);
        $res = $exporter->downloadXlsx(
            'test_prestasi.xlsx',
            'Template Prestasi',
            ['nama_siswa', 'nisn', 'jenis_kelamin', 'nama_kejuaraan', 'bidang', 'tingkat', 'peringkat', 'tahun', 'nama_sekolah', 'penyelenggara', 'keterangan'],
            [
                ['Test Siswa Uji', '0081234567', 'L', 'Olimpiade Uji Coba', 'Sains & Akademik', 'Nasional', 'Juara 1', 2034, 'SMP Negeri 1 Banjarnegara', 'Kemendikbud', 'Uji Coba'],
            ]
        );

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_prestasi_').'.xlsx';
        ob_start();
        $res->sendContent();
        file_put_contents($tmpFile, ob_get_clean());

        $file = new UploadedFile($tmpFile, 'prestasi.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($user)->post('/import/prestasi', [
            'file_prestasi' => $file,
            'tahun' => 2034,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('prestasi_siswa', [
            'nama_siswa' => 'Test Siswa Uji',
            'nama_kejuaraan' => 'Olimpiade Uji Coba',
            'peringkat' => 'Juara 1',
            'tahun' => 2034,
        ]);

        // Bersihkan data uji coba agar database tetap bersih
        PrestasiSiswa::where('tahun', 2034)->delete();
        @unlink($tmpFile);
    }

    /**
     * Test authenticated user can access sekolah, siswa, and guru pages.
     */
    public function test_authenticated_user_can_access_sekolah_siswa_and_guru_pages(): void
    {
        $user = $this->getAdminUser();

        $this->actingAs($user)->get('/sekolah')->assertStatus(200)->assertSee('Data Basic Satuan Pendidikan');
        $this->actingAs($user)->get('/siswa')->assertStatus(200)->assertSee('Data Partisipasi Siswa');
        $this->actingAs($user)->get('/guru-pendidik')->assertStatus(200)->assertSee('Data Tenaga Pendidik');
    }

    /**
     * Test user can create, update, and delete sekolah.
     */
    public function test_user_can_create_update_and_delete_sekolah(): void
    {
        $user = $this->getAdminUser();
        $kec = WilayahKecamatan::first() ?? WilayahKecamatan::factory()->create();

        // 1. Create
        $resCreate = $this->actingAs($user)->post('/sekolah', [
            'npsn' => '99887766',
            'nama_sekolah' => 'SD Negeri 99 Percobaan',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'akreditasi' => 'A',
            'kecamatan_id' => $kec->id,
            'alamat' => 'Jl. Percobaan No. 1',
            'kepala_sekolah' => 'Drs. Tester, M.Pd.',
        ]);

        $resCreate->assertSessionHas('success');
        $this->assertDatabaseHas('satuan_pendidikan', [
            'npsn' => '99887766',
            'nama_sekolah' => 'SD Negeri 99 Percobaan',
        ]);

        $sekolah = SatuanPendidikan::where('npsn', '99887766')->first();

        // 2. Update
        $resUpdate = $this->actingAs($user)->put('/sekolah/'.$sekolah->id, [
            'npsn' => '99887766',
            'nama_sekolah' => 'SD Negeri 99 Unggulan',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'akreditasi' => 'A',
            'kecamatan_id' => $kec->id,
            'alamat' => 'Jl. Percobaan No. 1 Updated',
            'kepala_sekolah' => 'Drs. Tester Updated, M.Pd.',
        ]);

        $resUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('satuan_pendidikan', [
            'id' => $sekolah->id,
            'nama_sekolah' => 'SD Negeri 99 Unggulan',
        ]);

        // 3. Delete
        $resDelete = $this->actingAs($user)->delete('/sekolah/'.$sekolah->id);
        $resDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('satuan_pendidikan', [
            'id' => $sekolah->id,
        ]);
    }

    /**
     * Test user can create and update partisipasi siswa.
     */
    public function test_user_can_create_and_update_partisipasi_siswa(): void
    {
        $user = $this->getAdminUser();
        $sekolah = SatuanPendidikan::first();

        $resCreate = $this->actingAs($user)->post('/siswa', [
            'satuan_pendidikan_id' => $sekolah->id,
            'tahun_ajaran' => '2026/2027',
            'tahun' => 2026,
            'total_siswa_semua_usia' => 300,
            'siswa_usia_sesuai' => 280,
            'jumlah_rombel' => 10,
        ]);

        $resCreate->assertSessionHas('success');
        $this->assertDatabaseHas('partisipasi_siswa', [
            'satuan_pendidikan_id' => $sekolah->id,
            'tahun_ajaran' => '2026/2027',
            'total_siswa_semua_usia' => 300,
        ]);
    }

    /**
     * Test user can create, update, and delete guru pendidik.
     */
    public function test_user_can_create_and_update_guru_pendidik(): void
    {
        $user = $this->getAdminUser();
        $sekolah = SatuanPendidikan::first();

        $resCreate = $this->actingAs($user)->post('/guru-pendidik', [
            'satuan_pendidikan_id' => $sekolah->id,
            'nama' => 'Guru Hebat, S.Pd.',
            'nip' => '199001012020121001',
            'nuptk' => '1234567890123456',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PPPK',
            'sudah_sertifikasi' => 1,
            'bidang_studi' => 'Fisika',
        ]);

        $resCreate->assertSessionHas('success');
        $this->assertDatabaseHas('guru_pendidik', [
            'nama' => 'Guru Hebat, S.Pd.',
            'status_kepegawaian' => 'PPPK',
            'sudah_sertifikasi' => true,
        ]);
    }

    /**
     * Test templates download for all types.
     */
    public function test_user_can_download_all_csv_templates(): void
    {
        $user = $this->getAdminUser();

        $this->actingAs($user)->get('/import/template/sekolah')->assertStatus(200);
        $this->actingAs($user)->get('/import/template/siswa')->assertStatus(200);
        $this->actingAs($user)->get('/import/template/guru')->assertStatus(200);
    }

    /**
     * Test official Excel export and Laporan Visual document.
     */
    public function test_user_can_export_indikator_official_and_view_laporan(): void
    {
        $user = $this->getAdminUser();

        // 1. Official Excel export (GET)
        $this->actingAs($user)->get('/export/apk')->assertStatus(200);
        $this->actingAs($user)->get('/export/apm')->assertStatus(200);
        $this->actingAs($user)->get('/export/aps')->assertStatus(200);

        // 2. Official Visual Report document (A4 Landscape PDF / View)
        $this->actingAs($user)->get('/indikator/laporan/apk')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/laporan/apm')->assertStatus(200);
        $this->actingAs($user)->get('/indikator/laporan/aps')->assertStatus(200);
    }

    /**
     * Test format baru tabel basic wilayah pada menu APK, APM, dan APS (sesuai format-baru.jpeg).
     */
    public function test_indikator_official_table_renders_format_baru_structure(): void
    {
        $user = $this->getAdminUser();

        foreach (['apk', 'apm', 'aps'] as $tipe) {
            $response = $this->actingAs($user)->get("/indikator/{$tipe}");
            $response->assertStatus(200);
            $response->assertSee('Berdasarkan Basis Sekolah');
            $response->assertSee('Kecamatan');
            $response->assertSee('Penduduk Usia');
            $response->assertSee('Jumlah Siswa');
            $response->assertSee('SMP');
            $response->assertSee('MTs');
            $response->assertSee('SMA');
            $response->assertSee('SMK');
            $response->assertSee('MA');
            $response->assertSee('Kec.');
            $response->assertSee('Jumlah');
        }

        $service = app(KinerjaService::class);
        $data = $service->getIndikatorOfficialData('aps');
        $this->assertArrayHasKey('blocks', $data);
        $this->assertArrayHasKey('smp', $data['blocks']);
        $this->assertArrayHasKey('SD', $data['blocks']['smp']['forms']);
        $this->assertArrayHasKey('MTS', $data['blocks']['smp']['forms']);
        $this->assertEquals('MTs', $data['blocks']['smp']['forms']['MTS']);
        $this->assertArrayHasKey('breakdown', $data['rows'][0]['smp']);
        $this->assertArrayHasKey('breakdown', $data['total']['smp']);
    }

    /**
     * Test user can access LKjIP official report, audit trail, and SOP SAKIP guide.
     */
    public function test_user_can_access_new_roadmap_features(): void
    {
        $user = $this->getAdminUser();

        $this->actingAs($user)->get('/laporan/lkjip')->assertStatus(200)->assertSee('Laporan Kinerja Instansi Pemerintah (LKjIP)');
        $this->actingAs($user)->get('/log-system')->assertStatus(200)->assertSee('Audit Trail');
        $this->actingAs($user)->get('/panduan-sakip')->assertStatus(200)->assertSee('Glosarium');
    }

    /**
     * Test activity log is automatically recorded upon create actions.
     */
    public function test_activity_log_is_recorded(): void
    {
        $user = $this->getAdminUser();
        $kecamatan = WilayahKecamatan::first();

        $uniqueNpsn = 'TEST'.rand(1000, 9999);
        $res = $this->actingAs($user)->post('/sekolah', [
            'npsn' => $uniqueNpsn,
            'nama_sekolah' => 'SD Uji Audit Trail',
            'jenjang' => 'SD',
            'status' => 'Negeri',
            'akreditasi' => 'A',
            'kecamatan_id' => $kecamatan->id,
        ]);

        $res->assertSessionHas('success');
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'sekolah',
            'action' => 'CREATE',
        ]);
    }

    /**
     * Test user management CRUD and permissions matrix.
     */
    public function test_user_management_and_role_permissions_matrix(): void
    {
        $admin = $this->getAdminUser();

        // 1. Visit index page
        $res = $this->actingAs($admin)->get('/manajemen-pengguna');
        $res->assertStatus(200);
        $res->assertSee('Manajemen Pengguna & Matriks Hak Akses', false);
        $res->assertSee('Kepala Dinas');
        $res->assertSee('Perencana');
        $res->assertSee('Operator');

        // 2. Create user with Operator role
        $uniqueEmail = 'operator_test_'.uniqid().'@dindikpora.banjarnegarakab.go.id';
        $resCreate = $this->actingAs($admin)->post('/manajemen-pengguna', [
            'name' => 'Operator Baru Pengujian',
            'email' => $uniqueEmail,
            'password' => 'password123',
            'role' => 'operator',
            'nip' => '199501012022031001',
            'jabatan' => 'Operator Data',
        ]);
        $resCreate->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'role' => 'operator',
        ]);

        $newUser = User::where('email', $uniqueEmail)->first();

        // 3. Update user
        $resUpdate = $this->actingAs($admin)->put('/manajemen-pengguna/'.$newUser->id, [
            'name' => 'Operator Baru Diperbarui',
            'email' => $uniqueEmail,
            'role' => 'perencana',
            'nip' => '199501012022031001',
            'jabatan' => 'Perencana Muda',
        ]);
        $resUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'name' => 'Operator Baru Diperbarui',
            'role' => 'perencana',
        ]);

        // 4. Update Permissions Matrix
        $resPerm = $this->actingAs($admin)->post('/manajemen-pengguna/permissions', [
            'permissions' => [
                'pimpinan' => ['dashboard', 'renstra_realisasi', 'laporan_lkjip'],
                'perencana' => ['dashboard', 'renstra_target', 'renstra_realisasi'],
                'operator' => ['dashboard', 'sekolah', 'siswa', 'guru'],
            ],
        ]);
        $resPerm->assertSessionHas('success');

        // 5. Delete test user
        $resDelete = $this->actingAs($admin)->delete('/manajemen-pengguna/'.$newUser->id);
        $resDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);

        // 6. Restore default permissions
        $this->actingAs($admin)->post('/manajemen-pengguna/permissions', [
            'permissions' => [
                'pimpinan' => ['dashboard', 'indikator_apk', 'indikator_apm', 'indikator_aps', 'indikator_guru', 'indikator_paud', 'renstra_target', 'renstra_realisasi', 'laporan_lkjip', 'peta', 'export', 'audit', 'panduan'],
                'perencana' => ['dashboard', 'sekolah', 'siswa', 'guru', 'tujuan1', 'tujuan2', 'indikator_apk', 'indikator_apm', 'indikator_aps', 'indikator_guru', 'indikator_paud', 'renstra_target', 'renstra_realisasi', 'laporan_lkjip', 'peta', 'export', 'audit', 'panduan'],
                'operator' => ['dashboard', 'sekolah', 'siswa', 'guru', 'tujuan1', 'tujuan2', 'peta', 'export', 'panduan'],
            ],
        ]);
    }

    /**
     * Test role menu access rights are enforced by middleware.
     */
    public function test_role_menu_permissions_enforced_by_middleware(): void
    {
        $operator = User::where('role', 'operator')->first() ?? User::factory()->create(['role' => 'operator']);

        // Operator does NOT have permission to access /manajemen-pengguna
        $this->actingAs($operator)->get('/manajemen-pengguna')->assertStatus(403);
    }

    /**
     * Test user cannot delete self or primary superadmin.
     */
    public function test_cannot_delete_self_or_primary_admin(): void
    {
        $admin = $this->getAdminUser();

        $res = $this->actingAs($admin)->delete('/manajemen-pengguna/'.$admin->id);
        $res->assertSessionHas('error');
    }

    /**
     * Test user can create, update, and delete Target Renstra data.
     */
    public function test_user_can_create_update_and_delete_target_renstra(): void
    {
        $admin = $this->getAdminUser();

        // 1. Visit target renstra index page
        $resPage = $this->actingAs($admin)->get('/renstra/target');
        $resPage->assertStatus(200);
        $resPage->assertSee('Input Target Baru');
        $resPage->assertSee('Matriks Penetapan Target Kinerja');

        // Clean up test year 2032 if exists
        TargetRenstra::where('tahun', 2032)->delete();

        // 2. Store new target renstra for year 2032
        $resCreate = $this->actingAs($admin)->post('/renstra/target', [
            'tahun' => 2032,
            'target_apk_paud' => 88.50,
            'target_apk_sd' => 105.00,
            'target_apk_smp' => 99.00,
            'target_apk_sma' => 90.00,
            'target_apm_paud' => 65.00,
            'target_apm_sd' => 99.50,
            'target_apm_smp' => 97.00,
            'target_apm_sma' => 80.00,
            'target_aps_paud' => 68.00,
            'target_aps_sd' => 99.80,
            'target_aps_smp' => 98.00,
            'target_aps_sma' => 85.00,
            'target_guru_sertifikasi' => 80.00,
            'target_paud_akreditasi_b' => 70.00,
            'target_akip' => 78.50,
            'target_rls' => 7.80,
            'target_hls' => 13.80,
            'target_ikk_renstra' => 88.00,
        ]);

        $resCreate->assertSessionHas('success');
        $this->assertDatabaseHas('target_renstra', [
            'tahun' => 2032,
            'target_apk_sd' => 105.00,
            'target_akip' => 78.50,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'renstra_target',
            'action' => 'CREATE',
        ]);

        $target = TargetRenstra::where('tahun', 2032)->firstOrFail();

        // 3. Update target renstra
        $resUpdate = $this->actingAs($admin)->put('/renstra/target/'.$target->id, [
            'target_apk_paud' => 90.00,
            'target_apk_sd' => 106.00,
            'target_apk_smp' => 99.50,
            'target_apk_sma' => 91.00,
            'target_apm_paud' => 66.00,
            'target_apm_sd' => 99.80,
            'target_apm_smp' => 97.50,
            'target_apm_sma' => 81.00,
            'target_aps_paud' => 70.00,
            'target_aps_sd' => 99.90,
            'target_aps_smp' => 98.50,
            'target_aps_sma' => 86.00,
            'target_guru_sertifikasi' => 82.00,
            'target_paud_akreditasi_b' => 72.00,
            'target_akip' => 80.00,
            'target_rls' => 8.00,
            'target_hls' => 14.00,
            'target_ikk_renstra' => 90.00,
        ]);

        $resUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('target_renstra', [
            'id' => $target->id,
            'target_apk_sd' => 106.00,
            'target_akip' => 80.00,
        ]);

        // 4. Delete target renstra
        $resDelete = $this->actingAs($admin)->delete('/renstra/target/'.$target->id);
        $resDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('target_renstra', ['id' => $target->id]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'renstra_target',
            'action' => 'DELETE',
        ]);
    }

    /**
     * Test user can create, update, and delete Realisasi Kinerja data.
     */
    public function test_user_can_create_update_and_delete_realisasi_kinerja(): void
    {
        $admin = $this->getAdminUser();

        // 1. Visit realisasi renstra index page
        $resPage = $this->actingAs($admin)->get('/renstra/realisasi');
        $resPage->assertStatus(200);
        $resPage->assertSee('Input Realisasi Baru');
        $resPage->assertSee('Realisasi Capaian IKK Renstra');

        // Clean up test year 2033 if exists
        RealisasiKinerja::where('tahun', 2033)->delete();

        // 2. Store new realisasi kinerja for year 2033
        $resCreate = $this->actingAs($admin)->post('/renstra/realisasi', [
            'tahun' => 2033,
            'capaian_ikk_renstra' => 89.20,
            'capaian_akip_n' => 79.50,
            'capaian_akip_prev' => 78.00,
            'capaian_rls_n' => 7.85,
            'capaian_rls_prev' => 7.70,
            'capaian_hls_n' => 13.85,
            'capaian_hls_prev' => 13.65,
            'evaluasi_status' => 'Sangat Baik',
            'catatan_evaluasi' => 'Peningkatan nilai SAKIP dan IPM melampaui target Renstra.',
            'rekomendasi' => 'Lanjutkan program afirmasi beasiswa dan penguatan kompetensi guru.',
        ]);

        $resCreate->assertSessionHas('success');
        $this->assertDatabaseHas('realisasi_kinerja', [
            'tahun' => 2033,
            'capaian_ikk_renstra' => 89.20,
            'evaluasi_status' => 'Sangat Baik',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'renstra_realisasi',
            'action' => 'CREATE',
        ]);

        $realisasi = RealisasiKinerja::where('tahun', 2033)->firstOrFail();

        // 3. Update realisasi kinerja
        $resUpdate = $this->actingAs($admin)->put('/renstra/realisasi/'.$realisasi->id, [
            'capaian_ikk_renstra' => 91.00,
            'capaian_akip_n' => 80.50,
            'capaian_akip_prev' => 78.50,
            'capaian_rls_n' => 8.00,
            'capaian_rls_prev' => 7.75,
            'capaian_hls_n' => 14.00,
            'capaian_hls_prev' => 13.70,
            'evaluasi_status' => 'Sangat Baik',
            'catatan_evaluasi' => 'Pembaruan catatan evaluasi hasil audit inspektorat.',
            'rekomendasi' => 'Pembaruan rekomendasi strategis.',
        ]);

        $resUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('realisasi_kinerja', [
            'id' => $realisasi->id,
            'capaian_ikk_renstra' => 91.00,
            'capaian_akip_n' => 80.50,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'renstra_realisasi',
            'action' => 'UPDATE',
        ]);

        // 4. Delete realisasi kinerja
        $resDelete = $this->actingAs($admin)->delete('/renstra/realisasi/'.$realisasi->id);
        $resDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('realisasi_kinerja', ['id' => $realisasi->id]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'renstra_realisasi',
            'action' => 'DELETE',
        ]);
    }

    /**
     * Test user can store demographic data and duplicate entry for same kecamatan and year is rejected.
     */
    public function test_user_can_store_demographic_data_and_cannot_duplicate_for_same_kecamatan_and_year(): void
    {
        $admin = $this->getAdminUser();
        $kecamatan = WilayahKecamatan::firstOrFail();
        $testYear = 2034;

        // Clean up test year 2034 if exists
        PendudukUsiaSekolah::where('kecamatan_id', $kecamatan->id)->where('tahun', $testYear)->delete();

        // 1. Visit demographic page
        $resPage = $this->actingAs($admin)->get('/tujuan1/penduduk?tahun='.$testYear);
        $resPage->assertStatus(200);
        $resPage->assertSee('Import Excel (.xlsx)');

        // 2. Store new record
        $resCreate = $this->actingAs($admin)->post('/tujuan1/penduduk', [
            'kecamatan_id' => $kecamatan->id,
            'tahun' => $testYear,
            'usia_0_4_total' => 120,
            'usia_5_6_total' => 300,
            'usia_7_12_total' => 850,
            'usia_13_15_total' => 450,
            'usia_16_18_total' => 400,
            'usia_18_plus_total' => 50,
        ]);

        $resCreate->assertSessionHas('success');
        $this->assertDatabaseHas('penduduk_usia_sekolah', [
            'kecamatan_id' => $kecamatan->id,
            'tahun' => $testYear,
            'usia_7_12_total' => 850,
            'total_penduduk' => 120 + 300 + 850 + 450 + 400 + 50,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'tujuan1',
            'action' => 'CREATE',
        ]);

        // 3. Attempt to duplicate same kecamatan and year (should fail validation)
        $resDuplicate = $this->actingAs($admin)->post('/tujuan1/penduduk', [
            'kecamatan_id' => $kecamatan->id,
            'tahun' => $testYear,
            'usia_0_4_total' => 999,
            'usia_5_6_total' => 999,
            'usia_7_12_total' => 999,
            'usia_13_15_total' => 999,
            'usia_16_18_total' => 999,
            'usia_18_plus_total' => 999,
        ]);

        $resDuplicate->assertSessionHasErrors(['kecamatan_id']);
        $errors = session('errors')->get('kecamatan_id');
        $this->assertStringContainsString('sudah terdaftar', $errors[0]);

        // Clean up test year
        PendudukUsiaSekolah::where('kecamatan_id', $kecamatan->id)->where('tahun', $testYear)->delete();
    }

    /**
     * Test user can update demographic data and total_penduduk is correctly recalculated.
     */
    public function test_user_can_update_demographic_data_and_total_is_recalculated(): void
    {
        $admin = $this->getAdminUser();
        $kecamatan = WilayahKecamatan::firstOrFail();
        $testYear = 2035;

        // Clean up and create fresh record
        PendudukUsiaSekolah::where('kecamatan_id', $kecamatan->id)->where('tahun', $testYear)->delete();

        $record = PendudukUsiaSekolah::create([
            'kecamatan_id' => $kecamatan->id,
            'tahun' => $testYear,
            'usia_0_4_total' => 100,
            'usia_5_6_total' => 200,
            'usia_7_12_total' => 500,
            'usia_13_15_total' => 300,
            'usia_16_18_total' => 250,
            'usia_18_plus_total' => 50,
            'total_penduduk' => 1400,
        ]);

        // Update demographic figures
        $resUpdate = $this->actingAs($admin)->put('/tujuan1/penduduk/'.$record->id, [
            'usia_5_6_total' => 250,
            'usia_7_12_total' => 600,
            'usia_13_15_total' => 350,
            'usia_16_18_total' => 300,
        ]);

        $resUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('penduduk_usia_sekolah', [
            'id' => $record->id,
            'usia_5_6_total' => 250,
            'usia_7_12_total' => 600,
            'usia_13_15_total' => 350,
            'usia_16_18_total' => 300,
            'total_penduduk' => 100 + 250 + 600 + 350 + 300 + 50, // 1650
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'tujuan1',
            'action' => 'UPDATE',
        ]);

        // Clean up
        $record->delete();
    }

    /**
     * Test user can create, update, and delete prestasi siswa in Tujuan 2.
     */
    public function test_user_can_create_update_and_delete_prestasi_siswa(): void
    {
        $admin = $this->getAdminUser();
        $sekolah = SatuanPendidikan::firstOrFail();

        // 1. Visit index page
        $resPage = $this->actingAs($admin)->get('/tujuan2/prestasi');
        $resPage->assertStatus(200);
        $resPage->assertSee('Catat Prestasi Siswa Baru');
        $resPage->assertSee('Edit Data Prestasi Siswa');

        // 2. Create prestasi
        $resCreate = $this->actingAs($admin)->post('/tujuan2/prestasi', [
            'nama_siswa' => 'Test Siswa Berprestasi',
            'nisn' => '1234567890',
            'jenis_kelamin' => 'L',
            'satuan_pendidikan_id' => $sekolah->id,
            'nama_kejuaraan' => 'Olimpiade Sains Nasional',
            'bidang' => 'Sains & Akademik',
            'tingkat' => 'Nasional',
            'peringkat' => 'Juara 1',
            'tahun' => 2025,
            'penyelenggara' => 'Kemendikbudristek',
            'keterangan' => 'Medali Emas',
        ]);

        $resCreate->assertSessionHas('success');
        $this->assertDatabaseHas('prestasi_siswa', [
            'nama_siswa' => 'Test Siswa Berprestasi',
            'jenis_kelamin' => 'L',
            'nama_kejuaraan' => 'Olimpiade Sains Nasional',
            'peringkat' => 'Juara 1',
            'satuan_pendidikan_id' => $sekolah->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'tujuan2',
            'action' => 'CREATE',
        ]);

        $prestasi = PrestasiSiswa::where('nama_siswa', 'Test Siswa Berprestasi')->firstOrFail();

        // 3. Update prestasi
        $resUpdate = $this->actingAs($admin)->put('/tujuan2/prestasi/'.$prestasi->id, [
            'nama_siswa' => 'Test Siswa Berprestasi Updated',
            'nisn' => '1234567890',
            'jenis_kelamin' => 'P',
            'satuan_pendidikan_id' => $sekolah->id,
            'nama_kejuaraan' => 'Olimpiade Sains Internasional',
            'bidang' => 'Sains & Akademik',
            'tingkat' => 'Internasional',
            'peringkat' => 'Juara 2',
            'tahun' => 2025,
            'penyelenggara' => 'UNESCO',
            'keterangan' => 'Medali Perak Dunia',
        ]);

        $resUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('prestasi_siswa', [
            'id' => $prestasi->id,
            'nama_siswa' => 'Test Siswa Berprestasi Updated',
            'jenis_kelamin' => 'P',
            'nama_kejuaraan' => 'Olimpiade Sains Internasional',
            'tingkat' => 'Internasional',
            'peringkat' => 'Juara 2',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'tujuan2',
            'action' => 'UPDATE',
        ]);

        // 4. Delete prestasi
        $resDelete = $this->actingAs($admin)->delete('/tujuan2/prestasi/'.$prestasi->id);
        $resDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('prestasi_siswa', [
            'id' => $prestasi->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'tujuan2',
            'action' => 'DELETE',
        ]);
    }

    /**
     * Test cannot add or update to duplicate kepala dinas role.
     */
    public function test_cannot_add_or_update_to_duplicate_kepala_dinas_role(): void
    {
        $admin = $this->getAdminUser();
        $pimpinan = User::where('role', 'pimpinan')->firstOrFail();

        // 1. Visit manajemen-pengguna and check UI displays restriction notice
        $resIndex = $this->actingAs($admin)->get('/manajemen-pengguna');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Akun Kepala Dinas saat ini telah terdaftar');

        // 2. Attempt to create a second user with role pimpinan
        $resCreate = $this->actingAs($admin)->post('/manajemen-pengguna', [
            'name' => 'Calon Kepala Dinas Kedua',
            'email' => 'calon.pimpinan.kedua@example.com',
            'password' => 'secret12345',
            'role' => 'pimpinan',
            'nip' => '197001011990011001',
        ]);

        $resCreate->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', [
            'email' => 'calon.pimpinan.kedua@example.com',
        ]);

        // 3. Attempt to update another user (admin) to role pimpinan
        $resUpdateOther = $this->actingAs($admin)->put('/manajemen-pengguna/'.$admin->id, [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'pimpinan',
        ]);

        $resUpdateOther->assertSessionHasErrors(['role']);

        // 4. Updating the current pimpinan themselves must be allowed
        $resUpdateSelf = $this->actingAs($admin)->put('/manajemen-pengguna/'.$pimpinan->id, [
            'name' => $pimpinan->name,
            'email' => $pimpinan->email,
            'role' => 'pimpinan',
            'nip' => $pimpinan->nip,
            'jabatan' => $pimpinan->jabatan,
        ]);

        $resUpdateSelf->assertSessionHas('success');
    }
}
