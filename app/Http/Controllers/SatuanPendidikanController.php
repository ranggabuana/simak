<?php

namespace App\Http\Controllers;

use App\Models\SatuanPendidikan;
use App\Models\WilayahKecamatan;
use App\Services\ActivityLogger;
use App\Services\DapodikSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SatuanPendidikanController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $kecamatanId = $request->input('kecamatan_id');
        $isOperatorScoped = false;

        // Scoping per wilayah untuk Operator Korwilcam
        if ($user && $user->role === 'operator' && $user->kecamatan_id) {
            $kecamatanId = $user->kecamatan_id;
            $isOperatorScoped = true;
        }

        $jenjang = $request->input('jenjang');
        $status = $request->input('status');
        $akreditasi = $request->input('akreditasi');
        $search = $request->input('search');

        $query = SatuanPendidikan::with('kecamatan');

        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }
        if ($jenjang) {
            $query->where('jenjang', $jenjang);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($akreditasi) {
            $query->where('akreditasi', $akreditasi);
        }

        // Server-Side Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_sekolah', 'like', "%{$search}%")
                    ->orWhere('npsn', 'like', "%{$search}%")
                    ->orWhere('kepala_sekolah', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%")
                    ->orWhereHas('kecamatan', function ($kq) use ($search) {
                        $kq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        // Server-Side Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'npsn') {
            $query->orderBy('npsn', $sortDir);
        } elseif ($sortBy === 'nama_sekolah') {
            $query->orderBy('nama_sekolah', $sortDir);
        } elseif ($sortBy === 'jenjang') {
            $query->orderBy('jenjang', $sortDir);
        } elseif ($sortBy === 'status') {
            $query->orderBy('status', $sortDir);
        } elseif ($sortBy === 'akreditasi') {
            $query->orderBy('akreditasi', $sortDir);
        } else {
            $query->orderBy('nama_sekolah', 'asc');
        }

        // Per Page pagination
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10;
        }

        $sekolahList = $query->paginate($perPage)->withQueryString();

        // Server-Side AJAX JSON Response
        if ($request->ajax() || $request->wantsJson() || $request->has('serverside')) {
            return response()->json([
                'html' => view('sekolah._rows', ['sekolahList' => $sekolahList])->render(),
                'total' => $sekolahList->total(),
                'filtered' => $sekolahList->total(),
                'current_page' => $sekolahList->currentPage(),
                'per_page' => $sekolahList->perPage(),
                'last_page' => $sekolahList->lastPage(),
                'from' => $sekolahList->firstItem() ?? 0,
                'to' => $sekolahList->lastItem() ?? 0,
            ]);
        }

        // Stats summary using fast SQL aggregation
        $statQuery = SatuanPendidikan::query();
        if ($isOperatorScoped) {
            $statQuery->where('kecamatan_id', $kecamatanId);
        }
        $statRow = (clone $statQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Negeri' THEN 1 ELSE 0 END) as negeri,
            SUM(CASE WHEN status = 'Swasta' THEN 1 ELSE 0 END) as swasta,
            SUM(CASE WHEN akreditasi = 'A' THEN 1 ELSE 0 END) as akr_a,
            SUM(CASE WHEN akreditasi = 'B' THEN 1 ELSE 0 END) as akr_b,
            SUM(CASE WHEN akreditasi IN ('A', 'B') THEN 1 ELSE 0 END) as akr_min_b
        ")->first();

        $stats = [
            'total' => (int) ($statRow->total ?? 0),
            'negeri' => (int) ($statRow->negeri ?? 0),
            'swasta' => (int) ($statRow->swasta ?? 0),
            'akr_a' => (int) ($statRow->akr_a ?? 0),
            'akr_b' => (int) ($statRow->akr_b ?? 0),
            'akr_min_b' => (int) ($statRow->akr_min_b ?? 0),
        ];

        return view('sekolah.index', [
            'sekolahList' => $sekolahList,
            'stats' => $stats,
            'kecamatanList' => WilayahKecamatan::orderBy('nama')->get(),
            'filterKecamatan' => $kecamatanId,
            'filterJenjang' => $jenjang,
            'filterStatus' => $status,
            'filterAkreditasi' => $akreditasi,
            'isOperatorScoped' => $isOperatorScoped,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'npsn' => 'required|string|max:10|unique:satuan_pendidikan,npsn',
            'nama_sekolah' => 'required|string|max:150',
            'jenjang' => ['required', Rule::in(['PAUD', 'TK', 'SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PKBM'])],
            'status' => ['required', Rule::in(['Negeri', 'Swasta'])],
            'akreditasi' => ['required', Rule::in(['A', 'B', 'C', 'Belum Terakreditasi'])],
            'kecamatan_id' => 'required|exists:wilayah_kecamatan,id',
            'alamat' => 'nullable|string|max:255',
            'kepala_sekolah' => 'nullable|string|max:150',
            'telepon' => 'nullable|string|max:30',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($user && $user->role === 'operator' && $user->kecamatan_id) {
            $validated['kecamatan_id'] = $user->kecamatan_id;
        }

        $sekolah = SatuanPendidikan::create($validated);

        ActivityLogger::log('CREATE', 'sekolah', "Menambahkan satuan pendidikan {$sekolah->nama_sekolah} (NPSN: {$sekolah->npsn})");

        return back()->with('success', "Satuan Pendidikan {$sekolah->nama_sekolah} (NPSN: {$sekolah->npsn}) berhasil ditambahkan.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $sekolah = SatuanPendidikan::findOrFail($id);

        $validated = $request->validate([
            'npsn' => ['required', 'string', 'max:10', Rule::unique('satuan_pendidikan', 'npsn')->ignore($sekolah->id)],
            'nama_sekolah' => 'required|string|max:150',
            'jenjang' => ['required', Rule::in(['PAUD', 'TK', 'SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PKBM'])],
            'status' => ['required', Rule::in(['Negeri', 'Swasta'])],
            'akreditasi' => ['required', Rule::in(['A', 'B', 'C', 'Belum Terakreditasi'])],
            'kecamatan_id' => 'required|exists:wilayah_kecamatan,id',
            'alamat' => 'nullable|string|max:255',
            'kepala_sekolah' => 'nullable|string|max:150',
            'telepon' => 'nullable|string|max:30',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($user && $user->role === 'operator' && $user->kecamatan_id) {
            $validated['kecamatan_id'] = $user->kecamatan_id;
        }

        $sekolah->update($validated);

        ActivityLogger::log('UPDATE', 'sekolah', "Memperbarui data satuan pendidikan {$sekolah->nama_sekolah} (NPSN: {$sekolah->npsn})");

        return back()->with('success', "Data Satuan Pendidikan {$sekolah->nama_sekolah} berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $sekolah = SatuanPendidikan::findOrFail($id);
        $nama = $sekolah->nama_sekolah;
        $sekolah->delete();

        ActivityLogger::log('DELETE', 'sekolah', "Menghapus satuan pendidikan {$nama}");

        return back()->with('success', "Satuan Pendidikan {$nama} berhasil dihapus.");
    }

    public function import(Request $request, DapodikSyncService $syncService): RedirectResponse
    {
        $user = auth()->user();
        $request->validate([
            'file_sekolah' => 'required|file|max:51200',
        ]);

        $file = $request->file('file_sekolah');
        $rows = $syncService->parseDelimitedFile($file->getRealPath());

        if (empty($rows)) {
            return back()->withErrors(['file_sekolah' => 'Header CSV tidak valid atau file kosong.']);
        }

        $scopedKecamatanId = ($user && $user->role === 'operator' && $user->kecamatan_id) ? (int) $user->kecamatan_id : null;
        $count = $syncService->syncSekolahBatch($rows, $scopedKecamatanId);

        if ($count === 0) {
            return back()->with('info', 'File berhasil diproses, namun tidak ada baris data sekolah yang valid. Periksa format kolom CSV.');
        }

        ActivityLogger::log('IMPORT', 'sekolah', "Mengimpor dan memperbarui {$count} data satuan pendidikan dari CSV/Dapodik");

        return back()->with('success', "Berhasil mengimpor dan memperbarui {$count} data Satuan Pendidikan.");
    }
}
