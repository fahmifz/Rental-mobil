<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class Refund extends Model
{
    protected $fillable = [
        'reservasi_id',
        'jenis_refund',
        'nominal_refund',
        'status',
        'alasan',
        'diputuskan_pada',
        'diputuskan_oleh_id',
        'metode_refund',
        'tanggal_refund',
        'referensi_refund',
        'dikonfirmasi_pada',
        'dikonfirmasi_oleh_id',
    ];

    protected function casts(): array
    {
        return [
            'nominal_refund' => 'decimal:2',
            'diputuskan_pada' => 'datetime',
            'tanggal_refund' => 'datetime',
            'dikonfirmasi_pada' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Refund $refund): void {
            $nominalRefund = (float) $refund->nominal_refund;
            $reservasi = $refund->reservasi()->first();

            if ($reservasi === null || $reservasi->status !== 'dibatalkan') {
                throw new InvalidArgumentException('Refund hanya dapat dicatat untuk reservasi yang dibatalkan.');
            }

            if ($nominalRefund < 0) {
                throw new InvalidArgumentException('Nominal refund tidak boleh negatif.');
            }

            if ($refund->jenis_refund === 'tidak_ada') {
                if ($nominalRefund !== 0.0 || $refund->status !== 'tidak_ada_refund') {
                    throw new InvalidArgumentException(
                        'Refund tanpa pengembalian dana harus bernilai nol dan berstatus tidak ada refund.'
                    );
                }

                return;
            }

            $totalPembayaran = $reservasi->totalPaid();

            if ($nominalRefund > $totalPembayaran) {
                throw new InvalidArgumentException('Nominal refund tidak boleh melebihi pembayaran yang telah diterima.');
            }

            if ($refund->jenis_refund === 'penuh' && $nominalRefund !== $totalPembayaran) {
                throw new InvalidArgumentException('Refund penuh harus sama dengan total pembayaran yang telah diterima.');
            }

            if ($refund->jenis_refund === 'sebagian' && $nominalRefund >= $totalPembayaran) {
                throw new InvalidArgumentException('Refund sebagian harus lebih kecil dari total pembayaran yang telah diterima.');
            }

            if ($nominalRefund <= 0.0) {
                throw new InvalidArgumentException('Nominal refund penuh atau sebagian harus lebih besar dari nol.');
            }

            if (! in_array($refund->status, ['menunggu_pengembalian', 'dikembalikan'], true)) {
                throw new InvalidArgumentException('Status refund tidak sesuai dengan jenis refund yang dipilih.');
            }

            if ($refund->status === 'dikembalikan'
                && ($refund->tanggal_refund === null
                    || $refund->metode_refund === null
                    || $refund->dikonfirmasi_pada === null
                    || $refund->dikonfirmasi_oleh_id === null)) {
                throw new InvalidArgumentException(
                    'Refund yang dikembalikan harus memiliki metode, waktu refund, dan metadata konfirmasi.'
                );
            }
        });
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function diputuskanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh_id');
    }

    public function dikonfirmasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh_id');
    }
}
