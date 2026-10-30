<?php

namespace App\Http\Controllers;

use App\Models\PartisipasiSiswa;
use App\Models\PendudukUsiaSekolah;
use App\Models\RealisasiKinerja;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Ambil tahun anggaran/kinerja default secara dinamis (tanpa hardcode 2025).
     */
    public static function defaultTahun(): int
    {
        return (int) date('Y');
    }

    /**
     * Resolusi tahun aktif dari parameter URL (?tahun=...), session ('tahun_anggaran'),
     * atau fallback ke tahun berjalan secara dinamis.
     */
    protected function resolveTahun(Request $request): int
    {
        if ($request->filled('tahun')) {
            $tahun = (int) $request->input('tahun');
            if ($tahun >= 2020 && $tahun <= 2035) {
                if ($request->hasSession()) {
                    $request->session()->put('tahun_anggaran', $tahun);
                }

                return $tahun;
            }
        }

        if ($request->hasSession() && $request->session()->has('tahun_anggaran')) {
            $sessionTahun = (int) $request->session()->get('tahun_anggaran');
            if ($sessionTahun >= 2020 && $sessionTahun <= 2035) {
                return $sessionTahun;
            }
        }

        $default = self::defaultTahun();
        if ($request->hasSession()) {
            $request->session()->put('tahun_anggaran', $default);
        }

        return $default;
    }

    /**
     * Daftar pilihan tahun yang tersedia secara dinamis untuk filter Top Navbar.
     *
     * @return array<int, array{tahun: int, label: string}>
     */
    public static function availableTahunOptions(): array
    {
        $currentYear = (int) date('Y');
        $years = [$currentYear - 2, $currentYear - 1, $currentYear];

        try {
            $dbYears = collect()
                ->merge(PartisipasiSiswa::select('tahun')->distinct()->pluck('tahun'))
                ->merge(PendudukUsiaSekolah::select('tahun')->distinct()->pluck('tahun'))
                ->merge(RealisasiKinerja::select('tahun')->distinct()->pluck('tahun'))
                ->map(fn ($y) => (int) $y)
                ->filter(fn ($y) => $y >= 2020 && $y <= 2035)
                ->all();

            $years = array_values(array_unique(array_merge($years, $dbYears)));
        } catch (\Throwable) {
            $years = array_values(array_unique($years));
        }

        rsort($years);

        return array_map(function (int $y) use ($currentYear): array {
            if ($y === $currentYear) {
                $suffix = ' (Tahun Berjalan)';
            } elseif ($y === $currentYear - 1) {
                $suffix = ' (Tahun N-1)';
            } elseif ($y > $currentYear) {
                $suffix = ' (Proyeksi)';
            } else {
                $suffix = '';
            }

            return [
                'tahun' => $y,
                'label' => "Tahun {$y}{$suffix}",
            ];
        }, $years);
    }
}
