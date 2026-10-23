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
        Schema::create('role_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role', 50); // pimpinan, perencana, operator
            $table->string('menu_key', 50);
            $table->timestamps();

            $table->unique(['role', 'menu_key']);
        });

        // Seed initial permissions for default roles
        $defaults = [
            'pimpinan' => [
                'dashboard',
                'indikator_apk', 'indikator_apm', 'indikator_aps', 'indikator_guru', 'indikator_paud',
                'renstra_target', 'renstra_realisasi', 'laporan_lkjip',
                'peta', 'export',
                'audit', 'panduan',
            ],
            'perencana' => [
                'dashboard',
                'sekolah', 'siswa', 'guru', 'tujuan1', 'tujuan2',
                'indikator_apk', 'indikator_apm', 'indikator_aps', 'indikator_guru', 'indikator_paud',
                'renstra_target', 'renstra_realisasi', 'laporan_lkjip',
                'peta', 'export',
                'audit', 'panduan',
            ],
            'operator' => [
                'dashboard',
                'sekolah', 'siswa', 'guru', 'tujuan1', 'tujuan2',
                'peta', 'export',
                'panduan',
            ],
        ];

        $now = now();
        $inserts = [];
        foreach ($defaults as $role => $keys) {
            foreach ($keys as $key) {
                $inserts[] = [
                    'role' => $role,
                    'menu_key' => $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('role_menu_permissions')->insert($inserts);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_menu_permissions');
    }
};
