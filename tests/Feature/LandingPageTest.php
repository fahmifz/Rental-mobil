<?php

use App\Models\KategoriMobil;
use App\Models\Mobil;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the public landing page shows only available cars', function () {
    $kategoriMobil = KategoriMobil::query()->create([
        'nama_kategori' => 'MPV',
    ]);

    $availableMobil = Mobil::query()->create([
        'kategori_mobil_id' => $kategoriMobil->id,
        'nama_mobil' => 'Mobil Tersedia',
        'merek' => 'Toyota',
        'plat_nomor' => 'B 1000 APR',
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);

    $unavailableMobil = Mobil::query()->create([
        'nama_mobil' => 'Mobil Disewa',
        'merek' => 'Toyota',
        'plat_nomor' => 'B 2000 APR',
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 350000,
        'status' => 'disewa',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Awal Putra Rentcar')
        ->assertSee($availableMobil->nama_mobil)
        ->assertDontSee($unavailableMobil->nama_mobil);
});

test('the public landing page has an empty state when no car is available', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Belum ada mobil tersedia');
});
