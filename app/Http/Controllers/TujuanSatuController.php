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
        $gender = $request->input('gender', 'all');

        $pendudukList = PendudukUsiaSekolah::with('kecamatan')
            ->where('tahun', $tahun)
            ->get();

        $totalsAll = [
            'usia_0_4' => $pendudukList->sum('usia_0_4_total'),
            'usia_5_6' => $pendudukList->sum('usia_5_6_total'),
            'usia_7_12' => $pendudukList->sum('usia_7_12_total'),
            'usia_13_15' => $pendudukList->sum('usia_13_15_total'),
            'usia_16_18' => $pendudukList->sum('usia_16_18_total'),
            'usia_16_30' => $pendudukList->sum('usia_16_30_total'),
            'usia_18_plus' => $pendudukList->sum('usia_18_plus_total'),
            'total' => $pendudukList->sum('total_penduduk'),
        ];

        $totalsL = [
            'usia_0_4' => $pendudukList->sum('usia_0_4_l'),
            'usia_5_6' => $pendudukList->sum('usia_5_6_l'),
            'usia_7_12' => $pendudukList->sum('usia_7_12_l'),
            'usia_13_15' => $pendudukList->sum('usia_13_15_l'),
            'usia_16_18' => $pendudukList->sum('usia_16_18_l'),
            'usia_16_30' => $pendudukList->sum('usia_16_30_l'),
            'usia_18_plus' => (int) round($pendudukList->sum('usia_18_plus_total') * 0.505),
            'total' => $pendudukList->sum('usia_0_4_l') + $pendudukList->sum('usia_5_6_l') + $pendudukList->sum('usia_7_12_l') + $pendudukList->sum('usia_13_15_l') + $pendudukList->sum('usia_16_18_l') + (int) round($pendudukList->sum('usia_18_plus_total') * 0.505),
        ];

        $totalsP = [
            'usia_0_4' => $pendudukList->sum('usia_0_4_p'),
            'usia_5_6' => $pendudukList->sum('usia_5_6_p'),
            'usia_7_12' => $pendudukList->sum('usia_7_12_p'),
            'usia_13_15' => $pendudukList->sum('usia_13_15_p'),
            'usia_16_18' => $pendudukList->sum('usia_16_18_p'),
            'usia_16_30' => $pendudukList->sum('usia_16_30_p'),
            'usia_18_plus' => (int) round($pendudukList->sum('usia_18_plus_total') * 0.495),
            'total' => $pendudukList->sum('usia_0_4_p') + $pendudukList->sum('usia_5_6_p') + $pendudukList->sum('usia_7_12_p') + $pendudukList->sum('usia_13_15_p') + $pendudukList->sum('usia_16_18_p') + (int) round($pendudukList->sum('usia_18_plus_total') * 0.495),
        ];

        $activeTotals = match ($gender) {
            'L' => $totalsL,
            'P' => $totalsP,
            default => $totalsAll,
        };

        $genderRecap = [
            'total_l' => $totalsL['total'],
            'total_p' => $totalsP['total'],
            'persen_l' => $totalsAll['total'] > 0 ? round(($totalsL['total'] / $totalsAll['total']) * 100, 1) : 0,
            'persen_p' => $totalsAll['total'] > 0 ? round(($totalsP['total'] / $totalsAll['total']) * 100, 1) : 0,
            'usia_sekolah_l' => $totalsL['usia_5_6'] + $totalsL['usia_7_12'] + $totalsL['usia_13_15'] + $totalsL['usia_16_18'],
            'usia_sekolah_p' => $totalsP['usia_5_6'] + $totalsP['usia_7_12'] + $totalsP['usia_13_15'] + $totalsP['usia_16_18'],
        ];

        return view('tujuan1.index', [
            'tahun' => $tahun,
            'gender' => $gender,
            'pendudukList' => $pendudukList,
            'totals' => $activeTotals,
            'totalsAll' => $totalsAll,
            'totalsL' => $totalsL,
            'totalsP' => $totalsP,
            'genderRecap' => $genderRecap,
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
