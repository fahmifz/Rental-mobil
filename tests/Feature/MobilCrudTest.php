<?php

use App\Models\KategoriMobil;
use App\Models\Mobil;
use App\Models\Rating;
use App\Models\Reservasi;
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

test('a car with a reservation cannot be deleted and keeps its photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('mobil/reserved.jpg', 'photo');
    $mobil = Mobil::query()->create(mobilPayload(['foto' => 'mobil/reserved.jpg']));
    $customer = User::factory()->create(['role' => 'pelanggan']);

    Reservasi::query()->create([
        'user_id' => $customer->id,
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->toDateString(),
        'tanggal_selesai' => today()->addDay()->toDateString(),
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => 'menunggu_konfirmasi',
    ]);

    $this->delete(route('mobil.destroy', $mobil))
        ->assertRedirect(route('mobil.index'))
        ->assertSessionHas('error', 'Mobil tidak dapat dihapus karena masih memiliki reservasi terkait.');

    $this->assertDatabaseHas('mobil', ['id' => $mobil->id]);
    Storage::disk('public')->assertExists('mobil/reserved.jpg');
});

test('a car with a rating cannot be deleted and keeps its photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('mobil/rated.jpg', 'photo');
    $mobil = Mobil::query()->create(mobilPayload(['foto' => 'mobil/rated.jpg']));
    $customer = User::factory()->create(['role' => 'pelanggan']);

    $reservasi = Reservasi::query()->create([
        'user_id' => $customer->id,
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->subDays(3)->toDateString(),
        'tanggal_selesai' => today()->subDays(1)->toDateString(),
        'durasi_sewa' => 3,
        'tarif_harian' => 350000,
        'total_biaya' => 1050000,
        'status' => 'selesai',
    ]);

    Rating::query()->create([
        'reservasi_id' => $reservasi->id,
        'user_id' => $customer->id,
        'mobil_id' => $mobil->id,
        'rating' => 5,
    ]);

    $this->delete(route('mobil.destroy', $mobil))
        ->assertRedirect(route('mobil.index'))
        ->assertSessionHas('error', 'Mobil tidak dapat dihapus karena masih memiliki rating terkait.');

    $this->assertDatabaseHas('mobil', ['id' => $mobil->id]);
    Storage::disk('public')->assertExists('mobil/rated.jpg');
});

test('editing a car cannot change an active rented car to available', function () {
    $mobil = Mobil::query()->create(mobilPayload(['status' => 'disewa']));
    $customer = User::factory()->create(['role' => 'pelanggan']);

    Reservasi::query()->create([
        'user_id' => $customer->id,
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->toDateString(),
        'tanggal_selesai' => today()->addDay()->toDateString(),
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => 'disetujui',
    ]);

    $this->from(route('mobil.edit', $mobil))
        ->put(route('mobil.update', $mobil), mobilPayload([
            'status' => 'tersedia',
        ]))
        ->assertRedirect(route('mobil.edit', $mobil))
        ->assertSessionHasErrors('status');

    expect($mobil->fresh()->status)->toBe('disewa');
});

test('a car without an active reservation can be updated back to available', function () {
    $mobil = Mobil::query()->create(mobilPayload(['status' => 'disewa']));

    $this->put(route('mobil.update', $mobil), mobilPayload([
        'status' => 'tersedia',
    ]))
        ->assertRedirect(route('mobil.index'))
        ->assertSessionHas('success', 'Mobil berhasil diperbarui.');

    expect($mobil->fresh()->status)->toBe('tersedia');
});

test('editing a car keeps maintenance status even with an active reservation', function () {
    $mobil = Mobil::query()->create(mobilPayload(['status' => 'perawatan']));
    $customer = User::factory()->create(['role' => 'pelanggan']);

    Reservasi::query()->create([
        'user_id' => $customer->id,
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->toDateString(),
        'tanggal_selesai' => today()->addDay()->toDateString(),
        'durasi_sewa' => 2,
        'tarif_harian' => 350000,
        'total_biaya' => 700000,
        'status' => 'disetujui',
    ]);

    $this->put(route('mobil.update', $mobil), mobilPayload([
        'status' => 'perawatan',
    ]))
        ->assertRedirect(route('mobil.index'))
        ->assertSessionHas('success', 'Mobil berhasil diperbarui.');

    expect($mobil->fresh()->status)->toBe('perawatan');
});

test('a future approved reservation does not make an edited car rented', function () {
    $mobil = Mobil::query()->create(mobilPayload(['status' => 'tersedia']));
    $customer = User::factory()->create(['role' => 'pelanggan']);

    Reservasi::query()->create([
        'user_id' => $customer->id,
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->addDays(2)->toDateString(),
        'tanggal_selesai' => today()->addDays(4)->toDateString(),
        'durasi_sewa' => 3,
        'tarif_harian' => 350000,
        'total_biaya' => 1050000,
        'status' => 'disetujui',
    ]);

    $this->put(route('mobil.update', $mobil), mobilPayload([
        'status' => 'tersedia',
    ]))
        ->assertRedirect(route('mobil.index'));

    expect($mobil->fresh()->status)->toBe('tersedia');
});
