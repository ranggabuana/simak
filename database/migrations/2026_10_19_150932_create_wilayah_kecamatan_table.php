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
        Schema::create('wilayah_kecamatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique()->index();
            $table->string('nama', 100);
            $table->string('ibukota', 100)->nullable();
            $table->decimal('luas_wilayah', 8, 2)->nullable();
            $table->integer('jumlah_desa')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->mediumText('geojson')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah_kecamatan');
    }
};
