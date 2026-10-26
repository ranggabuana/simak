<?php

use App\Models\PartisipasiSiswa;
use App\Models\PrestasiSiswa;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update Prestasi Siswa gender
        PrestasiSiswa::where('nama_siswa', 'like', '%Nabila%')
            ->orWhere('nama_siswa', 'like', '%Siti%')
            ->orWhere('nama_siswa', 'like', '%Zahra%')
            ->update(['jenis_kelamin' => 'P']);

        PrestasiSiswa::where('nama_siswa', 'like', '%Ahmad%')
            ->orWhere('nama_siswa', 'like', '%Rizky%')
            ->orWhere('nama_siswa', 'like', '%Dimas%')
            ->orWhere('nama_siswa', 'like', '%Bintang%')
            ->update(['jenis_kelamin' => 'L']);

        // 2. Update Partisipasi Siswa gender
        foreach (PartisipasiSiswa::all() as $p) {
            $l = (int) round($p->total_siswa_semua_usia * 0.51);
            $pCount = max(0, $p->total_siswa_semua_usia - $l);

            $sesuaiL = (int) round($p->siswa_usia_sesuai * 0.51);
            $sesuaiP = max(0, $p->siswa_usia_sesuai - $sesuaiL);

            $p->update([
                'siswa_l' => $l,
                'siswa_p' => $pCount,
                'siswa_usia_sesuai_l' => $sesuaiL,
                'siswa_usia_sesuai_p' => $sesuaiP,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
