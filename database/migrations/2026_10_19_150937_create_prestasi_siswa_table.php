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
        Schema::create('prestasi_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satuan_pendidikan_id')->constrained('satuan_pendidikan')->onDelete('cascade');
            $table->foreignId('kecamatan_id')->constrained('wilayah_kecamatan')->onDelete('cascade');
            $table->string('nama_siswa', 150);
            $table->string('nisn', 20)->nullable();
            $table->string('nama_kejuaraan', 200);
            $table->enum('bidang', ['Sains & Akademik', 'Olahraga', 'Seni & Budaya', 'Teknologi & Robotik', 'Keagamaan', 'Lainnya'])->index();
            $table->enum('tingkat', ['Kecamatan', 'Kabupaten', 'Provinsi', 'Nasional', 'Internasional'])->index();
            $table->enum('peringkat', ['Juara 1', 'Juara 2', 'Juara 3', 'Harapan 1', 'Harapan 2', 'Finalis'])->index();
            $table->year('tahun')->index();
            $table->string('penyelenggara')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestasi_siswa');
    }
};
