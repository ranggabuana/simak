<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DapodikSyncLog extends Model
{
    use HasFactory;

    protected $table = 'dapodik_sync_logs';

    protected $fillable = [
        'target',
        'status',
        'sekolah_synced',
        'siswa_synced',
        'guru_synced',
        'total_records',
        'semester_id',
        'purge_dummy',
        'message',
        'user_id',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'purge_dummy' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
