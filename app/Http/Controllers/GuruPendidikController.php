<?php

namespace App\Http\Controllers;

use App\Models\GuruPendidik;
use App\Models\SatuanPendidikan;
use App\Models\WilayahKecamatan;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuruPendidikController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $kecamatanId = $request->input('kecamatan_id');
        $isOperatorScoped = false;

        if ($user && $user->role === 'operator' && $user->kecamatan_id) {
            $kecamatanId = $user->kecamatan_id;
            $isOperatorScoped = true;
        }

        $jenjang = $request->input('jenjang');
        $statusPegawai = $request->input('status_kepegawaian');
        $sertifikasi = $request->input('sertifikasi');
        $jenisKelamin = $request->input('jenis_kelamin');
        $search = $request->input('search');

        $query = GuruPendidik::with(['satuanPendidikan.kecamatan']);

        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }
        if ($jenjang) {
            $query->whereHas('satuanPendidikan', function ($q) use ($jenjang) {
                $q->where('jenjang', $jenjang);
            });
        }
        if ($statusPegawai) {
            $query->where('status_kepegawaian', $statusPegawai);
        }
        if ($sertifikasi !== null && $sertifikasi !== '') {
            $query->where('sudah_sertifikasi', (bool) $sertifikasi);
        }
        if ($jenisKelamin) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        // Server-Side Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nuptk', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('bidang_studi', 'like', "%{$search}%")
                    ->orWhereHas('satuanPendidikan', function ($sq) use ($search) {
                        $sq->where('nama_sekolah', 'like', "%{$search}%")
                            ->orWhere('npsn', 'like', "%{$search}%");
                    });
            });
        }

        // Server-Side Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'nama') {
            $query->orderBy('nama', $sortDir);
        } elseif ($sortBy === 'nuptk') {
            $query->orderBy('nuptk', $sortDir);
        } elseif ($sortBy === 'status_kepegawaian') {
            $query->orderBy('status_kepegawaian', $sortDir);
        } elseif ($sortBy === 'sudah_sertifikasi') {
            $query->orderBy('sudah_sertifikasi', $sortDir);
        } elseif ($sortBy === 'bidang_studi') {
            $query->orderBy('bidang_studi', $sortDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Per Page pagination
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10;
        }

        $guruList = $query->paginate($perPage)->withQueryString();

        // Server-Side AJAX JSON Response
        if ($request->ajax() || $request->wantsJson() || $request->has('serverside')) {
            return response()->json([
                'html' => view('guru._rows', ['guruList' => $guruList])->render(),
                'total' => $guruList->total(),
                'filtered' => $guruList->total(),
                'current_page' => $guruList->currentPage(),
                'per_page' => $guruList->perPage(),
                'last_page' => $guruList->lastPage(),
                'from' => $guruList->firstItem() ?? 0,
                'to' => $guruList->lastItem() ?? 0,
            ]);
        }

        // Fast Stats Query using SQL aggregate (0 MB extra memory, ~1ms execution)
        $statQuery = GuruPendidik::query();
        if ($isOperatorScoped) {
            $statQuery->where('kecamatan_id', $kecamatanId);
        }
        $statRow = (clone $statQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as total_l,
            SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as total_p,
            SUM(CASE WHEN sudah_sertifikasi = 1 THEN 1 ELSE 0 END) as sertifikasi,
            SUM(CASE WHEN sudah_sertifikasi = 1 AND jenis_kelamin = 'L' THEN 1 ELSE 0 END) as sertifikasi_l,
            SUM(CASE WHEN sudah_sertifikasi = 1 AND jenis_kelamin = 'P' THEN 1 ELSE 0 END) as sertifikasi_p,
            SUM(CASE WHEN status_kepegawaian = 'PNS' THEN 1 ELSE 0 END) as pns,
            SUM(CASE WHEN status_kepegawaian = 'PPPK' THEN 1 ELSE 0 END) as pppk,
            SUM(CASE WHEN status_kepegawaian IN ('GTT', 'GTY', 'Honor') THEN 1 ELSE 0 END) as non_asn
        ")->first();

        $total = (int) ($statRow->total ?? 0);
        $sertifCount = (int) ($statRow->sertifikasi ?? 0);
        $belumSertif = $total - $sertifCount;
        $persen = $total > 0 ? round(($sertifCount / $total) * 100, 2) : 0;

        $totalL = (int) ($statRow->total_l ?? 0);
        $totalP = (int) ($statRow->total_p ?? 0);
        $sertifL = (int) ($statRow->sertifikasi_l ?? 0);
        $sertifP = (int) ($statRow->sertifikasi_p ?? 0);
        $persenL = $totalL > 0 ? round(($sertifL / $totalL) * 100, 1) : 0;
        $persenP = $totalP > 0 ? round(($sertifP / $totalP) * 100, 1) : 0;

        $stats = [
            'total' => $total,
            'sertifikasi' => $sertifCount,
            'belum_sertifikasi' => $belumSertif,
            'persentase' => $persen,
            'total_l' => $totalL,
            'total_p' => $totalP,
            'sertifikasi_l' => $sertifL,
            'sertifikasi_p' => $sertifP,
            'persen_l' => $persenL,
            'persen_p' => $persenP,
            'pns' => (int) ($statRow->pns ?? 0),
            'pppk' => (int) ($statRow->pppk ?? 0),
            'non_asn' => (int) ($statRow->non_asn ?? 0),
        ];

        $sekolahQuery = SatuanPendidikan::orderBy('nama_sekolah');
        if ($isOperatorScoped) {
            $sekolahQuery->where('kecamatan_id', $kecamatanId);
        }

        return view('guru.index', [
            'guruList' => $guruList,
            'stats' => $stats,
            'kecamatanList' => WilayahKecamatan::orderBy('nama')->get(),
            'sekolahList' => $sekolahQuery->get(['id', 'nama_sekolah', 'npsn', 'jenjang', 'kecamatan_id']),
            'filterKecamatan' => $kecamatanId,
            'filterJenjang' => $jenjang,
            'filterStatus' => $statusPegawai,
            'filterSertifikasi' => $sertifikasi,
            'filterJenisKelamin' => $jenisKelamin,
            'isOperatorScoped' => $isOperatorScoped,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'satuan_pendidikan_id' => 'required|exists:satuan_pendidikan,id',
            'nama' => 'required|string|max:150',
            'nip' => 'nullable|string|max:30',
            'nuptk' => 'nullable|string|max:30',
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'status_kepegawaian' => ['required', Rule::in(['PNS', 'PPPK', 'GTT', 'GTY', 'Honor'])],
            'sudah_sertifikasi' => 'required|boolean',
            'bidang_studi' => 'nullable|string|max:100',
        ]);

        $sekolah = SatuanPendidikan::findOrFail($validated['satuan_pendidikan_id']);

        if ($user && $user->role === 'operator' && $user->kecamatan_id && $sekolah->kecamatan_id != $user->kecamatan_id) {
            abort(403, 'Akses ditolak: Anda hanya dapat menambah guru untuk sekolah di wilayah kecamatan Anda.');
        }

        $guru = GuruPendidik::create([
            'satuan_pendidikan_id' => $sekolah->id,
            'kecamatan_id' => $sekolah->kecamatan_id,
            'nama' => $validated['nama'],
            'nip' => $validated['nip'] ?? null,
            'nuptk' => $validated['nuptk'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'status_kepegawaian' => $validated['status_kepegawaian'],
            'sudah_sertifikasi' => (bool) $validated['sudah_sertifikasi'],
            'bidang_studi' => $validated['bidang_studi'] ?? 'Guru Kelas/Mapel',
        ]);

        ActivityLogger::log('CREATE', 'guru', "Mendaftarkan tenaga pendidik {$guru->nama} ({$sekolah->nama_sekolah})");

        return back()->with('success', "Tenaga pendidik {$guru->nama} ({$sekolah->nama_sekolah}) berhasil didaftarkan.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $guru = GuruPendidik::findOrFail($id);

        if ($user && $user->role === 'operator' && $user->kecamatan_id && $guru->kecamatan_id != $user->kecamatan_id) {
            abort(403, 'Akses ditolak: Anda tidak memiliki akses mengubah data guru di luar wilayah Anda.');
        }

        $validated = $request->validate([
            'satuan_pendidikan_id' => 'required|exists:satuan_pendidikan,id',
            'nama' => 'required|string|max:150',
            'nip' => 'nullable|string|max:30',
            'nuptk' => 'nullable|string|max:30',
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'status_kepegawaian' => ['required', Rule::in(['PNS', 'PPPK', 'GTT', 'GTY', 'Honor'])],
            'sudah_sertifikasi' => 'required|boolean',
            'bidang_studi' => 'nullable|string|max:100',
        ]);

        $sekolah = SatuanPendidikan::findOrFail($validated['satuan_pendidikan_id']);

        if ($user && $user->role === 'operator' && $user->kecamatan_id && $sekolah->kecamatan_id != $user->kecamatan_id) {
            abort(403, 'Akses ditolak: Sekolah tujuan berada di luar wilayah kecamatan Anda.');
        }

        $guru->update([
            'satuan_pendidikan_id' => $sekolah->id,
            'kecamatan_id' => $sekolah->kecamatan_id,
            'nama' => $validated['nama'],
            'nip' => $validated['nip'] ?? null,
            'nuptk' => $validated['nuptk'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'status_kepegawaian' => $validated['status_kepegawaian'],
            'sudah_sertifikasi' => (bool) $validated['sudah_sertifikasi'],
            'bidang_studi' => $validated['bidang_studi'] ?? 'Guru Kelas/Mapel',
        ]);

        ActivityLogger::log('UPDATE', 'guru', "Memperbarui data tenaga pendidik {$guru->nama} ({$sekolah->nama_sekolah})");

        return back()->with('success', "Data tenaga pendidik {$guru->nama} berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = auth()->user();
        $guru = GuruPendidik::findOrFail($id);

        if ($user && $user->role === 'operator' && $user->kecamatan_id && $guru->kecamatan_id != $user->kecamatan_id) {
            abort(403, 'Akses ditolak: Anda tidak dapat menghapus guru di luar wilayah korwilcam Anda.');
        }

        $nama = $guru->nama;
        $guru->delete();

        ActivityLogger::log('DELETE', 'guru', "Menghapus tenaga pendidik {$nama}");

        return back()->with('success', "Data tenaga pendidik {$nama} berhasil dihapus.");
    }

    public function import(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $request->validate([
            'file_guru' => 'required|file|max:20480',
        ]);

        $file = $request->file('file_guru');
        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return back()->withErrors(['file_guru' => 'Gagal membaca file CSV.']);
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

            return back()->withErrors(['file_guru' => 'Header CSV tidak valid atau file kosong.']);
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
            if (empty($row) || count($row) < 2) {
                continue;
            }

            $nama = trim($row[$headerMap['nama'] ?? $headerMap['namaguru'] ?? 0] ?? '');
            if (empty($nama)) {
                continue;
            }

            // Find school by npsn or name
            $npsn = trim($row[$headerMap['npsn'] ?? 1] ?? '');
            $namaSekolah = trim($row[$headerMap['namasekolah'] ?? $headerMap['sekolah'] ?? 2] ?? '');

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
                $sekolah = $allSekolah->first();
            }

            // Scoping check for operator
            if ($user && $user->role === 'operator' && $user->kecamatan_id) {
                if ($sekolah->kecamatan_id != $user->kecamatan_id) {
                    continue; // Skip out of scope school
                }
            }

            $nip = isset($headerMap['nip']) ? trim($row[$headerMap['nip']] ?? '') : null;
            $nuptk = isset($headerMap['nuptk']) ? trim($row[$headerMap['nuptk']] ?? '') : null;

            $jkRaw = strtoupper(trim($row[$headerMap['jeniskelamin'] ?? $headerMap['jk'] ?? 3] ?? 'L'));
            $jk = in_array($jkRaw, ['L', 'P']) ? $jkRaw : (str_starts_with($jkRaw, 'P') ? 'P' : 'L');

            $statusRaw = strtoupper(trim($row[$headerMap['statuskepegawaian'] ?? $headerMap['status'] ?? 4] ?? 'PNS'));
            $status = in_array($statusRaw, ['PNS', 'PPPK', 'GTT', 'GTY', 'Honor']) ? $statusRaw : 'PNS';

            $sertifRaw = strtolower(trim($row[$headerMap['sudahsertifikasi'] ?? $headerMap['sertifikasi'] ?? 5] ?? ''));
            $isSertif = in_array($sertifRaw, ['1', 'ya', 'true', 'sudah', 'y', 'sertifikasi']);

            $mapel = isset($headerMap['bidangstudi']) || isset($headerMap['mapel'])
                ? trim($row[$headerMap['bidangstudi'] ?? $headerMap['mapel']] ?? 'Guru Kelas/Mapel')
                : 'Guru Kelas/Mapel';

            // Insert or update based on NUPTK/NIP or name+school
            $matchKey = [];
            if (! empty($nuptk)) {
                $matchKey = ['nuptk' => $nuptk];
            } elseif (! empty($nip)) {
                $matchKey = ['nip' => $nip];
            } else {
                $matchKey = ['nama' => $nama, 'satuan_pendidikan_id' => $sekolah->id];
            }

            GuruPendidik::updateOrCreate(
                $matchKey,
                [
                    'satuan_pendidikan_id' => $sekolah->id,
                    'kecamatan_id' => $sekolah->kecamatan_id,
                    'nama' => $nama,
                    'nip' => $nip ?: null,
                    'nuptk' => $nuptk ?: null,
                    'jenis_kelamin' => $jk,
                    'status_kepegawaian' => $status,
                    'sudah_sertifikasi' => $isSertif,
                    'bidang_studi' => $mapel ?: 'Guru Kelas/Mapel',
                ]
            );

            $count++;
        }

        fclose($handle);

        if ($count === 0) {
            return back()->with('info', 'File berhasil diproses, namun tidak ada baris data guru yang valid.');
        }

        ActivityLogger::log('IMPORT', 'guru', "Mengimpor {$count} data tenaga pendidik via CSV");

        return back()->with('success', "Berhasil mengimpor {$count} data tenaga pendidik.");
    }
}
