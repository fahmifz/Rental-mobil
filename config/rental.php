<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tarif Denda Keterlambatan Pengembalian (Per Hari)
    |--------------------------------------------------------------------------
    |
    | Tarif denda yang dikenakan per hari keterlambatan pengembalian unit mobil.
    | Nilai dapat disesuaikan melalui environment variable TARIF_DENDA_PER_HARI.
    |
    */
    'tarif_denda_per_hari' => (float) env('TARIF_DENDA_PER_HARI', 50000.0),
];

