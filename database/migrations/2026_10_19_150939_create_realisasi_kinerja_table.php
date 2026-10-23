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
        Schema::create('realisasi_kinerja', function (Blueprint $table) {
            $table->id();
            $table->year('tahun')->unique()->index();
            $table->decimal('capaian_ikk_renstra', 5, 2)->default(0);
            $table->decimal('capaian_akip_n', 5, 2)->default(0);
            $table->decimal('capaian_akip_prev', 5, 2)->default(0);
            $table->decimal('capaian_rls_n', 4, 2)->default(0);
            $table->decimal('capaian_rls_prev', 4, 2)->default(0);
            $table->decimal('capaian_hls_n', 4, 2)->default(0);
            $table->decimal('capaian_hls_prev', 4, 2)->default(0);
            $table->enum('evaluasi_status', ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'])->default('Baik');
            $table->text('catatan_evaluasi')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_kinerja');
    }
};
