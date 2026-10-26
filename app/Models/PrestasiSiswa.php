<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrestasiSiswa extends Model
{
    use HasFactory;

    protected $table = 'prestasi_siswa';

    protected $fillable = [
        'satuan_pendidikan_id',
        'kecamatan_id',
        'nama_siswa',
        'nisn',
        'jenis_kelamin',
        'nama_kejuaraan',
        'bidang',
        'tingkat',
        'peringkat',
        'tahun',
        'penyelenggara',
        'keterangan',
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
