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
        $ip = request()->ip();

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistem/Tamu',
            'user_role' => $user?->role ?? 'guest',
            'action' => strtoupper($action),
            'module' => strtolower($module),
            'description' => $description,
            'ip_address' => $ip,
            'mac_address' => self::resolveMacAddress($ip),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
        ]);
    }

    /**
     * Dapatkan MAC address dari header kustom, cache ARP server, atau getmac lokal jika berjalan lokal.
     */
    public static function resolveMacAddress(?string $ip = null): ?string
    {
        // 1. Cek header kustom jika dikirim oleh reverse proxy / agen internal / tunnel
        $customHeader = request()->header('X-Mac-Address') ?? request()->header('Client-Mac');
        if ($customHeader && preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', trim($customHeader))) {
            return strtoupper(str_replace('-', ':', trim($customHeader)));
        }

        $ip = $ip ?: request()->ip();

        // 2. Jika request berasal dari localhost / loopback server
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
            static $localMac = null;
            if ($localMac !== null) {
                return $localMac;
            }

            try {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $output = @shell_exec('getmac');
                    if ($output && preg_match('/([0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2})/', $output, $matches)) {
                        $localMac = strtoupper(str_replace('-', ':', $matches[1]));

                        return $localMac;
                    }
                }
            } catch (\Throwable) {
                // Ignore fallback
            }
        }

        // 3. Jika request berasal dari subnet LAN lokal, periksa ARP table sistem
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            try {
                $escapedIp = escapeshellarg($ip);
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $arp = @shell_exec("arp -a {$escapedIp}");
                    if ($arp && preg_match('/([0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2}[-:][0-9A-Fa-f]{2})/', $arp, $matches)) {
                        return strtoupper(str_replace('-', ':', $matches[1]));
                    }
                }
            } catch (\Throwable) {
                // Ignore fallback
            }
        }

        return null;
    }
}
