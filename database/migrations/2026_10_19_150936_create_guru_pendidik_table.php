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
        Schema::create('guru_pendidik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satuan_pendidikan_id')->constrained('satuan_pendidikan')->onDelete('cascade');
            $table->foreignId('kecamatan_id')->constrained('wilayah_kecamatan')->onDelete('cascade');
            $table->string('nama', 150);
            $table->string('nip', 30)->nullable();
            $table->string('nuptk', 30)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'GTT', 'GTY', 'Honor'])->default('PNS');
            $table->boolean('sudah_sertifikasi')->default(false)->index();
            $table->string('bidang_studi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_pendidik');
    }
};
