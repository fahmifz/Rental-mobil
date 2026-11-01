<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengembalian extends Model
{
    protected $fillable = [
        'reservasi_id',
        'tanggal_pengembalian',
        'kondisi_mobil',
        'catatan_kondisi',
        'durasi_keterlambatan',
        'tarif_denda_per_hari',
        'total_denda',
        'status_mobil_setelah_kembali',
        'diproses_oleh_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengembalian' => 'datetime',
            'durasi_keterlambatan' => 'integer',
            'tarif_denda_per_hari' => 'decimal:2',
            'total_denda' => 'decimal:2',
        ];
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh_id');
    }

    public function isLate(): bool
    {
        return $this->durasi_keterlambatan > 0;
    }

    /**
     * Menghitung keterlambatan dan denda pengembalian mobil.
     * Batas pengembalian adalah akhir hari pada tanggal_selesai (23:59:59).
     * Jika terlambat, durasi keterlambatan dibulatkan ke atas per hari (ceil).
     *
     * @return array{durasi_keterlambatan: int, tarif_denda_per_hari: float, total_denda: float, is_late: bool}
     */
    public static function calculateLateReturn(Reservasi $reservasi, CarbonInterface $actualReturn): array
    {
        $deadline = $reservasi->tanggal_selesai->copy()->endOfDay();
        $tarifDenda = (float) config('rental.tarif_denda_per_hari', 50000.0);

        if ($actualReturn->lte($deadline)) {
            return [
                'durasi_keterlambatan' => 0,
                'tarif_denda_per_hari' => $tarifDenda,
                'total_denda' => 0.0,
                'is_late' => false,
            ];
        }

        $diffInSeconds = $deadline->diffInSeconds($actualReturn);
        $lateDays = (int) ceil($diffInSeconds / 86400);
        $totalDenda = round($lateDays * $tarifDenda, 2);

        return [
            'durasi_keterlambatan' => $lateDays,
            'tarif_denda_per_hari' => $tarifDenda,
            'total_denda' => $totalDenda,
            'is_late' => true,
        ];
    }
}

