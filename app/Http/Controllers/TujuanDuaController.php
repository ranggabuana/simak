<?php

namespace App\Http\Controllers;

use App\Models\PrestasiSiswa;
use App\Models\SatuanPendidikan;
use App\Models\WilayahKecamatan;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TujuanDuaController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $tahun = $this->resolveTahun($request);
        $bidang = $request->input('bidang');
        $tingkat = $request->input('tingkat');
        $jenisKelamin = $request->input('jenis_kelamin');
        $search = $request->input('search');

        $query = PrestasiSiswa::with(['satuanPendidikan.kecamatan', 'kecamatan']);

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        if ($bidang) {
            $query->where('bidang', $bidang);
        }

        if ($tingkat) {
            $query->where('tingkat', $tingkat);
        }

        if ($jenisKelamin) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nama_kejuaraan', 'like', "%{$search}%")
                    ->orWhere('bidang', 'like', "%{$search}%")
                    ->orWhere('tingkat', 'like', "%{$search}%")
                    ->orWhere('peringkat', 'like', "%{$search}%")
                    ->orWhereHas('satuanPendidikan', function ($sq) use ($search) {
                        $sq->where('nama_sekolah', 'like', "%{$search}%");
                    });
            });
        }

        // Server-Side Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'nama_siswa') {
            $query->orderBy('nama_siswa', $sortDir);
        } elseif ($sortBy === 'nisn') {
            $query->orderBy('nisn', $sortDir);
        } elseif ($sortBy === 'nama_kejuaraan') {
            $query->orderBy('nama_kejuaraan', $sortDir);
        } elseif ($sortBy === 'bidang') {
            $query->orderBy('bidang', $sortDir);
        } elseif ($sortBy === 'tingkat') {
            $query->orderBy('tingkat', $sortDir);
        } elseif ($sortBy === 'peringkat') {
            $query->orderBy('peringkat', $sortDir);
        } elseif ($sortBy === 'tahun') {
            $query->orderBy('tahun', $sortDir);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10;
        }

        $prestasiList = $query->paginate($perPage)->withQueryString();

        // Server-Side AJAX JSON Response
        if ($request->ajax() || $request->wantsJson() || $request->has('serverside')) {
            return response()->json([
                'html' => view('tujuan2._rows', ['prestasiList' => $prestasiList])->render(),
                'total' => $prestasiList->total(),
                'filtered' => $prestasiList->total(),
                'current_page' => $prestasiList->currentPage(),
                'per_page' => $prestasiList->perPage(),
                'last_page' => $prestasiList->lastPage(),
                'from' => $prestasiList->firstItem() ?? 0,
                'to' => $prestasiList->lastItem() ?? 0,
            ]);
        }

        $rekapBidang = PrestasiSiswa::where('tahun', $tahun)
            ->selectRaw('bidang, count(*) as total')
            ->groupBy('bidang')
            ->pluck('total', 'bidang');

        $rekapTingkat = PrestasiSiswa::where('tahun', $tahun)
            ->selectRaw('tingkat, count(*) as total')
            ->groupBy('tingkat')
            ->pluck('total', 'tingkat');

        $rekapGender = PrestasiSiswa::where('tahun', $tahun)
            ->selectRaw('jenis_kelamin, count(*) as total')
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        return view('tujuan2.index', [
            'tahun' => $tahun,
            'bidang' => $bidang,
            'tingkat' => $tingkat,
            'jenis_kelamin' => $jenisKelamin,
            'search' => $search,
            'prestasiList' => $prestasiList,
            'rekapBidang' => $rekapBidang,
            'rekapTingkat' => $rekapTingkat,
            'rekapGender' => $rekapGender,
            'sekolahList' => SatuanPendidikan::with('kecamatan')->orderBy('nama_sekolah')->get(),
            'kecamatanList' => WilayahKecamatan::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_siswa' => 'required|string|max:150',
            'nisn' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'nama_kejuaraan' => 'required|string|max:200',
            'bidang' => 'required|in:Sains & Akademik,Olahraga,Seni & Budaya,Teknologi & Robotik,Keagamaan,Lainnya',
            'tingkat' => 'required|in:Kecamatan,Kabupaten,Provinsi,Nasional,Internasional',
            'peringkat' => 'required|in:Juara 1,Juara 2,Juara 3,Harapan 1,Harapan 2,Finalis',
            'tahun' => 'required|integer|min:2020|max:2035',
            'satuan_pendidikan_id' => 'required|exists:satuan_pendidikan,id',
            'penyelenggara' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string',
        ]);

        $validated['jenis_kelamin'] = $validated['jenis_kelamin'] ?? 'L';

        $sekolah = SatuanPendidikan::findOrFail($validated['satuan_pendidikan_id']);
        $validated['kecamatan_id'] = $sekolah->kecamatan_id;

        $prestasi = PrestasiSiswa::create($validated);

        ActivityLogger::log('CREATE', 'tujuan2', "Mencatat prestasi siswa {$prestasi->nama_siswa} ({$prestasi->nama_kejuaraan} - {$prestasi->peringkat})");

        return back()->with('success', 'Data prestasi siswa berhasil ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_siswa' => 'required|string|max:150',
            'nisn' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'nama_kejuaraan' => 'required|string|max:200',
            'bidang' => 'required|in:Sains & Akademik,Olahraga,Seni & Budaya,Teknologi & Robotik,Keagamaan,Lainnya',
            'tingkat' => 'required|in:Kecamatan,Kabupaten,Provinsi,Nasional,Internasional',
            'peringkat' => 'required|in:Juara 1,Juara 2,Juara 3,Harapan 1,Harapan 2,Finalis',
            'tahun' => 'required|integer|min:2020|max:2035',
            'satuan_pendidikan_id' => 'required|exists:satuan_pendidikan,id',
            'penyelenggara' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string',
        ]);

        $prestasi = PrestasiSiswa::findOrFail($id);
        $validated['jenis_kelamin'] = $validated['jenis_kelamin'] ?? $prestasi->jenis_kelamin ?? 'L';
        $sekolah = SatuanPendidikan::findOrFail($validated['satuan_pendidikan_id']);
        $validated['kecamatan_id'] = $sekolah->kecamatan_id;

        $prestasi->update($validated);

        ActivityLogger::log('UPDATE', 'tujuan2', "Memperbarui data prestasi siswa {$prestasi->nama_siswa} ({$prestasi->nama_kejuaraan} - {$prestasi->peringkat})");

        return back()->with('success', 'Data prestasi siswa berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $prestasi = PrestasiSiswa::findOrFail($id);
        $nama = $prestasi->nama_siswa;
        $prestasi->delete();

        ActivityLogger::log('DELETE', 'tujuan2', "Menghapus data prestasi siswa {$nama}");

        return back()->with('success', 'Data prestasi berhasil dihapus.');
    }
}
