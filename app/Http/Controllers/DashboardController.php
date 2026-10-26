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
        $gender = $request->input('gender');

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun, $gender);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun, $gender);
        $targetRenstraAll = TargetRenstra::orderBy('tahun')->get();

        $prestasiQuery = PrestasiSiswa::with('satuanPendidikan.kecamatan')
            ->where('tahun', $tahun);
        if ($gender) {
            $prestasiQuery->where('jenis_kelamin', $gender);
        }
        $recentPrestasi = $prestasiQuery->latest()
            ->take(5)
            ->get();

        $ewsData = $this->kinerjaService->hitungEarlyWarningSystem($tahun);

        return view('dashboard.index', [
            'tahun' => $tahun,
            'gender' => $gender,
            'kabupaten' => $kabupaten,
            'kecamatanList' => $kecamatanList,
            'targetRenstraAll' => $targetRenstraAll,
            'recentPrestasi' => $recentPrestasi,
            'ewsData' => $ewsData,
        ]);
    }
}
