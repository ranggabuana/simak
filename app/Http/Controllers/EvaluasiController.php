<?php

namespace App\Http\Controllers;

use App\Models\RealisasiKinerja;
use App\Models\TargetRenstra;
use App\Services\KinerjaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluasiController extends Controller
{
    public function __construct(
        protected KinerjaService $kinerjaService
    ) {}

    public function index(Request $request): View
    {
        $tahun = (int) $request->input('tahun', 2025);

        $kabupaten = $this->kinerjaService->hitungKinerjaKabupaten($tahun);
        $target = TargetRenstra::where('tahun', $tahun)->first();
        $realisasi = RealisasiKinerja::where('tahun', $tahun)->first();

        // Calculate Gap Matrix
        $evaluasiMatrix = [];
        if ($target) {
            $evaluasiMatrix = [
                [
                    'indikator' => 'Angka Partisipasi Kasar (APK) SD/MI',
                    'target' => $target->target_apk_sd,
                    'realisasi' => $kabupaten['apk']['sd'],
                    'satuan' => '%',
                ],
                [
                    'indikator' => 'Angka Partisipasi Murni (APM) SD/MI',
                    'target' => $target->target_apm_sd,
                    'realisasi' => $kabupaten['apm']['sd'],
                    'satuan' => '%',
                ],
                [
                    'indikator' => 'Angka Partisipasi Kasar (APK) SMP/MTs',
                    'target' => $target->target_apk_smp,
                    'realisasi' => $kabupaten['apk']['smp'],
                    'satuan' => '%',
                ],
                [
                    'indikator' => 'Angka Partisipasi Murni (APM) SMP/MTs',
                    'target' => $target->target_apm_smp,
                    'realisasi' => $kabupaten['apm']['smp'],
                    'satuan' => '%',
                ],
                [
                    'indikator' => 'Persentase Guru Bersertifikat Pendidik',
                    'target' => $target->target_guru_sertifikasi,
                    'realisasi' => $kabupaten['guru']['persentase'],
                    'satuan' => '%',
                ],
                [
                    'indikator' => 'Persentase PAUD Terakreditasi Min. B',
                    'target' => $target->target_paud_akreditasi_b,
                    'realisasi' => $kabupaten['paud_akreditasi']['persentase'],
                    'satuan' => '%',
                ],
                [
                    'indikator' => 'Indeks AKIP DINDIKPORA',
                    'target' => $target->target_akip,
                    'realisasi' => $realisasi?->capaian_akip_n ?? 0,
                    'satuan' => 'Poin',
                ],
                [
                    'indikator' => 'Rata-rata Lama Sekolah (RLS)',
                    'target' => $target->target_rls,
                    'realisasi' => $realisasi?->capaian_rls_n ?? 0,
                    'satuan' => 'Tahun',
                ],
                [
                    'indikator' => 'Harapan Lama Sekolah (HLS)',
                    'target' => $target->target_hls,
                    'realisasi' => $realisasi?->capaian_hls_n ?? 0,
                    'satuan' => 'Tahun',
                ],
                [
                    'indikator' => 'Capaian IKK Renstra Pendidikan',
                    'target' => $target->target_ikk_renstra,
                    'realisasi' => $realisasi?->capaian_ikk_renstra ?? 0,
                    'satuan' => '%',
                ],
            ];
        }

        return view('evaluasi.index', [
            'tahun' => $tahun,
            'kabupaten' => $kabupaten,
            'target' => $target,
            'realisasi' => $realisasi,
            'evaluasiMatrix' => $evaluasiMatrix,
        ]);
    }

    public function simpanRekomendasi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'catatan_evaluasi' => 'required|string',
            'rekomendasi' => 'required|string',
            'evaluasi_status' => 'required|in:Sangat Baik,Baik,Cukup,Kurang',
        ]);

        RealisasiKinerja::updateOrCreate(
            ['tahun' => $validated['tahun']],
            [
                'evaluasi_status' => $validated['evaluasi_status'],
                'catatan_evaluasi' => $validated['catatan_evaluasi'],
                'rekomendasi' => $validated['rekomendasi'],
            ]
        );

        return back()->with('success', 'Catatan evaluasi dan rekomendasi kebijakan berhasil disimpan.');
    }
}
