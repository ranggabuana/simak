<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['role', 'menu_key'])]
class RoleMenuPermission extends Model
{
    use HasFactory;

    /**
     * Role definitions with readable labels and styling.
     *
     * @return array<string, array{label: string, badge: string, desc: string}>
     */
    public static function getRoles(): array
    {
        return [
            'pimpinan' => [
                'label' => 'Kepala Dinas',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'desc' => 'Pimpinan Eksekutif Dinas Pendidikan',
            ],
            'perencana' => [
                'label' => 'Perencana',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
                'desc' => 'Analis AKIP Renstra & Penyusun Laporan Kinerja',
            ],
            'operator' => [
                'label' => 'Operator',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
                'desc' => 'Pengelola & Penginput Data Pendidikan Wilayah',
            ],
            'admin' => [
                'label' => 'Super Admin',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
                'desc' => 'Administrator Sistem & Pengelola Hak Akses',
            ],
        ];
    }

    /**
     * All system menu definitions grouped by sidebar sections.
     *
     * @return array<string, array{label: string, menus: array<string, array{name: string, route: string, desc: string}>}>
     */
    public static function getMenuDefinitions(): array
    {
        return [
            'ringkasan_master' => [
                'label' => 'Ringkasan & Master Data',
                'menus' => [
                    'dashboard' => ['name' => 'Dashboard Kinerja', 'route' => 'dashboard', 'desc' => 'Ringkasan capaian kinerja dan early warning system'],
                    'sekolah' => ['name' => 'Satuan Pendidikan (Sekolah)', 'route' => 'sekolah.index', 'desc' => 'Data basic sekolah, status negeri/swasta & akreditasi'],
                    'siswa' => ['name' => 'Partisipasi Siswa (Dapodik)', 'route' => 'siswa.index', 'desc' => 'Data murid jenjang PAUD, SD/MI, SMP/MTs'],
                    'guru' => ['name' => 'Data Guru & Sertifikasi', 'route' => 'guru.index', 'desc' => 'Data pendidik, status kepegawaian & sertifikasi'],
                    'tujuan1' => ['name' => 'Tujuan 1: Penduduk Usia Sekolah', 'route' => 'tujuan1.index', 'desc' => 'Distribusi agregat anak usia sekolah 20 kecamatan'],
                    'tujuan2' => ['name' => 'Tujuan 2: Prestasi Siswa', 'route' => 'tujuan2.index', 'desc' => 'Catatan prestasi akademik dan non-akademik siswa'],
                ],
            ],
            'hasil_indikator' => [
                'label' => 'Hasil & Indikator Kinerja',
                'menus' => [
                    'indikator_apk' => ['name' => 'APK (Partisipasi Kasar)', 'route' => 'indikator.apk', 'desc' => 'Analisis Angka Partisipasi Kasar jenjang SD & SMP'],
                    'indikator_apm' => ['name' => 'APM (Partisipasi Murni)', 'route' => 'indikator.apm', 'desc' => 'Analisis Angka Partisipasi Murni usia resmi sekolah'],
                    'indikator_aps' => ['name' => 'APS (Partisipasi Sekolah)', 'route' => 'indikator.aps', 'desc' => 'Analisis Angka Partisipasi Sekolah'],
                    'indikator_guru' => ['name' => 'Sertifikasi Guru', 'route' => 'indikator.guru', 'desc' => 'Indikator persentase guru bersertifikat pendidik'],
                    'indikator_paud' => ['name' => 'Akreditasi PAUD (Min. B)', 'route' => 'indikator.paud', 'desc' => 'Indikator satuan PAUD berakreditasi minimal B'],
                ],
            ],
            'renstra_akuntabilitas' => [
                'label' => 'Renstra & Akuntabilitas',
                'menus' => [
                    'renstra_target' => ['name' => 'Target Renstra (2025–2029)', 'route' => 'renstra.target', 'desc' => 'Target proyeksi IKK dokumen Renstra 5 tahunan'],
                    'renstra_realisasi' => ['name' => 'Realisasi Capaian Kinerja', 'route' => 'renstra.realisasi', 'desc' => 'Realisasi capaian riil indikator kinerja'],
                    'laporan_lkjip' => ['name' => 'Dokumen LKjIP Resmi (A4)', 'route' => 'laporan.lkjip', 'desc' => 'Dokumen resmi LKjIP siap cetak berstandar PermenPAN-RB'],
                ],
            ],
            'peta_ekspor' => [
                'label' => 'Peta & Ekspor',
                'menus' => [
                    'peta' => ['name' => 'Peta Sebaran 20 Kecamatan', 'route' => 'peta.index', 'desc' => 'Peta interaktif GIS capaian APK dan sebaran sekolah'],
                    'export' => ['name' => 'Unduh Data Excel (.xlsx)', 'route' => 'export.kecamatan', 'desc' => 'Fitur unduh data format Microsoft Excel resmi'],
                ],
            ],
            'pengaturan_sistem' => [
                'label' => 'Pengaturan & Tata Kelola',
                'menus' => [
                    'pengaturan' => ['name' => 'Pengaturan Aplikasi', 'route' => 'settings.update', 'desc' => 'Konfigurasi parameter sistem, instansi & Renstra'],
                    'manajemen_pengguna' => ['name' => 'Manajemen Pengguna & Hak Akses', 'route' => 'users.index', 'desc' => 'Kelola akun pengguna dan matriks hak akses menu'],
                    'audit' => ['name' => 'Log System (Audit Trail & Error)', 'route' => 'audit.index', 'desc' => 'Catatan jejak rekam digital perubahan data & log error aplikasi'],
                    'panduan' => ['name' => 'Panduan & SOP SAKIP', 'route' => 'panduan.index', 'desc' => 'Manual book, SOP dan glosarium teknis SAKIP'],
                ],
            ],
        ];
    }

    /**
     * Get all permitted menu keys for a role.
     *
     * @return array<int, string>
     */
    public static function getPermissionsForRole(string $role): array
    {
        if ($role === 'admin' || $role === 'superadmin') {
            $all = [];
            foreach (self::getMenuDefinitions() as $group) {
                foreach (array_keys($group['menus']) as $key) {
                    $all[] = $key;
                }
            }

            return $all;
        }

        return self::where('role', $role)->pluck('menu_key')->all();
    }
}
