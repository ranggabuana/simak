<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('penduduk_usia_sekolah', function (Blueprint $table) {
            $table->unsignedInteger('usia_18_plus_l')->default(0)->after('usia_16_30_total');
            $table->unsignedInteger('usia_18_plus_p')->default(0)->after('usia_18_plus_l');
        });

        // Backfill existing records
        DB::table('penduduk_usia_sekolah')->get()->each(function ($row) {
            $l = (int) round($row->usia_18_plus_total * 0.505);
            $p = max(0, $row->usia_18_plus_total - $l);
            DB::table('penduduk_usia_sekolah')->where('id', $row->id)->update([
                'usia_18_plus_l' => $l,
                'usia_18_plus_p' => $p,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penduduk_usia_sekolah', function (Blueprint $table) {
            $table->dropColumn(['usia_18_plus_l', 'usia_18_plus_p']);
        });
    }
};
