<?php

namespace App\Http\Controllers;

use App\Models\PartisipasiSiswa;
use App\Models\SatuanPendidikan;
use App\Models\WilayahKecamatan;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartisipasiSiswaController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $tahun = (int) $request->input('tahun', 2025);
        $jenjang = $request->input('jenjang');
        $kecamatanId = $request->input('kecamatan_id');
        $search = $request->input('search');
        $isOperatorScoped = false;

        if ($user && $user->role === 'operator' && $user->kecamatan_id) {
            $kecamatanId = $user->kecamatan_id;
            $isOperatorScoped = true;
        }

        $query = PartisipasiSiswa::with(['satuanPendidikan.kecamatan'])
            ->where('tahun', $tahun);

        if ($jenjang) {
            $query->where('jenjang', $jenjang);
        }
        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        // Server-Side Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tahun_ajaran', 'like', "%{$search}%")
                    ->orWhereHas('satuanPendidikan', function ($sq) use ($search) {
                        $sq->where('nama_sekolah', 'like', "%{$search}%")
                            ->orWhere('npsn', 'like', "%{$search}%");
                    })
                    ->orWhereHas('satuanPendidikan.kecamatan', function ($kq) use ($search) {
                        $kq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        // Server-Side Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'jenjang') {
            $query->orderBy('jenjang', $sortDir);
        } elseif ($sortBy === 'tahun_ajaran') {
            $query->orderBy('tahun_ajaran', $sortDir);
        } elseif ($sortBy === 'total_siswa_semua_usia') {
            $query->orderBy('total_siswa_semua_usia', $sortDir);
        } elseif ($sortBy === 'siswa_usia_sesuai') {
            $query->orderBy('siswa_usia_sesuai', $sortDir);
        } elseif ($sortBy === 'jumlah_rombel') {
            $query->orderBy('jumlah_rombel', $sortDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Per Page pagination
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10;
        }

        $siswaList = $query->paginate($perPage)->withQueryString();

        // Server-Side AJAX JSON Response
        if ($request->ajax() || $request->wantsJson() || $request->has('serverside')) {
            return response()->json([
                'html' => view('siswa._rows', ['siswaList' => $siswaList, 'tahun' => $tahun])->render(),
                'total' => $siswaList->total(),
                'filtered' => $siswaList->total(),
                'current_page' => $siswaList->currentPage(),
                'per_page' => $siswaList->perPage(),
                'last_page' => $siswaList->lastPage(),
                'from' => $siswaList->firstItem() ?? 0,
                'to' => $siswaList->lastItem() ?? 0,
            ]);
        }

        // Stats summary using fast SQL aggregation
        $statQuery = PartisipasiSiswa::where('tahun', $tahun);
        if ($isOperatorScoped) {
            $statQuery->where('kecamatan_id', $kecamatanId);
        }
        $statRow = (clone $statQuery)->selectRaw('
            COUNT(*) as total_sekolah,
            SUM(total_siswa_semua_usia) as total_siswa,
            SUM(siswa_usia_sesuai) as total_sesuai,
            SUM(jumlah_rombel) as total_rombel
        ')->first();

        $totalSiswa = (int) ($statRow->total_siswa ?? 0);
        $totalSesuai = (int) ($statRow->total_sesuai ?? 0);
        $persenSesuai = $totalSiswa > 0 ? round(($totalSesuai / $totalSiswa) * 100, 1) : 0;
        $totalRombel = (int) ($statRow->total_rombel ?? 0);

        $stats = [
            'total_siswa' => $totalSiswa,
            'total_sesuai' => $totalSesuai,
            'persen_sesuai' => $persenSesuai,
            'total_rombel' => $totalRombel,
            'total_sekolah_terdata' => (int) ($statRow->total_sekolah ?? 0),
        ];

        $sekolahQuery = SatuanPendidikan::orderBy('nama_sekolah');
        if ($isOperatorScoped) {
            $sekolahQuery->where('kecamatan_id', $kecamatanId);
        }

        return view('siswa.index', [
            'siswaList' => $siswaList,
            'stats' => $stats,
            'tahun' => $tahun,
            'kecamatanList' => WilayahKecamatan::orderBy('nama')->get(),
            'sekolahList' => $sekolahQuery->get(['id', 'npsn', 'nama_sekolah', 'jenjang', 'kecamatan_id']),
            'filterJenjang' => $jenjang,
            'filterKecamatan' => $kecamatanId,
            'isOperatorScoped' => $isOperatorScoped,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'satuan_pendidikan_id' => 'required|exists:satuan_pendidikan,id',
            'tahun_ajaran' => 'required|string|max:10',
            'tahun' => 'required|integer|min:2020|max:2035',
            'total_siswa_semua_usia' => 'required|integer|min:0',
            'siswa_usia_sesuai' => 'required|integer|min:0',
            'jumlah_rombel' => 'nullable|integer|min:0',
        ]);

        $sekolah = SatuanPendidikan::findOrFail($validated['satuan_pendidikan_id']);

        $totalSiswa = (int) $validated['total_siswa_semua_usia'];
        $siswaSesuai = min((int) $validated['siswa_usia_sesuai'], $totalSiswa);
        $rombel = (int) ($validated['jumlah_rombel'] ?? max(1, (int) ($totalSiswa / 28)));

        // Distribusi usia jenjang berdasarkan jenjang sekolah
        $u5_6 = ($sekolah->jenjang === 'PAUD' || $sekolah->jenjang === 'TK') ? $siswaSesuai : 0;
        $u7_12 = ($sekolah->jenjang === 'SD' || $sekolah->jenjang === 'MI') ? $siswaSesuai : 0;
        $u13_15 = ($sekolah->jenjang === 'SMP' || $sekolah->jenjang === 'MTS') ? $siswaSesuai : 0;
        $u16_18 = ($sekolah->jenjang === 'SMA' || $sekolah->jenjang === 'SMK' || $sekolah->jenjang === 'MA') ? $siswaSesuai : 0;

        PartisipasiSiswa::updateOrCreate(
            [
                'satuan_pendidikan_id' => $sekolah->id,
                'tahun_ajaran' => $validated['tahun_ajaran'],
            ],
            [
                'kecamatan_id' => $sekolah->kecamatan_id,
                'tahun' => $validated['tahun'],
                'jenjang' => $sekolah->jenjang,
                'total_siswa_semua_usia' => $totalSiswa,
                'siswa_usia_sesuai' => $siswaSesuai,
                'siswa_usia_5_6' => $u5_6,
                'siswa_usia_7_12' => $u7_12,
                'siswa_usia_13_15' => $u13_15,
                'siswa_usia_16_18' => $u16_18,
                'siswa_usia_lainnya' => max(0, $totalSiswa - $siswaSesuai),
                'jumlah_rombel' => $rombel,
            ]
        );

        ActivityLogger::log('CREATE', 'siswa', "Mencatat partisipasi murid {$sekolah->nama_sekolah} (T.A {$validated['tahun_ajaran']}: {$totalSiswa} murid)");

        return back()->with('success', "Data partisipasi peserta didik {$sekolah->nama_sekolah} (T.A {$validated['tahun_ajaran']}) berhasil disimpan.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $partisipasi = PartisipasiSiswa::findOrFail($id);

        $validated = $request->validate([
            'total_siswa_semua_usia' => 'required|integer|min:0',
            'siswa_usia_sesuai' => 'required|integer|min:0',
            'jumlah_rombel' => 'nullable|integer|min:0',
            'tahun_ajaran' => 'required|string|max:10',
            'tahun' => 'required|integer|min:2020|max:2035',
        ]);

        $sekolah = $partisipasi->satuanPendidikan;
        $totalSiswa = (int) $validated['total_siswa_semua_usia'];
        $siswaSesuai = min((int) $validated['siswa_usia_sesuai'], $totalSiswa);
        $rombel = (int) ($validated['jumlah_rombel'] ?? $partisipasi->jumlah_rombel);

        $u5_6 = ($sekolah->jenjang === 'PAUD' || $sekolah->jenjang === 'TK') ? $siswaSesuai : 0;
        $u7_12 = ($sekolah->jenjang === 'SD' || $sekolah->jenjang === 'MI') ? $siswaSesuai : 0;
        $u13_15 = ($sekolah->jenjang === 'SMP' || $sekolah->jenjang === 'MTS') ? $siswaSesuai : 0;
        $u16_18 = ($sekolah->jenjang === 'SMA' || $sekolah->jenjang === 'SMK' || $sekolah->jenjang === 'MA') ? $siswaSesuai : 0;

        $partisipasi->update([
            'total_siswa_semua_usia' => $totalSiswa,
            'siswa_usia_sesuai' => $siswaSesuai,
            'siswa_usia_5_6' => $u5_6,
            'siswa_usia_7_12' => $u7_12,
            'siswa_usia_13_15' => $u13_15,
            'siswa_usia_16_18' => $u16_18,
            'siswa_usia_lainnya' => max(0, $totalSiswa - $siswaSesuai),
            'jumlah_rombel' => $rombel,
            'tahun_ajaran' => $validated['tahun_ajaran'],
            'tahun' => $validated['tahun'],
        ]);

        ActivityLogger::log('UPDATE', 'siswa', "Memperbarui partisipasi murid {$sekolah->nama_sekolah} (T.A {$validated['tahun_ajaran']})");

        return back()->with('success', "Data partisipasi {$sekolah->nama_sekolah} berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $partisipasi = PartisipasiSiswa::findOrFail($id);
        $nama = $partisipasi->satuanPendidikan->nama_sekolah ?? 'Sekolah';
        $ta = $partisipasi->tahun_ajaran;
        $partisipasi->delete();

        ActivityLogger::log('DELETE', 'siswa', "Menghapus partisipasi murid {$nama} (T.A {$ta})");

        return back()->with('success', "Data partisipasi peserta didik {$nama} berhasil dihapus.");
    }

    public function import(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $request->validate([
            'file_siswa' => 'required|file|max:20480',
            'tahun' => 'required|integer|min:2020|max:2035',
        ]);

        $file = $request->file('file_siswa');
        $tahunDefault = (int) $request->input('tahun');

        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return back()->withErrors(['file_siswa' => 'Gagal membaca file CSV.']);
        }

        // BOM detection
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 0, ',');
        $delimiter = ',';
        if (! $header || count($header) < 3) {
            rewind($handle);
            $header = fgetcsv($handle, 0, ';');
            $delimiter = ';';
        }

        if (! $header) {
            fclose($handle);

            return back()->withErrors(['file_siswa' => 'Header CSV tidak valid atau file kosong.']);
        }

        $headerMap = [];
        foreach ($header as $idx => $col) {
            $clean = strtolower(trim($col));
            $clean = str_replace([' ', '-', '_'], '', $clean);
            $headerMap[$clean] = $idx;
        }

        $count = 0;
        $allSekolah = SatuanPendidikan::all();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($row) || count($row) < 3) {
                continue;
            }

            $npsn = trim($row[$headerMap['npsn'] ?? 0] ?? '');
            $namaSekolah = trim($row[$headerMap['namasekolah'] ?? $headerMap['nama'] ?? 1] ?? '');

            $sekolah = null;
            if (! empty($npsn)) {
                $sekolah = $allSekolah->firstWhere('npsn', $npsn);
            }
            if (! $sekolah && ! empty($namaSekolah)) {
                $sekolah = $allSekolah->first(function ($s) use ($namaSekolah) {
                    return strcasecmp($s->nama_sekolah, $namaSekolah) === 0 || stripos($s->nama_sekolah, $namaSekolah) !== false;
                });
            }

            if (! $sekolah) {
                continue;
            }

            // Scoping check for operator
            if ($user && $user->role === 'operator' && $user->kecamatan_id && $sekolah->kecamatan_id !== $user->kecamatan_id) {
                continue;
            }

            $ta = trim($row[$headerMap['tahunajaran'] ?? $headerMap['ta'] ?? 2] ?? '2024/2025');
            $rowTahun = isset($headerMap['tahun']) ? (int) ($row[$headerMap['tahun']] ?? $tahunDefault) : $tahunDefault;
            if ($rowTahun <= 0) {
                $rowTahun = $tahunDefault;
            }

            $totalSiswa = (int) ($row[$headerMap['totalsiswa'] ?? $headerMap['totalsiswasemuausia'] ?? $headerMap['jumlahsiswa'] ?? 3] ?? 0);
            $siswaSesuai = (int) ($row[$headerMap['siswasesuai'] ?? $headerMap['siswausiasesuai'] ?? 4] ?? (int) ($totalSiswa * 0.94));
            $siswaSesuai = min($siswaSesuai, $totalSiswa);

            $rombel = isset($headerMap['rombel']) || isset($headerMap['jumlahrombel'])
                ? (int) ($row[$headerMap['rombel'] ?? $headerMap['jumlahrombel']] ?? max(1, (int) ($totalSiswa / 28)))
                : max(1, (int) ($totalSiswa / 28));

            $jenjang = $sekolah->jenjang;
            $u5_6 = ($jenjang === 'PAUD' || $jenjang === 'TK') ? $siswaSesuai : 0;
            $u7_12 = ($jenjang === 'SD' || $jenjang === 'MI') ? $siswaSesuai : 0;
            $u13_15 = ($jenjang === 'SMP' || $jenjang === 'MTS') ? $siswaSesuai : 0;
            $u16_18 = ($jenjang === 'SMA' || $jenjang === 'SMK' || $jenjang === 'MA') ? $siswaSesuai : 0;

            PartisipasiSiswa::updateOrCreate(
                [
                    'satuan_pendidikan_id' => $sekolah->id,
                    'tahun_ajaran' => $ta,
                ],
                [
                    'kecamatan_id' => $sekolah->kecamatan_id,
                    'tahun' => $rowTahun,
                    'jenjang' => $jenjang,
                    'total_siswa_semua_usia' => $totalSiswa,
                    'siswa_usia_sesuai' => $siswaSesuai,
                    'siswa_usia_5_6' => $u5_6,
                    'siswa_usia_7_12' => $u7_12,
                    'siswa_usia_13_15' => $u13_15,
                    'siswa_usia_16_18' => $u16_18,
                    'siswa_usia_lainnya' => max(0, $totalSiswa - $siswaSesuai),
                    'jumlah_rombel' => $rombel,
                ]
            );

            $count++;
        }

        fclose($handle);

        if ($count === 0) {
            return back()->with('info', 'File berhasil diproses, namun tidak ada baris data siswa yang cocok dengan sekolah terdaftar.');
        }

        ActivityLogger::log('IMPORT', 'siswa', "Mengimpor {$count} data partisipasi murid Dapodik dari CSV");

        return back()->with('success', "Berhasil mengimpor {$count} data partisipasi siswa Dapodik.");
    }
}
