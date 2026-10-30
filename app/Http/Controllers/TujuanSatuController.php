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
        $tahun = $this->resolveTahun($request);
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
            'usia_18_plus' => $pendudukList->sum('usia_18_plus_l'),
            'total' => $pendudukList->sum('usia_0_4_l') + $pendudukList->sum('usia_5_6_l') + $pendudukList->sum('usia_7_12_l') + $pendudukList->sum('usia_13_15_l') + $pendudukList->sum('usia_16_18_l') + $pendudukList->sum('usia_18_plus_l'),
        ];

        $totalsP = [
            'usia_0_4' => $pendudukList->sum('usia_0_4_p'),
            'usia_5_6' => $pendudukList->sum('usia_5_6_p'),
            'usia_7_12' => $pendudukList->sum('usia_7_12_p'),
            'usia_13_15' => $pendudukList->sum('usia_13_15_p'),
            'usia_16_18' => $pendudukList->sum('usia_16_18_p'),
            'usia_16_30' => $pendudukList->sum('usia_16_30_p'),
            'usia_18_plus' => $pendudukList->sum('usia_18_plus_p'),
            'total' => $pendudukList->sum('usia_0_4_p') + $pendudukList->sum('usia_5_6_p') + $pendudukList->sum('usia_7_12_p') + $pendudukList->sum('usia_13_15_p') + $pendudukList->sum('usia_16_18_p') + $pendudukList->sum('usia_18_plus_p'),
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
            'usia_0_4_l' => 'nullable|integer|min:0',
            'usia_0_4_p' => 'nullable|integer|min:0',
            'usia_0_4_total' => 'nullable|integer|min:0',
            'usia_5_6_l' => 'nullable|integer|min:0',
            'usia_5_6_p' => 'nullable|integer|min:0',
            'usia_5_6_total' => 'nullable|integer|min:0',
            'usia_7_12_l' => 'nullable|integer|min:0',
            'usia_7_12_p' => 'nullable|integer|min:0',
            'usia_7_12_total' => 'nullable|integer|min:0',
            'usia_13_15_l' => 'nullable|integer|min:0',
            'usia_13_15_p' => 'nullable|integer|min:0',
            'usia_13_15_total' => 'nullable|integer|min:0',
            'usia_16_18_l' => 'nullable|integer|min:0',
            'usia_16_18_p' => 'nullable|integer|min:0',
            'usia_16_18_total' => 'nullable|integer|min:0',
            'usia_18_plus_l' => 'nullable|integer|min:0',
            'usia_18_plus_p' => 'nullable|integer|min:0',
            'usia_18_plus_total' => 'nullable|integer|min:0',
        ]);

        $record = PendudukUsiaSekolah::findOrFail($id);

        $g0_4 = $this->resolveGenderGroup($validated, 'usia_0_4', (int) $record->usia_0_4_l, (int) $record->usia_0_4_p, (int) $record->usia_0_4_total);
        $g5_6 = $this->resolveGenderGroup($validated, 'usia_5_6', (int) $record->usia_5_6_l, (int) $record->usia_5_6_p, (int) $record->usia_5_6_total);
        $g7_12 = $this->resolveGenderGroup($validated, 'usia_7_12', (int) $record->usia_7_12_l, (int) $record->usia_7_12_p, (int) $record->usia_7_12_total);
        $g13_15 = $this->resolveGenderGroup($validated, 'usia_13_15', (int) $record->usia_13_15_l, (int) $record->usia_13_15_p, (int) $record->usia_13_15_total);
        $g16_18 = $this->resolveGenderGroup($validated, 'usia_16_18', (int) $record->usia_16_18_l, (int) $record->usia_16_18_p, (int) $record->usia_16_18_total);
        $g18p = $this->resolveGenderGroup($validated, 'usia_18_plus', (int) $record->usia_18_plus_l, (int) $record->usia_18_plus_p, (int) $record->usia_18_plus_total);

        $totalPenduduk = $g0_4['total'] + $g5_6['total'] + $g7_12['total'] + $g13_15['total'] + $g16_18['total'] + $g18p['total'];

        $record->update([
            'usia_0_4_l' => $g0_4['l'],
            'usia_0_4_p' => $g0_4['p'],
            'usia_0_4_total' => $g0_4['total'],
            'usia_5_6_l' => $g5_6['l'],
            'usia_5_6_p' => $g5_6['p'],
            'usia_5_6_total' => $g5_6['total'],
            'usia_7_12_l' => $g7_12['l'],
            'usia_7_12_p' => $g7_12['p'],
            'usia_7_12_total' => $g7_12['total'],
            'usia_13_15_l' => $g13_15['l'],
            'usia_13_15_p' => $g13_15['p'],
            'usia_13_15_total' => $g13_15['total'],
            'usia_16_18_l' => $g16_18['l'],
            'usia_16_18_p' => $g16_18['p'],
            'usia_16_18_total' => $g16_18['total'],
            'usia_18_plus_l' => $g18p['l'],
            'usia_18_plus_p' => $g18p['p'],
            'usia_18_plus_total' => $g18p['total'],
            'total_penduduk' => $totalPenduduk,
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
            'usia_0_4_l' => 'nullable|integer|min:0',
            'usia_0_4_p' => 'nullable|integer|min:0',
            'usia_0_4_total' => 'nullable|integer|min:0',
            'usia_5_6_l' => 'nullable|integer|min:0',
            'usia_5_6_p' => 'nullable|integer|min:0',
            'usia_5_6_total' => 'nullable|integer|min:0',
            'usia_7_12_l' => 'nullable|integer|min:0',
            'usia_7_12_p' => 'nullable|integer|min:0',
            'usia_7_12_total' => 'nullable|integer|min:0',
            'usia_13_15_l' => 'nullable|integer|min:0',
            'usia_13_15_p' => 'nullable|integer|min:0',
            'usia_13_15_total' => 'nullable|integer|min:0',
            'usia_16_18_l' => 'nullable|integer|min:0',
            'usia_16_18_p' => 'nullable|integer|min:0',
            'usia_16_18_total' => 'nullable|integer|min:0',
            'usia_18_plus_l' => 'nullable|integer|min:0',
            'usia_18_plus_p' => 'nullable|integer|min:0',
            'usia_18_plus_total' => 'nullable|integer|min:0',
        ], [
            'kecamatan_id.unique' => 'Data kependudukan untuk kecamatan tersebut pada tahun '.$tahun.' sudah terdaftar. Silakan gunakan tombol "Edit" pada tabel jika ingin memperbarui data.',
        ]);

        $g0_4 = $this->resolveGenderGroup($validated, 'usia_0_4');
        $g5_6 = $this->resolveGenderGroup($validated, 'usia_5_6');
        $g7_12 = $this->resolveGenderGroup($validated, 'usia_7_12');
        $g13_15 = $this->resolveGenderGroup($validated, 'usia_13_15');
        $g16_18 = $this->resolveGenderGroup($validated, 'usia_16_18');
        $g18p = $this->resolveGenderGroup($validated, 'usia_18_plus');

        $totalPenduduk = $g0_4['total'] + $g5_6['total'] + $g7_12['total'] + $g13_15['total'] + $g16_18['total'] + $g18p['total'];

        $record = PendudukUsiaSekolah::create([
            'kecamatan_id' => $validated['kecamatan_id'],
            'tahun' => $validated['tahun'],
            'usia_0_4_l' => $g0_4['l'],
            'usia_0_4_p' => $g0_4['p'],
            'usia_0_4_total' => $g0_4['total'],
            'usia_5_6_l' => $g5_6['l'],
            'usia_5_6_p' => $g5_6['p'],
            'usia_5_6_total' => $g5_6['total'],
            'usia_7_12_l' => $g7_12['l'],
            'usia_7_12_p' => $g7_12['p'],
            'usia_7_12_total' => $g7_12['total'],
            'usia_13_15_l' => $g13_15['l'],
            'usia_13_15_p' => $g13_15['p'],
            'usia_13_15_total' => $g13_15['total'],
            'usia_16_18_l' => $g16_18['l'],
            'usia_16_18_p' => $g16_18['p'],
            'usia_16_18_total' => $g16_18['total'],
            'usia_18_plus_l' => $g18p['l'],
            'usia_18_plus_p' => $g18p['p'],
            'usia_18_plus_total' => $g18p['total'],
            'total_penduduk' => $totalPenduduk,
        ]);

        $kec = WilayahKecamatan::find($validated['kecamatan_id']);

        ActivityLogger::log('CREATE', 'tujuan1', "Menambahkan data kependudukan usia sekolah {$kec->nama} (Tahun {$validated['tahun']})");

        return back()->with('success', 'Data kependudukan usia sekolah '.$kec->nama." (Tahun {$validated['tahun']}) berhasil ditambahkan.");
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{l: int, p: int, total: int}
     */
    private function resolveGenderGroup(array $input, string $prefix, int $defaultL = 0, int $defaultP = 0, int $defaultTotal = 0): array
    {
        $hasL = array_key_exists("{$prefix}_l", $input) && $input["{$prefix}_l"] !== null;
        $hasP = array_key_exists("{$prefix}_p", $input) && $input["{$prefix}_p"] !== null;
        $hasTotal = array_key_exists("{$prefix}_total", $input) && $input["{$prefix}_total"] !== null;

        if ($hasL || $hasP) {
            $l = (int) ($input["{$prefix}_l"] ?? $defaultL);
            $p = (int) ($input["{$prefix}_p"] ?? $defaultP);
            $total = $l + $p;

            return ['l' => $l, 'p' => $p, 'total' => $total];
        }

        if ($hasTotal) {
            $total = (int) $input["{$prefix}_total"];
            $l = (int) round($total * 0.505);
            $p = max(0, $total - $l);

            return ['l' => $l, 'p' => $p, 'total' => $total];
        }

        return ['l' => $defaultL, 'p' => $defaultP, 'total' => $defaultTotal];
    }
}
