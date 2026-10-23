<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $menuKey): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessMenu($menuKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak: Peran Anda tidak memiliki hak akses ke modul ini.',
                ], 403);
            }

            abort(403, 'Akses ditolak: Peran Anda ('.($user?->role_label ?? 'Pengguna').') tidak memiliki izin untuk mengakses menu ini.');
        }

        return $next($request);
    }
}
