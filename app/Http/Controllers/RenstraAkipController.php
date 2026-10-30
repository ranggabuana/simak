<?php

namespace App\Http\Controllers;

use App\Models\RealisasiKinerja;
use App\Models\TargetRenstra;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RenstraAkipController extends Controller
{
    public function target(Request $request): View
    {
        $targetList = TargetRenstra::orderBy('tahun')->get();
        $latestTahun = $targetList->max('tahun') ?? self::defaultTahun();
        $suggestedNextTahun = $latestTahun + 1;

        return view('renstra.target', [
            'targetList' => $targetList,
            'suggestedNextTahun' => $suggestedNextTahun,
        ]);
    }

    public function storeTarget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2020|max:2035|unique:target_renstra,tahun',
            'target_apk_paud' => 'required|numeric|min:0|max:150',
            'target_apk_sd' => 'required|numeric|min:0|max:150',
            'target_apk_smp' => 'required|numeric|min:0|max:150',
            'target_apk_sma' => 'required|numeric|min:0|max:150',
            'target_apm_paud' => 'required|numeric|min:0|max:100',
            'target_apm_sd' => 'required|numeric|min:0|max:100',
            'target_apm_smp' => 'required|numeric|min:0|max:100',
            'target_apm_sma' => 'required|numeric|min:0|max:100',
            'target_aps_paud' => 'nullable|numeric|min:0|max:100',
            'target_aps_sd' => 'nullable|numeric|min:0|max:100',
            'target_aps_smp' => 'nullable|numeric|min:0|max:100',
            'target_aps_sma' => 'nullable|numeric|min:0|max:100',
            'target_guru_sertifikasi' => 'required|numeric|min:0|max:100',
            'target_paud_akreditasi_b' => 'required|numeric|min:0|max:100',
            'target_akip' => 'required|numeric|min:0|max:100',
            'target_rls' => 'required|numeric|min:0|max:20',
            'target_hls' => 'required|numeric|min:0|max:25',
            'target_ikk_renstra' => 'required|numeric|min:0|max:100',
        ], [
            'tahun.unique' => 'Target Renstra untuk tahun ini sudah terdaftar di sistem. Silakan perbarui target tahun tersebut pada tombol Edit.',
            'tahun.required' => 'Tahun target perencanaan wajib diisi.',
            'tahun.min' => 'Tahun target minimal tahun 2020.',
            'tahun.max' => 'Tahun target maksimal tahun 2035.',
        ]);

        $target = TargetRenstra::create([
            'tahun' => (int) $validated['tahun'],
            'target_apk_paud' => $validated['target_apk_paud'],
            'target_apk_sd' => $validated['target_apk_sd'],
            'target_apk_smp' => $validated['target_apk_smp'],
            'target_apk_sma' => $validated['target_apk_sma'],
            'target_apm_paud' => $validated['target_apm_paud'],
            'target_apm_sd' => $validated['target_apm_sd'],
            'target_apm_smp' => $validated['target_apm_smp'],
            'target_apm_sma' => $validated['target_apm_sma'],
            'target_aps_paud' => $validated['target_aps_paud'] ?? 0,
            'target_aps_sd' => $validated['target_aps_sd'] ?? 0,
            'target_aps_smp' => $validated['target_aps_smp'] ?? 0,
            'target_aps_sma' => $validated['target_aps_sma'] ?? 0,
            'target_guru_sertifikasi' => $validated['target_guru_sertifikasi'],
            'target_paud_akreditasi_b' => $validated['target_paud_akreditasi_b'],
            'target_akip' => $validated['target_akip'],
            'target_rls' => $validated['target_rls'],
            'target_hls' => $validated['target_hls'],
            'target_ikk_renstra' => $validated['target_ikk_renstra'],
        ]);

        ActivityLogger::log('CREATE', 'renstra_target', "Menambahkan target renstra baru untuk tahun {$target->tahun}");

        return back()->with('success', "Target Renstra Tahun {$target->tahun} berhasil ditambahkan ke matriks.");
    }

    public function updateTarget(Request $request, int $id): RedirectResponse
    {
        $target = TargetRenstra::findOrFail($id);

        $validated = $request->validate([
            'target_apk_paud' => 'required|numeric|min:0|max:150',
            'target_apk_sd' => 'required|numeric|min:0|max:150',
            'target_apk_smp' => 'required|numeric|min:0|max:150',
            'target_apk_sma' => 'required|numeric|min:0|max:150',
            'target_apm_paud' => 'required|numeric|min:0|max:100',
            'target_apm_sd' => 'required|numeric|min:0|max:100',
            'target_apm_smp' => 'required|numeric|min:0|max:100',
            'target_apm_sma' => 'required|numeric|min:0|max:100',
            'target_aps_paud' => 'nullable|numeric|min:0|max:100',
            'target_aps_sd' => 'nullable|numeric|min:0|max:100',
            'target_aps_smp' => 'nullable|numeric|min:0|max:100',
            'target_aps_sma' => 'nullable|numeric|min:0|max:100',
            'target_guru_sertifikasi' => 'required|numeric|min:0|max:100',
            'target_paud_akreditasi_b' => 'required|numeric|min:0|max:100',
            'target_akip' => 'required|numeric|min:0|max:100',
            'target_rls' => 'required|numeric|min:0|max:20',
            'target_hls' => 'required|numeric|min:0|max:25',
            'target_ikk_renstra' => 'required|numeric|min:0|max:100',
        ]);

        $target->update([
            ...$validated,
            'target_aps_paud' => $validated['target_aps_paud'] ?? $target->target_aps_paud,
            'target_aps_sd' => $validated['target_aps_sd'] ?? $target->target_aps_sd,
            'target_aps_smp' => $validated['target_aps_smp'] ?? $target->target_aps_smp,
            'target_aps_sma' => $validated['target_aps_sma'] ?? $target->target_aps_sma,
        ]);

        ActivityLogger::log('UPDATE', 'renstra_target', "Memperbarui target renstra tahun {$target->tahun}");

        return back()->with('success', 'Target Renstra Tahun '.$target->tahun.' berhasil diperbarui.');
    }

    public function destroyTarget(int $id): RedirectResponse
    {
        $target = TargetRenstra::findOrFail($id);
        $tahun = $target->tahun;
        $target->delete();

        ActivityLogger::log('DELETE', 'renstra_target', "Menghapus data target renstra tahun {$tahun}");

        return back()->with('success', "Target Renstra Tahun {$tahun} berhasil dihapus dari sistem.");
    }

    public function realisasi(Request $request): View
    {
        $realisasiList = RealisasiKinerja::orderBy('tahun', 'desc')->get();
        $latestTahun = $realisasiList->max('tahun') ?? self::defaultTahun();
        $suggestedNextTahun = $latestTahun + 1;

        return view('renstra.realisasi', [
            'realisasiList' => $realisasiList,
            'suggestedNextTahun' => $suggestedNextTahun,
        ]);
    }

    public function storeRealisasi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2020|max:2035|unique:realisasi_kinerja,tahun',
            'capaian_ikk_renstra' => 'required|numeric|min:0|max:100',
            'capaian_akip_n' => 'required|numeric|min:0|max:100',
            'capaian_akip_prev' => 'required|numeric|min:0|max:100',
            'capaian_rls_n' => 'required|numeric|min:0|max:20',
            'capaian_rls_prev' => 'required|numeric|min:0|max:20',
            'capaian_hls_n' => 'required|numeric|min:0|max:25',
            'capaian_hls_prev' => 'required|numeric|min:0|max:25',
            'evaluasi_status' => 'required|in:Sangat Baik,Baik,Cukup,Kurang',
            'catatan_evaluasi' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ], [
            'tahun.unique' => 'Data Realisasi Kinerja untuk tahun ini sudah terdaftar di sistem. Silakan perbarui data tahun tersebut pada tombol Edit.',
            'tahun.required' => 'Tahun realisasi kinerja wajib diisi.',
            'tahun.min' => 'Tahun realisasi minimal tahun 2020.',
            'tahun.max' => 'Tahun realisasi maksimal tahun 2035.',
        ]);

        $realisasi = RealisasiKinerja::create($validated);

        ActivityLogger::log('CREATE', 'renstra_realisasi', "Menambahkan data realisasi capaian kinerja untuk tahun {$realisasi->tahun}");

        return back()->with('success', "Data Realisasi Capaian Kinerja Tahun {$realisasi->tahun} berhasil ditambahkan.");
    }

    public function updateRealisasi(Request $request, int $id): RedirectResponse
    {
        $realisasi = RealisasiKinerja::findOrFail($id);

        $validated = $request->validate([
            'capaian_ikk_renstra' => 'required|numeric|min:0|max:100',
            'capaian_akip_n' => 'required|numeric|min:0|max:100',
            'capaian_akip_prev' => 'required|numeric|min:0|max:100',
            'capaian_rls_n' => 'required|numeric|min:0|max:20',
            'capaian_rls_prev' => 'required|numeric|min:0|max:20',
            'capaian_hls_n' => 'required|numeric|min:0|max:25',
            'capaian_hls_prev' => 'required|numeric|min:0|max:25',
            'evaluasi_status' => 'required|in:Sangat Baik,Baik,Cukup,Kurang',
            'catatan_evaluasi' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ]);

        $realisasi->update($validated);

        ActivityLogger::log('UPDATE', 'renstra_realisasi', "Memperbarui data realisasi kinerja tahun {$realisasi->tahun}");

        return back()->with('success', 'Data Realisasi Kinerja Tahun '.$realisasi->tahun.' berhasil diperbarui.');
    }

    public function destroyRealisasi(int $id): RedirectResponse
    {
        $realisasi = RealisasiKinerja::findOrFail($id);
        $tahun = $realisasi->tahun;
        $realisasi->delete();

        ActivityLogger::log('DELETE', 'renstra_realisasi', "Menghapus data realisasi kinerja tahun {$tahun}");

        return back()->with('success', "Data Realisasi Capaian Kinerja Tahun {$tahun} berhasil dihapus dari sistem.");
    }
}
