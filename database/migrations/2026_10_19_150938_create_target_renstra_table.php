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
        Schema::create('target_renstra', function (Blueprint $table) {
            $table->id();
            $table->year('tahun')->unique()->index();
            $table->decimal('target_apk_paud', 5, 2)->default(0);
            $table->decimal('target_apk_sd', 5, 2)->default(0);
            $table->decimal('target_apk_smp', 5, 2)->default(0);
            $table->decimal('target_apk_sma', 5, 2)->default(0);
            $table->decimal('target_apm_paud', 5, 2)->default(0);
            $table->decimal('target_apm_sd', 5, 2)->default(0);
            $table->decimal('target_apm_smp', 5, 2)->default(0);
            $table->decimal('target_apm_sma', 5, 2)->default(0);
            $table->decimal('target_aps_paud', 5, 2)->default(0);
            $table->decimal('target_aps_sd', 5, 2)->default(0);
            $table->decimal('target_aps_smp', 5, 2)->default(0);
            $table->decimal('target_aps_sma', 5, 2)->default(0);
            $table->decimal('target_guru_sertifikasi', 5, 2)->default(0);
            $table->decimal('target_paud_akreditasi_b', 5, 2)->default(0);
            $table->decimal('target_akip', 5, 2)->default(0);
            $table->decimal('target_rls', 4, 2)->default(0);
            $table->decimal('target_hls', 4, 2)->default(0);
            $table->decimal('target_ikk_renstra', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_renstra');
    }
};
