<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('satuan_pendidikan', 'sekolah_id')) {
            Schema::table('satuan_pendidikan', function (Blueprint $table) {
                $table->string('sekolah_id', 40)->nullable()->unique()->after('id');
            });
        }

        if (! Schema::hasColumn('guru_pendidik', 'ptk_id')) {
            Schema::table('guru_pendidik', function (Blueprint $table) {
                $table->string('ptk_id', 40)->nullable()->unique()->after('id');
            });
        }

        if (Schema::hasTable('dapodik_sync_logs') && ! Schema::hasColumn('dapodik_sync_logs', 'purge_dummy')) {
            Schema::table('dapodik_sync_logs', function (Blueprint $table) {
                $table->boolean('purge_dummy')->default(false)->after('semester_id');
            });
        }

        // Siapkan data pembagi demografi penduduk_usia_sekolah untuk tahun 2026 (semester_id 20261)
        // jika belum tersedia, dengan menyalin basis demografi 2025 per kecamatan.
        if (Schema::hasTable('penduduk_usia_sekolah')) {
            $rows2025 = DB::table('penduduk_usia_sekolah')->where('tahun', 2025)->get();
            foreach ($rows2025 as $row) {
                $exists = DB::table('penduduk_usia_sekolah')
                    ->where('kecamatan_id', $row->kecamatan_id)
                    ->where('tahun', 2026)
                    ->exists();

                if (! $exists) {
                    $data = (array) $row;
                    unset($data['id']);
                    $data['tahun'] = 2026;
                    $data['created_at'] = now();
                    $data['updated_at'] = now();
                    DB::table('penduduk_usia_sekolah')->insert($data);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('satuan_pendidikan', 'sekolah_id')) {
            Schema::table('satuan_pendidikan', function (Blueprint $table) {
                $table->dropUnique(['sekolah_id']);
                $table->dropColumn('sekolah_id');
            });
        }

        if (Schema::hasColumn('guru_pendidik', 'ptk_id')) {
            Schema::table('guru_pendidik', function (Blueprint $table) {
                $table->dropUnique(['ptk_id']);
                $table->dropColumn('ptk_id');
            });
        }

        if (Schema::hasTable('dapodik_sync_logs') && Schema::hasColumn('dapodik_sync_logs', 'purge_dummy')) {
            Schema::table('dapodik_sync_logs', function (Blueprint $table) {
                $table->dropColumn('purge_dummy');
            });
        }
    }
};

