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
        Schema::create('satuan_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->string('npsn', 10)->unique()->index();
            $table->string('nama_sekolah', 150)->index();
            $table->enum('jenjang', ['PAUD', 'TK', 'SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PKBM'])->index();
            $table->enum('status', ['Negeri', 'Swasta'])->default('Negeri')->index();
            $table->enum('akreditasi', ['A', 'B', 'C', 'Belum Terakreditasi'])->default('Belum Terakreditasi')->index();
            $table->foreignId('kecamatan_id')->constrained('wilayah_kecamatan')->onDelete('cascade');
            $table->string('alamat')->nullable();
            $table->string('kepala_sekolah')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satuan_pendidikan');
    }
};
