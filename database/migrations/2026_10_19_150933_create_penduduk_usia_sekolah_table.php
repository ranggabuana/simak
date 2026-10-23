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
        Schema::create('penduduk_usia_sekolah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('wilayah_kecamatan')->onDelete('cascade');
            $table->year('tahun')->index();
            $table->unsignedInteger('usia_0_4_l')->default(0);
            $table->unsignedInteger('usia_0_4_p')->default(0);
            $table->unsignedInteger('usia_0_4_total')->default(0);
            $table->unsignedInteger('usia_5_6_l')->default(0);
            $table->unsignedInteger('usia_5_6_p')->default(0);
            $table->unsignedInteger('usia_5_6_total')->default(0);
            $table->unsignedInteger('usia_7_12_l')->default(0);
            $table->unsignedInteger('usia_7_12_p')->default(0);
            $table->unsignedInteger('usia_7_12_total')->default(0);
            $table->unsignedInteger('usia_13_15_l')->default(0);
            $table->unsignedInteger('usia_13_15_p')->default(0);
            $table->unsignedInteger('usia_13_15_total')->default(0);
            $table->unsignedInteger('usia_16_18_l')->default(0);
            $table->unsignedInteger('usia_16_18_p')->default(0);
            $table->unsignedInteger('usia_16_18_total')->default(0);
            $table->unsignedInteger('usia_16_30_l')->default(0);
            $table->unsignedInteger('usia_16_30_p')->default(0);
            $table->unsignedInteger('usia_16_30_total')->default(0);
            $table->unsignedInteger('usia_18_plus_total')->default(0);
            $table->unsignedInteger('total_penduduk')->default(0);
            $table->timestamps();

            $table->unique(['kecamatan_id', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penduduk_usia_sekolah');
    }
};
