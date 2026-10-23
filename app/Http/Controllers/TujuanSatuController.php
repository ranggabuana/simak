<?php

namespace App\Http\Controllers;

use App\Models\PendudukUsiaSekolah;
use App\Models\WilayahKecamatan;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TujuanSatuController extends Controller
{
    public function index(Request $request): View
    {
        $tahun = (int) $request->input('tahun', 2025);

        $pendudukList = PendudukUsiaSekolah::with('kecamatan')
            ->where('tahun', $tahun)
            ->get();

        $totals = [
            'usia_0_4' => $pendudukList->sum('usia_0_4_total'),
            'usia_5_6' => $pendudukList->sum('usia_5_6_total'),
            'usia_7_12' => $pendudukList->sum('usia_7_12_total'),
            'usia_13_15' => $pendudukList->sum('usia_13_15_total'),
            'usia_16_18' => $pendudukList->sum('usia_16_18_total'),
            'usia_16_30' => $pendudukList->sum('usia_16_30_total'),
            'usia_18_plus' => $pendudukList->sum('usia_18_plus_total'),
            'total' => $pendudukList->sum('total_penduduk'),
        ];

        return view('tujuan1.index', [
            'tahun' => $tahun,
            'pendudukList' => $pendudukList,
            'totals' => $totals,
            'kecamatanList' => WilayahKecamatan::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'usia_5_6_total' => 'required|integer|min:0',
            'usia_7_12_total' => 'required|integer|min:0',
            'usia_13_15_total' => 'required|integer|min:0',
            'usia_16_18_total' => 'required|integer|min:0',
        ]);

        $record = PendudukUsiaSekolah::findOrFail($id);

        $u5_6 = (int) $validated['usia_5_6_total'];
        $u7_12 = (int) $validated['usia_7_12_total'];
        $u13_15 = (int) $validated['usia_13_15_total'];
        $u16_18 = (int) $validated['usia_16_18_total'];
        $u0_4 = (int) $record->usia_0_4_total;
        $u18p = (int) $record->usia_18_plus_total;
        $total = $u0_4 + $u5_6 + $u7_12 + $u13_15 + $u16_18 + $u18p;

        $record->update([
            'usia_5_6_total' => $u5_6,
            'usia_7_12_total' => $u7_12,
            'usia_13_15_total' => $u13_15,
            'usia_16_18_total' => $u16_18,
            'total_penduduk' => $total,
        ]);

        ActivityLogger::log('UPDATE', 'tujuan1', "Memperbarui data kependudukan usia sekolah {$record->kecamatan->nama} (Tahun {$record->tahun})");

        return back()->with('success', 'Data kependudukan '.$record->kecamatan->nama.' berhasil diperbarui.');
    }

    public function store(Request $request): RedirectResponse
    {
        $tahun = (int) $request->input('tahun');

        $validated = $request->validate([
            'kecamatan_id' => [
                'required',
                'exists:wilayah_kecamatan,id',
                Rule::unique('penduduk_usia_sekolah')->where(function ($query) use ($tahun) {
                    return $query->where('tahun', $tahun);
                }),
            ],
            'tahun' => 'required|integer|min:2020|max:2035',
            'usia_0_4_total' => 'nullable|integer|min:0',
            'usia_5_6_total' => 'required|integer|min:0',
            'usia_7_12_total' => 'required|integer|min:0',
            'usia_13_15_total' => 'required|integer|min:0',
            'usia_16_18_total' => 'required|integer|min:0',
            'usia_18_plus_total' => 'nullable|integer|min:0',
        ], [
            'kecamatan_id.unique' => 'Data kependudukan untuk kecamatan tersebut pada tahun '.$tahun.' sudah terdaftar. Silakan gunakan tombol "Edit" pada tabel jika ingin memperbarui data.',
        ]);

        $u0_4 = (int) ($validated['usia_0_4_total'] ?? 0);
        $u5_6 = (int) $validated['usia_5_6_total'];
        $u7_12 = (int) $validated['usia_7_12_total'];
        $u13_15 = (int) $validated['usia_13_15_total'];
        $u16_18 = (int) $validated['usia_16_18_total'];
        $u18p = (int) ($validated['usia_18_plus_total'] ?? 0);
        $total = $u0_4 + $u5_6 + $u7_12 + $u13_15 + $u16_18 + $u18p;

        $record = PendudukUsiaSekolah::create([
            'kecamatan_id' => $validated['kecamatan_id'],
            'tahun' => $validated['tahun'],
            'usia_0_4_total' => $u0_4,
            'usia_5_6_total' => $u5_6,
            'usia_7_12_total' => $u7_12,
            'usia_13_15_total' => $u13_15,
            'usia_16_18_total' => $u16_18,
            'usia_18_plus_total' => $u18p,
            'total_penduduk' => $total,
        ]);

        $kec = WilayahKecamatan::find($validated['kecamatan_id']);

        ActivityLogger::log('CREATE', 'tujuan1', "Menambahkan data kependudukan usia sekolah {$kec->nama} (Tahun {$validated['tahun']})");

        return back()->with('success', 'Data kependudukan usia sekolah '.$kec->nama." (Tahun {$validated['tahun']}) berhasil ditambahkan.");
    }
}
