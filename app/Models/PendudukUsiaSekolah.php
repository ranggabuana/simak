<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendudukUsiaSekolah extends Model
{
    use HasFactory;

    protected $table = 'penduduk_usia_sekolah';

    protected $fillable = [
        'kecamatan_id',
        'tahun',
        'usia_0_4_l',
        'usia_0_4_p',
        'usia_0_4_total',
        'usia_5_6_l',
        'usia_5_6_p',
        'usia_5_6_total',
        'usia_7_12_l',
        'usia_7_12_p',
        'usia_7_12_total',
        'usia_13_15_l',
        'usia_13_15_p',
        'usia_13_15_total',
        'usia_16_18_l',
        'usia_16_18_p',
        'usia_16_18_total',
        'usia_16_30_l',
        'usia_16_30_p',
        'usia_16_30_total',
        'usia_18_plus_total',
        'total_penduduk',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kecamatan_id');
    }
}
