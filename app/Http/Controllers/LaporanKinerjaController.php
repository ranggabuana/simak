<?php

namespace App\Http\Controllers;

use App\Models\PrestasiSiswa;
use App\Models\RealisasiKinerja;
use App\Models\TargetRenstra;
use App\Models\User;
use App\Services\KinerjaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanKinerjaController extends Controller
{
    public function __construct(
        protected KinerjaService $kinerjaService
    ) {}

    public function lkjip(Request $request): View
    {
        $tahun = (int) $request->input('tahun', 2025);

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun);
        $target = TargetRenstra::where('tahun', $tahun)->first();
        $realisasi = RealisasiKinerja::where('tahun', $tahun)->first();
        $targetAll = TargetRenstra::orderBy('tahun')->get();
        $recentPrestasi = PrestasiSiswa::with('satuanPendidikan.kecamatan')
            ->where('tahun', $tahun)
            ->latest()
            ->take(10)
            ->get();

        $pimpinan = User::where('role', 'pimpinan')->first() ?? (object) [
            'name' => 'Dr. H. Teguh Handoko, S.Sos., M.Si.',
            'nip' => '196807151992031005',
            'jabatan' => 'Kepala Dinas Pendidikan, Kepemudaan dan Olahraga',
        ];

        // Gap analysis matrix
        $matrix = [];
        if ($target) {
            $matrix = [
                ['nama' => 'Angka Partisipasi Kasar (APK) SD/MI', 'target' => $target->target_apk_sd, 'realisasi' => $kabupaten['apk']['sd'], 'satuan' => '%'],
                ['nama' => 'Angka Partisipasi Murni (APM) SD/MI', 'target' => $target->target_apm_sd, 'realisasi' => $kabupaten['apm']['sd'], 'satuan' => '%'],
                ['nama' => 'Angka Partisipasi Kasar (APK) SMP/MTs', 'target' => $target->target_apk_smp, 'realisasi' => $kabupaten['apk']['smp'], 'satuan' => '%'],
                ['nama' => 'Angka Partisipasi Murni (APM) SMP/MTs', 'target' => $target->target_apm_smp, 'realisasi' => $kabupaten['apm']['smp'], 'satuan' => '%'],
                ['nama' => 'Persentase Guru Bersertifikat Pendidik', 'target' => $target->target_guru_sertifikasi, 'realisasi' => $kabupaten['guru']['persentase'], 'satuan' => '%'],
                ['nama' => 'Satuan PAUD Terakreditasi Minimal B', 'target' => $target->target_paud_akreditasi_b, 'realisasi' => $kabupaten['paud_akreditasi']['persentase'], 'satuan' => '%'],
            ];
        }

        return view('laporan.lkjip', [
            'tahun' => $tahun,
            'kabupaten' => $kabupaten,
            'kecamatanList' => $kecamatanList,
            'target' => $target,
            'realisasi' => $realisasi,
            'targetAll' => $targetAll,
            'recentPrestasi' => $recentPrestasi,
            'pimpinan' => $pimpinan,
            'matrix' => $matrix,
        ]);
    }
}
