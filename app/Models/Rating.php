<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = [
        'reservasi_id',
        'user_id',
        'mobil_id',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mobil(): BelongsTo
    {
        return $this->belongsTo(Mobil::class);
    }
}
