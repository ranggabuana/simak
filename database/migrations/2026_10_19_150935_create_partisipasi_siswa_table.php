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
        Schema::create('partisipasi_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satuan_pendidikan_id')->constrained('satuan_pendidikan')->onDelete('cascade');
            $table->foreignId('kecamatan_id')->constrained('wilayah_kecamatan')->onDelete('cascade');
            $table->string('tahun_ajaran', 10)->index();
            $table->year('tahun')->index();
            $table->enum('jenjang', ['PAUD', 'TK', 'SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PKBM'])->index();
            $table->unsignedInteger('total_siswa_semua_usia')->default(0);
            $table->unsignedInteger('siswa_usia_sesuai')->default(0);
            $table->unsignedInteger('siswa_usia_5_6')->default(0);
            $table->unsignedInteger('siswa_usia_7_12')->default(0);
            $table->unsignedInteger('siswa_usia_13_15')->default(0);
            $table->unsignedInteger('siswa_usia_16_18')->default(0);
            $table->unsignedInteger('siswa_usia_lainnya')->default(0);
            $table->unsignedInteger('jumlah_rombel')->default(0);
            $table->timestamps();

            $table->unique(['satuan_pendidikan_id', 'tahun_ajaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partisipasi_siswa');
    }
};
