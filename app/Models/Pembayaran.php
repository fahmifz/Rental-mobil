<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $fillable = [
        'reservasi_id',
        'nominal_pembayaran',
        'tanggal_pembayaran',
        'metode_pembayaran',
        'status',
        'catatan',
        'dicatat_oleh_id',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'nominal_pembayaran' => 'decimal:2',
            'tanggal_pembayaran' => 'date',
        ];
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh_id');
    }
}
