<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WilayahKecamatan extends Model
{
    use HasFactory;

    protected $table = 'wilayah_kecamatan';

    protected $fillable = [
        'kode',
        'nama',
        'ibukota',
        'luas_wilayah',
        'jumlah_desa',
        'latitude',
        'longitude',
        'geojson',
    ];

    public function pendudukUsiaSekolah(): HasMany
    {
        return $this->hasMany(PendudukUsiaSekolah::class, 'kecamatan_id');
    }

    public function satuanPendidikan(): HasMany
    {
        return $this->hasMany(SatuanPendidikan::class, 'kecamatan_id');
    }

    public function partisipasiSiswa(): HasMany
    {
        return $this->hasMany(PartisipasiSiswa::class, 'kecamatan_id');
    }

    public function guruPendidik(): HasMany
    {
        return $this->hasMany(GuruPendidik::class, 'kecamatan_id');
    }

    public function prestasiSiswa(): HasMany
    {
        return $this->hasMany(PrestasiSiswa::class, 'kecamatan_id');
    }
}
