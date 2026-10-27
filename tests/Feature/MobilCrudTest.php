<?php

use App\Models\KategoriMobil;
use App\Models\Mobil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();

    $this->actingAs($admin);
});

function mobilPayload(array $overrides = []): array
{
    return array_merge([
        'nama_mobil' => 'Toyota Avanza',
        'merek' => 'Toyota',
        'tipe' => 'MPV',
        'plat_nomor' => 'B 1234 APR',
        'tahun' => 2025,
        'transmisi' => 'Automatic',
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 350000,
        'deskripsi' => 'Mobil keluarga.',
        'status' => 'tersedia',
    ], $overrides);
}

test('a car can be created with a photo and category', function () {
    Storage::fake('public');
    $kategoriMobil = KategoriMobil::query()->create(['nama_kategori' => 'MPV']);

    $this->post(route('mobil.store'), mobilPayload([
        'kategori_mobil_id' => $kategoriMobil->id,
        'foto' => UploadedFile::fake()->image('avanza.jpg'),
    ]))
        ->assertRedirect(route('mobil.index'))
        ->assertSessionHas('success');

    $mobil = Mobil::query()->firstOrFail();

    expect($mobil->kategori_mobil_id)->toBe($kategoriMobil->id);
    Storage::disk('public')->assertExists($mobil->foto);
});

test('a car photo is replaced safely on update', function () {
    Storage::fake('public');
    Storage::disk('public')->put('mobil/old.jpg', 'old photo');
    $mobil = Mobil::query()->create(mobilPayload(['foto' => 'mobil/old.jpg']));

    $this->put(route('mobil.update', $mobil), mobilPayload([
        'nama_mobil' => 'Toyota Avanza Baru',
        'foto' => UploadedFile::fake()->image('new.jpg'),
    ]))
        ->assertRedirect(route('mobil.index'));

    $mobil->refresh();

    Storage::disk('public')->assertMissing('mobil/old.jpg');
    Storage::disk('public')->assertExists($mobil->foto);
});

test('a car photo is deleted when its car is deleted', function () {
    Storage::fake('public');
    Storage::disk('public')->put('mobil/delete.jpg', 'photo');
    $mobil = Mobil::query()->create(mobilPayload(['foto' => 'mobil/delete.jpg']));

    $this->delete(route('mobil.destroy', $mobil))
        ->assertRedirect(route('mobil.index'));

    $this->assertDatabaseMissing('mobil', ['id' => $mobil->id]);
    Storage::disk('public')->assertMissing('mobil/delete.jpg');
});

test('a duplicate license plate is rejected', function () {
    Mobil::query()->create(mobilPayload());

    $this->post(route('mobil.store'), mobilPayload())
        ->assertSessionHasErrors('plat_nomor');
});
