<?php

namespace App\Http\Controllers;

use App\Models\SatuanPendidikan;
use App\Services\KinerjaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetaSebaranController extends Controller
{
    public function __construct(
        protected KinerjaService $kinerjaService
    ) {}

    public function index(Request $request): View
    {
        $tahun = $this->resolveTahun($request);
        $indikator = $request->input('indikator', 'apk_sd'); // apk_sd, apm_sd, aps_sd, guru, paud

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun);

        $sekolahList = SatuanPendidikan::with(['kecamatan', 'partisipasiSiswa' => function ($q) use ($tahun) {
            $q->where('tahun', $tahun);
        }])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'npsn', 'nama_sekolah', 'jenjang', 'status', 'akreditasi', 'kecamatan_id', 'kepala_sekolah', 'alamat', 'latitude', 'longitude']);

        return view('peta.index', [
            'tahun' => $tahun,
            'indikator' => $indikator,
            'kabupaten' => $kabupaten,
            'kecamatanList' => $kecamatanList,
            'sekolahList' => $sekolahList,
        ]);
    }
}
