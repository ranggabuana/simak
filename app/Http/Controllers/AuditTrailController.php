<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
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

        // Server-Side AJAX JSON Response
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

        return view('audit.index', [
            'logs' => $logs,
            'stats' => $stats,
            'filterAction' => $action,
            'filterModule' => $module,
        ]);
    }
}
