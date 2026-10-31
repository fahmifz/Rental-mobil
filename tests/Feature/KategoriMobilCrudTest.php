<?php

use App\Models\KategoriMobil;
use App\Models\Mobil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();

    $this->actingAs($admin);
});

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

test('a category name already in use is rejected on create', function () {
    KategoriMobil::query()->create([
        'nama_kategori' => 'SUV',
        'deskripsi' => 'Existing category.',
    ]);

    $this->from(route('kategori-mobil.create'))
        ->post(route('kategori-mobil.store'), [
            'nama_kategori' => 'SUV',
            'deskripsi' => 'Duplicate category.',
        ])
        ->assertRedirect(route('kategori-mobil.create'))
        ->assertSessionHasErrors('nama_kategori');

    expect(KategoriMobil::query()->where('nama_kategori', 'SUV')->count())->toBe(1);
});

test('a category can keep its own name during update', function () {
    $kategoriMobil = KategoriMobil::query()->create([
        'nama_kategori' => 'SUV',
        'deskripsi' => 'Existing category.',
    ]);

    $this->put(route('kategori-mobil.update', $kategoriMobil), [
        'nama_kategori' => 'SUV',
        'deskripsi' => 'Updated description.',
    ])
        ->assertRedirect(route('kategori-mobil.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('kategori_mobil', [
        'id' => $kategoriMobil->id,
        'nama_kategori' => 'SUV',
        'deskripsi' => 'Updated description.',
    ]);
});

test('a category name cannot be changed to another category name', function () {
    $kategoriMobil = KategoriMobil::query()->create([
        'nama_kategori' => 'SUV',
    ]);
    KategoriMobil::query()->create([
        'nama_kategori' => 'MPV',
    ]);

    $this->from(route('kategori-mobil.edit', $kategoriMobil))
        ->put(route('kategori-mobil.update', $kategoriMobil), [
            'nama_kategori' => 'MPV',
            'deskripsi' => 'Duplicate category name.',
        ])
        ->assertRedirect(route('kategori-mobil.edit', $kategoriMobil))
        ->assertSessionHasErrors('nama_kategori');

    expect($kategoriMobil->fresh()->nama_kategori)->toBe('SUV');
});

test('deleting a category used by a car keeps the car and nulls its category', function () {
    $kategoriMobil = KategoriMobil::query()->create([
        'nama_kategori' => 'SUV',
    ]);
    $mobil = Mobil::query()->create([
        'kategori_mobil_id' => $kategoriMobil->id,
        'nama_mobil' => 'Toyota Rush',
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 1001 KAT',
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 400000,
        'status' => 'tersedia',
    ]);

    $this->delete(route('kategori-mobil.destroy', $kategoriMobil))
        ->assertRedirect(route('kategori-mobil.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('kategori_mobil', [
        'id' => $kategoriMobil->id,
    ]);
    $this->assertDatabaseHas('mobil', [
        'id' => $mobil->id,
        'kategori_mobil_id' => null,
    ]);
});
