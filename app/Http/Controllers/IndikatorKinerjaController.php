<?php

namespace App\Http\Controllers;

use App\Models\GuruPendidik;
use App\Models\SatuanPendidikan;
use App\Models\WilayahKecamatan;
use App\Services\KinerjaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndikatorKinerjaController extends Controller
{
    public function __construct(
        protected KinerjaService $kinerjaService
    ) {}

    public function apk(Request $request): View
    {
        $tahun = (int) $request->input('tahun', 2025);
        $tab = $request->input('tab', 'wilayah'); // wilayah | sekolah
        $kecamatanId = $request->input('kecamatan_id');
        $jenjang = $request->input('jenjang');

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun);
        $sekolahPaginated = $tab === 'sekolah'
            ? $this->kinerjaService->hitungKinerjaSekolah($kecamatanId ? (int) $kecamatanId : null, $jenjang, $tahun)
            : null;

        return view('indikator.apk', [
            'tahun' => $tahun,
            'tab' => $tab,
            'kecamatanId' => $kecamatanId,
            'jenjang' => $jenjang,
            'kabupaten' => $kabupaten,
            'kecamatanList' => $kecamatanList,
            'sekolahList' => $sekolahPaginated,
            'allKecamatan' => WilayahKecamatan::orderBy('nama')->get(),
        ]);
    }

    public function apm(Request $request): View
    {
        $tahun = (int) $request->input('tahun', 2025);
        $tab = $request->input('tab', 'wilayah');
        $kecamatanId = $request->input('kecamatan_id');
        $jenjang = $request->input('jenjang');

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun);
        $sekolahPaginated = $tab === 'sekolah'
            ? $this->kinerjaService->hitungKinerjaSekolah($kecamatanId ? (int) $kecamatanId : null, $jenjang, $tahun)
            : null;

        return view('indikator.apm', [
            'tahun' => $tahun,
            'tab' => $tab,
            'kecamatanId' => $kecamatanId,
            'jenjang' => $jenjang,
            'kabupaten' => $kabupaten,
            'kecamatanList' => $kecamatanList,
            'sekolahList' => $sekolahPaginated,
            'allKecamatan' => WilayahKecamatan::orderBy('nama')->get(),
        ]);
    }

    public function aps(Request $request): View
    {
        $tahun = (int) $request->input('tahun', 2025);
        $tab = $request->input('tab', 'wilayah');
        $kecamatanId = $request->input('kecamatan_id');
        $jenjang = $request->input('jenjang');

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun);
        $sekolahPaginated = $tab === 'sekolah'
            ? $this->kinerjaService->hitungKinerjaSekolah($kecamatanId ? (int) $kecamatanId : null, $jenjang, $tahun)
            : null;

        return view('indikator.aps', [
            'tahun' => $tahun,
            'tab' => $tab,
            'kecamatanId' => $kecamatanId,
            'jenjang' => $jenjang,
            'kabupaten' => $kabupaten,
            'kecamatanList' => $kecamatanList,
            'sekolahList' => $sekolahPaginated,
            'allKecamatan' => WilayahKecamatan::orderBy('nama')->get(),
        ]);
    }

    public function guru(Request $request): View
    {
        $tahun = (int) $request->input('tahun', 2025);
        $kecamatanId = $request->input('kecamatan_id');

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun);

        $guruQuery = GuruPendidik::with(['satuanPendidikan.kecamatan']);
        if ($kecamatanId) {
            $guruQuery->where('kecamatan_id', $kecamatanId);
        }
        $guruList = $guruQuery->paginate(15);

        return view('indikator.guru', [
            'tahun' => $tahun,
            'kecamatanId' => $kecamatanId,
            'kabupaten' => $kabupaten,
            'kecamatanList' => $kecamatanList,
            'guruList' => $guruList,
            'allKecamatan' => WilayahKecamatan::orderBy('nama')->get(),
        ]);
    }

    public function paud(Request $request): View
    {
        $tahun = (int) $request->input('tahun', 2025);
        $kecamatanId = $request->input('kecamatan_id');

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun);
        $kecamatanList = $this->kinerjaService->hitungKinerjaPerKecamatan($tahun);

        $paudQuery = SatuanPendidikan::whereIn('jenjang', ['PAUD', 'TK'])->with('kecamatan');
        if ($kecamatanId) {
            $paudQuery->where('kecamatan_id', $kecamatanId);
        }
        $paudList = $paudQuery->paginate(15);

        return view('indikator.paud', [
            'tahun' => $tahun,
            'kecamatanId' => $kecamatanId,
            'kabupaten' => $kabupaten,
            'kecamatanList' => $kecamatanList,
            'paudList' => $paudList,
            'allKecamatan' => WilayahKecamatan::orderBy('nama')->get(),
        ]);
    }
}
