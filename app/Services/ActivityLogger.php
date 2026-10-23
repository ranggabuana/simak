<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Catat aktivitas audit trail
     */
    public static function log(string $action, string $module, string $description): void
    {
        $user = Auth::user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistem/Tamu',
            'user_role' => $user?->role ?? 'guest',
            'action' => strtoupper($action),
            'module' => strtolower($module),
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
        ]);
    }
}
