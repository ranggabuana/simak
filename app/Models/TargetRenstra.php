<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetRenstra extends Model
{
    use HasFactory;

    protected $table = 'target_renstra';

    protected $fillable = [
        'tahun',
        'target_apk_paud',
        'target_apk_sd',
        'target_apk_smp',
        'target_apk_sma',
        'target_apm_paud',
        'target_apm_sd',
        'target_apm_smp',
        'target_apm_sma',
        'target_aps_paud',
        'target_aps_sd',
        'target_aps_smp',
        'target_aps_sma',
        'target_guru_sertifikasi',
        'target_paud_akreditasi_b',
        'target_akip',
        'target_rls',
        'target_hls',
        'target_ikk_renstra',
    ];

    protected function casts(): array
    {
        return [
            'target_apk_paud' => 'float',
            'target_apk_sd' => 'float',
            'target_apk_smp' => 'float',
            'target_apk_sma' => 'float',
            'target_apm_paud' => 'float',
            'target_apm_sd' => 'float',
            'target_apm_smp' => 'float',
            'target_apm_sma' => 'float',
            'target_aps_paud' => 'float',
            'target_aps_sd' => 'float',
            'target_aps_smp' => 'float',
            'target_aps_sma' => 'float',
            'target_guru_sertifikasi' => 'float',
            'target_paud_akreditasi_b' => 'float',
            'target_akip' => 'float',
            'target_rls' => 'float',
            'target_hls' => 'float',
            'target_ikk_renstra' => 'float',
        ];
    }
}
