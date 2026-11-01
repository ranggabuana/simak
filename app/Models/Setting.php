<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected static ?array $cachedAll = null;

    /**
     * Nilai default konfigurasi aplikasi SIMAK jika belum tersimpan di database.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            // Identitas Instansi & Wilayah
            'nama_instansi' => 'Dinas Pendidikan, Kepemudaan dan Olahraga Kab. Banjarnegara',
            'alamat_instansi' => 'Jalan Mayjend Soetojo No. 51 Banjarnegara 53412',
            'telepon_instansi' => '(0286) 591244',
            'email_instansi' => 'dindikpora@banjarnegarakab.go.id',
            'website_instansi' => 'dindikpora.banjarnegarakab.go.id',
            'kota_dokumen' => 'Banjarnegara',

            // Pejabat Penandatangan Laporan Resmi
            'kepala_dinas_nama' => 'Dr. H. Teguh Handoko, S.Sos., M.Si.',
            'kepala_dinas_nip' => '19680715 199203 1 005',
            'kepala_dinas_pangkat' => 'Pembina Utama Muda (IV/c)',
            'kepala_dinas_jabatan' => 'Kepala Dinas Pendidikan, Kepemudaan dan Olahraga',
            'perencana_nama' => 'Subbag Perencanaan & Evaluasi',
            'perencana_nip' => '19790512 200501 1 008',
            'perencana_jabatan' => 'Kasubbag Program dan Evaluasi Kinerja',

            // Standar Kinerja & Target SPM
            'tahun_anggaran_default' => 2025,
            'periode_renstra' => '2025–2029',
            'ambang_batas_spm' => 85.00,
            'toleransi_deviasi' => 5.00,

            // Kontrol Akses & Kunci Data Historis
            'cut_off_date' => null,
            'kunci_data_historis' => '0',

            // Pengumuman Sistem / Banner Dashboard
            'banner_aktif' => '0',
            'banner_tipe' => 'info',
            'banner_pesan' => '',
        ];
    }

    /**
     * Ambil nilai pengaturan berdasarkan kunci.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $record = static::where('key', $key)->first();
            if ($record !== null && $record->value !== null) {
                return $record->value;
            }
        } catch (\Throwable) {
            // Fallback jika migrasi belum berjalan
        }

        if ($default !== null) {
            return $default;
        }

        $defaults = static::defaults();

        return $defaults[$key] ?? null;
    }

    /**
     * Simpan atau perbarui nilai pengaturan.
     */
    public static function set(string $key, mixed $value): void
    {
        static::$cachedAll = null;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );
    }

    /**
     * Ambil seluruh konfigurasi aplikasi lengkap beserta default.
     *
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        if (static::$cachedAll !== null) {
            return static::$cachedAll;
        }

        $defaults = static::defaults();

        try {
            if (Schema::hasTable('settings')) {
                $dbSettings = static::pluck('value', 'key')->toArray();

                static::$cachedAll = array_merge($defaults, array_filter($dbSettings, fn ($v) => $v !== null));

                return static::$cachedAll;
            }
        } catch (\Throwable) {
            // Ignore error jika belum migrate
        }

        static::$cachedAll = $defaults;

        return static::$cachedAll;
    }
}
