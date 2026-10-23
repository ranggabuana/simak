<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartisipasiSiswa extends Model
{
    use HasFactory;

    protected $table = 'partisipasi_siswa';

    protected $fillable = [
        'satuan_pendidikan_id',
        'kecamatan_id',
        'tahun_ajaran',
        'tahun',
        'jenjang',
        'total_siswa_semua_usia',
        'siswa_usia_sesuai',
        'siswa_usia_5_6',
        'siswa_usia_7_12',
        'siswa_usia_13_15',
        'siswa_usia_16_18',
        'siswa_usia_lainnya',
        'jumlah_rombel',
    ];

    public function satuanPendidikan(): BelongsTo
    {
        return $this->belongsTo(SatuanPendidikan::class, 'satuan_pendidikan_id');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kecamatan_id');
    }
}
