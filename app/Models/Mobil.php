<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mobil extends Model
{
    protected $table = 'mobil';

    protected $fillable = [
        'kategori_mobil_id',
        'nama_mobil',
        'merek',
        'tipe',
        'plat_nomor',
        'tahun',
        'transmisi',
        'kapasitas_penumpang',
        'harga_sewa_per_hari',
        'deskripsi',
        'foto',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'kapasitas_penumpang' => 'integer',
            'harga_sewa_per_hari' => 'decimal:2',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(
            KategoriMobil::class,
            'kategori_mobil_id'
        );
    }

    public function reservasis(): HasMany
    {
        return $this->hasMany(Reservasi::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
