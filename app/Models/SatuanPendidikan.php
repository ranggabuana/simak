<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SatuanPendidikan extends Model
{
    use HasFactory;

    protected $table = 'satuan_pendidikan';

    protected $fillable = [
        'sekolah_id',
        'npsn',
        'nama_sekolah',
        'jenjang',
        'status',
        'akreditasi',
        'kecamatan_id',
        'alamat',
        'kepala_sekolah',
        'telepon',
        'latitude',
        'longitude',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kecamatan_id');
    }

    public function partisipasiSiswa(): HasMany
    {
        return $this->hasMany(PartisipasiSiswa::class, 'satuan_pendidikan_id');
    }

    public function guruPendidik(): HasMany
    {
        return $this->hasMany(GuruPendidik::class, 'satuan_pendidikan_id');
    }

    public function prestasiSiswa(): HasMany
    {
        return $this->hasMany(PrestasiSiswa::class, 'satuan_pendidikan_id');
    }
}
