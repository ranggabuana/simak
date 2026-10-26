<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partisipasi_siswa', function (Blueprint $table) {
            $table->unsignedInteger('siswa_l')->default(0)->after('total_siswa_semua_usia');
            $table->unsignedInteger('siswa_p')->default(0)->after('siswa_l');
            $table->unsignedInteger('siswa_usia_sesuai_l')->default(0)->after('siswa_usia_sesuai');
            $table->unsignedInteger('siswa_usia_sesuai_p')->default(0)->after('siswa_usia_sesuai_l');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partisipasi_siswa', function (Blueprint $table) {
            $table->dropColumn(['siswa_l', 'siswa_p', 'siswa_usia_sesuai_l', 'siswa_usia_sesuai_p']);
        });
    }
};
