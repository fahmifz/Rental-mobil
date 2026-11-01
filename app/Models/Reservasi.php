<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function pengembalian(): HasOne
    {
        return $this->hasOne(Pengembalian::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class)->latest('tanggal_pembayaran')->latest('id');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class)->latestOfMany('id');
    }

    public function totalPaid(): float
    {
        return round((float) $this->pembayarans()->sum('nominal_pembayaran'), 2);
    }

    public function remainingAmount(): float
    {
        return max(0, round((float) $this->total_biaya - $this->totalPaid(), 2));
    }

    public function paymentStatus(): string
    {
        $paid = $this->totalPaid();
        $total = round((float) $this->total_biaya, 2);

        if ($paid <= 0) {
            return 'belum_dibayar';
        }

        return $paid < $total ? 'dibayar_sebagian' : 'lunas';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'disetujui'
            && !$this->tanggal_selesai->isAfter(today());
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, ['menunggu_konfirmasi', 'disetujui'], true)
            && $this->tanggal_mulai->isAfter(today())
            && ! $this->pembayarans()->exists();
    }

    public function canBeCancelledByAdmin(): bool
    {
        return in_array($this->status, ['menunggu_konfirmasi', 'disetujui'], true);
    }

    public function canBeReturned(): bool
    {
        return $this->status === 'disetujui' && !$this->pengembalian()->exists();
    }

    public function isActiveOn(CarbonInterface $date): bool
    {
        return $this->status === 'disetujui'
            && !$this->tanggal_mulai->isAfter($date)
            && !$this->tanggal_selesai->isBefore($date);
    }
}
