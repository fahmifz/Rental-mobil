<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservasi extends Model
{
    protected $fillable = [
        'user_id',
        'mobil_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi_sewa',
        'tarif_harian',
        'total_biaya',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'durasi_sewa' => 'integer',
            'tarif_harian' => 'decimal:2',
            'total_biaya' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mobil(): BelongsTo
    {
        return $this->belongsTo(Mobil::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'disetujui'
            && !$this->tanggal_selesai->isAfter(today());
    }
}
