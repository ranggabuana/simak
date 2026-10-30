<?php

namespace App\Http\Controllers;

use App\Models\DapodikSyncLog;
use App\Services\ActivityLogger;
use App\Services\DapodikSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DapodikSyncController extends Controller
{
    public function __construct(
        protected DapodikSyncService $syncService
    ) {}

    /**
     * Validasi token rahasia untuk komunikasi agen PowerShell Kominfo.
     */
    protected function validateAgentToken(Request $request): void
    {
        $token = $request->bearerToken() ?? $request->header('X-Sync-Token');
        $expected = config('services.dapodik.sync_secret', 'SIMAK_BANJARNEGARA_DAPODIK_SECRET_2026');

        if (! $token || ! hash_equals((string) $expected, (string) $token)) {
            abort(401, 'Unauthorized: Token sinkronisasi Dapodik tidak valid.');
        }
    }

    /**
     * Memicu antrean sinkronisasi baru dari tombol UI SIMAK (Mode Reverse Push Agent).
     */
    public function trigger(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target' => 'required|in:semua,sekolah,siswa,guru',
            'semester_id' => 'nullable|string|max:10',
            'purge_dummy' => 'nullable|boolean',
        ]);

        $purgeDummy = $request->boolean('purge_dummy', true);
        if ($purgeDummy) {
            $this->syncService->purgeDummyData($validated['target']);
        }

        $log = DapodikSyncLog::create([
            'target' => $validated['target'],
            'status' => 'pending',
            'semester_id' => $validated['semester_id'] ?? '20261',
            'purge_dummy' => $purgeDummy,
            'message' => 'Menunggu agen PowerShell di Windows Server Kominfo mengambil tugas...',
            'user_id' => auth()->id(),
            'started_at' => now(),
        ]);

        ActivityLogger::log('SYNC', 'dapodik', "Memicu permintaan sinkronisasi Dapodik (Target: {$log->target}, ID Tugas: #{$log->id})");

        return response()->json([
            'status' => 'ok',
            'task_id' => $log->id,
            'message' => $log->message,
        ]);
    }

    /**
     * Polling status sinkronisasi dari browser UI SIMAK.
     */
    public function status(int $id): JsonResponse
    {
        $log = DapodikSyncLog::findOrFail($id);

        return response()->json([
            'id' => $log->id,
            'target' => $log->target,
            'status' => $log->status,
            'sekolah_synced' => $log->sekolah_synced,
            'siswa_synced' => $log->siswa_synced,
            'guru_synced' => $log->guru_synced,
            'total_records' => $log->total_records,
            'message' => $log->message,
            'updated_at' => $log->updated_at?->diffForHumans(),
        ]);
    }

    /**
     * Endpoint API yang dicek secara berkala oleh Agen PowerShell di Windows Server Kominfo.
     */
    public function checkTask(Request $request): JsonResponse
    {
        $this->validateAgentToken($request);

        $task = DapodikSyncLog::where('status', 'pending')->oldest()->first();

        if (! $task) {
            return response()->json(['has_task' => false]);
        }

        $task->update([
            'status' => 'processing',
            'message' => 'Agen Windows Server Kominfo sedang mengeksekusi SQL View Banjarnegara...',
        ]);

        return response()->json([
            'has_task' => true,
            'task_id' => $task->id,
            'target' => $task->target,
            'semester_id' => $task->semester_id ?? '20261',
        ]);
    }

    /**
     * Endpoint API penerima batch data JSON dari Agen PowerShell di Windows Server Kominfo.
     */
    public function receiveData(Request $request): JsonResponse
    {
        $this->validateAgentToken($request);

        $validated = $request->validate([
            'task_id' => 'required|exists:dapodik_sync_logs,id',
            'entity' => 'required|in:sekolah,siswa,guru',
            'data' => 'required|array',
            'is_last_batch' => 'boolean',
        ]);

        $task = DapodikSyncLog::findOrFail($validated['task_id']);
        $rows = $validated['data'];
        $entity = $validated['entity'];

        try {
            $processed = 0;
            if ($entity === 'sekolah') {
                $processed = $this->syncService->syncSekolahBatch($rows);
                $task->increment('sekolah_synced', $processed);
            } elseif ($entity === 'siswa') {
                $processed = $this->syncService->syncSiswaBatch($rows, 2025);
                $task->increment('siswa_synced', $processed);
            } elseif ($entity === 'guru') {
                $processed = $this->syncService->syncGuruBatch($rows);
                $task->increment('guru_synced', $processed);
            }

            $task->increment('total_records', $processed);

            if ($request->boolean('is_last_batch', false)) {
                $task->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'message' => "Sinkronisasi selesai! Sekolah: {$task->sekolah_synced}, Partisipasi Siswa: {$task->siswa_synced}, Guru: {$task->guru_synced}.",
                ]);
            } else {
                $task->update([
                    'message' => "Menerima batch {$entity} ({$task->total_records} baris diproses)...",
                ]);
            }

            return response()->json([
                'status' => 'success',
                'processed' => $processed,
                'total_records' => $task->total_records,
            ]);
        } catch (\Throwable $e) {
            $task->update([
                'status' => 'failed',
                'message' => 'Gagal memproses batch: '.$e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sinkronisasi langsung dari file CSV hasil ekspor v_simak_sekolah_bna, v_simak_siswa_bna, atau v_simak_guru_bna.
     */
    public function importViewFile(Request $request): RedirectResponse
    {
        $request->validate([
            'entity' => 'required|in:sekolah,siswa,guru',
            'file_dapodik' => 'required|file|max:51200',
            'tahun_target' => 'nullable|integer|min:2020|max:2035',
            'purge_dummy' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $scopedKecamatanId = ($user && $user->role === 'operator' && $user->kecamatan_id) ? (int) $user->kecamatan_id : null;
        $entity = $request->input('entity');
        $tahunTarget = (int) $request->input('tahun_target', 2025);
        $purgeDummy = $request->boolean('purge_dummy', false);

        $file = $request->file('file_dapodik');
        $rows = $this->syncService->parseDelimitedFile($file->getRealPath());

        if (empty($rows)) {
            return back()->withErrors(['file_dapodik' => 'File kosong atau header kolom tidak dikenali. Pastikan mengekspor dari View SSMS dengan opsi "Save Results As CSV" (Include column headers).']);
        }

        if ($purgeDummy && ! $scopedKecamatanId) {
            $this->syncService->purgeDummyData($entity);
        }

        if ($entity === 'sekolah') {
            $count = $this->syncService->syncSekolahBatch($rows, $scopedKecamatanId);
            $label = 'Satuan Pendidikan (v_simak_sekolah_bna)';
        } elseif ($entity === 'siswa') {
            $count = $this->syncService->syncSiswaBatch($rows, $tahunTarget, $scopedKecamatanId);
            $label = 'Partisipasi Siswa (v_simak_siswa_bna)';
        } else {
            $count = $this->syncService->syncGuruBatch($rows, $scopedKecamatanId);
            $label = 'Guru & Tendik (v_simak_guru_bna)';
        }

        DapodikSyncLog::create([
            'target' => $entity,
            'status' => 'completed',
            'sekolah_synced' => $entity === 'sekolah' ? $count : 0,
            'siswa_synced' => $entity === 'siswa' ? $count : 0,
            'guru_synced' => $entity === 'guru' ? $count : 0,
            'total_records' => $count,
            'semester_id' => '20261',
            'message' => "Sinkronisasi {$count} baris {$label} berhasil.",
            'user_id' => $user?->id,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        ActivityLogger::log('SYNC', $entity, "Menyinkronkan {$count} baris data Dapodik Banjarnegara ({$label})");

        return back()->with('success', "Sinkronisasi Dapodik berhasil! {$count} baris data {$label} telah diperbarui ke dalam SIMAK.");
    }
}
