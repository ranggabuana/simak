<?php

namespace App\Http\Controllers;

use App\Models\PrestasiSiswa;
use App\Models\TargetRenstra;
use App\Services\KinerjaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected KinerjaService $kinerjaService
    ) {}

    public function index(Request $request): View
    {
        $tahun = (int) $request->input('tahun', 2025);

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun);
        $targetRenstraAll = TargetRenstra::orderBy('tahun')->get();
        $recentPrestasi = PrestasiSiswa::with('satuanPendidikan.kecamatan')
            ->where('tahun', $tahun)
            ->latest()
            ->take(5)
            ->get();

        $ewsData = $this->kinerjaService->hitungEarlyWarningSystem($tahun);

        return view('dashboard.index', [
            'tahun' => $tahun,
            'kabupaten' => $kabupaten,
            'kecamatanList' => $kecamatanList,
            'targetRenstraAll' => $targetRenstraAll,
            'recentPrestasi' => $recentPrestasi,
            'ewsData' => $ewsData,
        ]);
    }
}
