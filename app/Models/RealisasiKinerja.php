<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiKinerja extends Model
{
    use HasFactory;

    protected $table = 'realisasi_kinerja';

    protected $fillable = [
        'tahun',
        'capaian_ikk_renstra',
        'capaian_akip_n',
        'capaian_akip_prev',
        'capaian_rls_n',
        'capaian_rls_prev',
        'capaian_hls_n',
        'capaian_hls_prev',
        'evaluasi_status',
        'catatan_evaluasi',
        'rekomendasi',
    ];

    protected function casts(): array
    {
        return [
            'capaian_ikk_renstra' => 'float',
            'capaian_akip_n' => 'float',
            'capaian_akip_prev' => 'float',
            'capaian_rls_n' => 'float',
            'capaian_rls_prev' => 'float',
            'capaian_hls_n' => 'float',
            'capaian_hls_prev' => 'float',
        ];
    }
}
