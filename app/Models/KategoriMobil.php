<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriMobil extends Model
{
    protected $table = 'kategori_mobil';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    public function mobil(): HasMany
    {
        return $this->hasMany(Mobil::class, 'kategori_mobil_id');
    }
}
