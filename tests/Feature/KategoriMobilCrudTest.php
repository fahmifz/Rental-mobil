<?php

use App\Models\KategoriMobil;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('car category index page can be displayed', function () {
    $this->get(route('kategori-mobil.index'))
        ->assertOk()
        ->assertSee('Car Categories');
});

test('a car category can be created', function () {
    $this->post(route('kategori-mobil.store'), [
        'nama_kategori' => 'SUV',
        'deskripsi' => 'Sport utility vehicles.',
    ])
        ->assertRedirect(route('kategori-mobil.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('kategori_mobil', [
        'nama_kategori' => 'SUV',
        'deskripsi' => 'Sport utility vehicles.',
    ]);
});

test('a car category can be updated', function () {
    $kategoriMobil = KategoriMobil::query()->create([
        'nama_kategori' => 'SUV',
        'deskripsi' => 'Initial description.',
    ]);

    $this->put(route('kategori-mobil.update', $kategoriMobil), [
        'nama_kategori' => 'Premium SUV',
        'deskripsi' => 'Updated description.',
    ])
        ->assertRedirect(route('kategori-mobil.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('kategori_mobil', [
        'id' => $kategoriMobil->id,
        'nama_kategori' => 'Premium SUV',
        'deskripsi' => 'Updated description.',
    ]);
});

test('a car category can be deleted', function () {
    $kategoriMobil = KategoriMobil::query()->create([
        'nama_kategori' => 'MPV',
    ]);

    $this->delete(route('kategori-mobil.destroy', $kategoriMobil))
        ->assertRedirect(route('kategori-mobil.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('kategori_mobil', [
        'id' => $kategoriMobil->id,
    ]);
});
