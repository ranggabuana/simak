<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $tab = $request->input('tab', 'audit'); // 'audit' atau 'error'
        $action = $request->input('action');
        $module = $request->input('module');
        $search = $request->input('search');

        $query = ActivityLog::query();

        if ($action) {
            $query->where('action', $action);
        }
        if ($module) {
            $query->where('module', $module);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('user_role', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Server-Side Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'user_name') {
            $query->orderBy('user_name', $sortDir);
        } elseif ($sortBy === 'action') {
            $query->orderBy('action', $sortDir);
        } elseif ($sortBy === 'module') {
            $query->orderBy('module', $sortDir);
        } elseif ($sortBy === 'created_at') {
            $query->orderBy('created_at', $sortDir);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 10;
        }

        $logs = $query->paginate($perPage)->withQueryString();

        // Server-Side AJAX JSON Response untuk tabel audit trail
        if ($request->ajax() || $request->wantsJson() || $request->has('serverside')) {
            return response()->json([
                'html' => view('audit._rows', ['logs' => $logs])->render(),
                'total' => $logs->total(),
                'filtered' => $logs->total(),
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'last_page' => $logs->lastPage(),
                'from' => $logs->firstItem() ?? 0,
                'to' => $logs->lastItem() ?? 0,
            ]);
        }

        $stats = [
            'total' => ActivityLog::count(),
            'creates' => ActivityLog::where('action', 'CREATE')->count(),
            'updates' => ActivityLog::where('action', 'UPDATE')->count(),
            'deletes' => ActivityLog::where('action', 'DELETE')->count(),
            'imports' => ActivityLog::where('action', 'IMPORT')->count(),
        ];

        // Parse log error dari storage/logs
        $errorLogs = $this->getErrorLogs($request);

        return view('audit.index', [
            'tab' => $tab,
            'logs' => $logs,
            'stats' => $stats,
            'filterAction' => $action,
            'filterModule' => $module,
            'errorLogs' => $errorLogs,
        ]);
    }

    /**
     * Membaca dan mem-parsing berkas laravel.log menjadi koleksi error log terstruktur.
     */
    protected function getErrorLogs(Request $request): LengthAwarePaginator
    {
        $logPath = storage_path('logs/laravel.log');
        $entries = collect();

        if (File::exists($logPath)) {
            $content = File::get($logPath);
            // Pola log: [YYYY-MM-DD HH:MM:SS] environment.LEVEL: message
            $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([a-zA-Z0-9_\-]+)\.([A-Z]+):\s+(.*?)(?=\n\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|\Z)/ms';

            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                foreach (array_reverse($matches) as $match) {
                    $rawBlock = trim($match[0]);
                    $rawTime = $match[1];
                    $env = $match[2];
                    $level = strtoupper($match[3]);
                    $body = trim($match[4]);
                    $id = md5($rawTime.$env.$level.substr($body, 0, 80));

                    // Ambil baris pertama sebagai pesan singkat
                    $lines = explode("\n", $body);
                    $headline = trim($lines[0] ?? 'Unknown error');
                    $stackTrace = count($lines) > 1 ? trim(implode("\n", array_slice($lines, 1))) : null;

                    // Konversi waktu ke Asia/Jakarta (WIB)
                    try {
                        $timeWib = Carbon::parse($rawTime)->timezone('Asia/Jakarta');
                    } catch (\Throwable) {
                        $timeWib = Carbon::now('Asia/Jakarta');
                    }

                    $entries->push([
                        'id' => $id,
                        'raw_block' => $rawBlock,
                        'time' => $timeWib,
                        'env' => $env,
                        'level' => $level,
                        'headline' => $headline,
                        'body' => $body,
                        'stack' => $stackTrace,
                    ]);
                }
            }
        }

        // Filter search jika ada di tab error
        $errorSearch = $request->input('error_search');
        if ($errorSearch) {
            $entries = $entries->filter(function ($item) use ($errorSearch) {
                return str_contains(strtolower($item['headline']), strtolower($errorSearch))
                    || str_contains(strtolower($item['level']), strtolower($errorSearch))
                    || str_contains(strtolower($item['env']), strtolower($errorSearch));
            });
        }

        $errorLevel = $request->input('error_level');
        if ($errorLevel) {
            $entries = $entries->filter(fn ($item) => $item['level'] === strtoupper($errorLevel));
        }

        // Manual pagination untuk error log
        $page = (int) $request->input('error_page', 1);
        $perPage = 15;
        $items = $entries->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $entries->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'error_page']
        );
    }

    /**
     * Menghapus log error baik satu per satu (single), beberapa (batch), atau sekaligus (clear all).
     */
    public function destroyErrors(Request $request): RedirectResponse
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath)) {
            return redirect()->route('audit.index', ['tab' => 'error'])->with('info', 'Berkas log tidak ditemukan atau sudah kosong.');
        }

        // 1. Opsi Bersihkan Semua (Clear All)
        if ($request->boolean('clear_all')) {
            File::put($logPath, '');

            ActivityLogger::log('DELETE', 'audit', 'Membersihkan seluruh log error aplikasi (laravel.log)');

            return redirect()->route('audit.index', ['tab' => 'error'])->with('success', 'Seluruh catatan log error aplikasi berhasil dibersihkan.');
        }

        // 2. Opsi Single / Batch Delete berdasarkan list ID
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = array_filter((array) $ids);

        if (empty($ids)) {
            return redirect()->route('audit.index', ['tab' => 'error'])->withErrors(['error_delete' => 'Tidak ada item log error yang dipilih untuk dihapus.']);
        }

        $content = File::get($logPath);
        $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([a-zA-Z0-9_\-]+)\.([A-Z]+):\s+(.*?)(?=\n\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|\Z)/ms';

        $deletedCount = 0;
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            $preservedBlocks = [];

            foreach ($matches as $match) {
                $rawBlock = trim($match[0]);
                $rawTime = $match[1];
                $env = $match[2];
                $level = strtoupper($match[3]);
                $body = trim($match[4]);
                $id = md5($rawTime.$env.$level.substr($body, 0, 80));

                if (in_array($id, $ids, true)) {
                    $deletedCount++;
                } else {
                    $preservedBlocks[] = $rawBlock;
                }
            }

            $newContent = empty($preservedBlocks) ? '' : implode("\n\n", $preservedBlocks)."\n";
            File::put($logPath, $newContent);
        }

        if ($deletedCount > 0) {
            ActivityLogger::log('DELETE', 'audit', "Menghapus {$deletedCount} catatan log error aplikasi");

            return redirect()->route('audit.index', ['tab' => 'error'])->with('success', "Berhasil menghapus {$deletedCount} catatan log error.");
        }

        return redirect()->route('audit.index', ['tab' => 'error'])->with('info', 'Catatan log yang dipilih sudah tidak ada di berkas log.');
    }
}
