<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuruPendidik extends Model
{
    use HasFactory;

    protected $table = 'guru_pendidik';

    protected $fillable = [
        'satuan_pendidikan_id',
        'kecamatan_id',
        'nama',
        'nip',
        'nuptk',
        'jenis_kelamin',
        'status_kepegawaian',
        'sudah_sertifikasi',
        'bidang_studi',
    ];

    protected function casts(): array
    {
        return [
            'sudah_sertifikasi' => 'boolean',
        ];
    }

    public function satuanPendidikan(): BelongsTo
    {
        return $this->belongsTo(SatuanPendidikan::class, 'satuan_pendidikan_id');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kecamatan_id');
    }
}
