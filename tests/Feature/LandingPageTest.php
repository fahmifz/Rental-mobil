<?php

use App\Models\KategoriMobil;
use App\Models\Mobil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

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

test('the public landing page builds hero slides from available cars with valid public photos', function () {
    Storage::fake('public');

    $kategoriMobil = KategoriMobil::query()->create([
        'nama_kategori' => 'SUV',
    ]);

    Storage::disk('public')->put('mobil/hero-tersedia.jpg', 'valid image fixture');
    Storage::disk('public')->put('mobil/hero-kedua.jpg', 'valid image fixture');
    Storage::disk('public')->put('mobil/hero-tidak-tersedia.jpg', 'valid image fixture');

    $heroMobil = Mobil::query()->create([
        'kategori_mobil_id' => $kategoriMobil->id,
        'nama_mobil' => 'Mobil Hero Tersedia',
        'merek' => 'Toyota',
        'tipe' => 'Fortuner',
        'plat_nomor' => 'B 3000 APR',
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 650000,
        'foto' => 'mobil/hero-tersedia.jpg',
        'status' => 'tersedia',
    ]);

    $secondHeroMobil = Mobil::query()->create([
        'kategori_mobil_id' => $kategoriMobil->id,
        'nama_mobil' => 'Mobil Hero Kedua',
        'merek' => 'Mitsubishi',
        'tipe' => 'Pajero Sport',
        'plat_nomor' => 'B 3003 APR',
        'kapasitas_penumpang' => 7,
        'harga_sewa_per_hari' => 700000,
        'foto' => 'mobil/hero-kedua.jpg',
        'status' => 'tersedia',
    ]);

    Mobil::query()->create([
        'nama_mobil' => 'Mobil Foto Hilang',
        'merek' => 'Toyota',
        'plat_nomor' => 'B 3001 APR',
        'harga_sewa_per_hari' => 500000,
        'foto' => 'mobil/tidak-ada.jpg',
        'status' => 'tersedia',
    ]);

    Mobil::query()->create([
        'nama_mobil' => 'Mobil Tidak Tersedia',
        'merek' => 'Toyota',
        'plat_nomor' => 'B 3002 APR',
        'harga_sewa_per_hari' => 550000,
        'foto' => 'mobil/hero-tidak-tersedia.jpg',
        'status' => 'disewa',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertViewHas('heroMobils', fn ($heroMobils) => $heroMobils->count() === 2
            && $heroMobils->contains('id', $heroMobil->id)
            && $heroMobils->contains('id', $secondHeroMobil->id))
        ->assertSee('aria-roledescription="carousel"', false)
        ->assertSee('Gambar sebelumnya')
        ->assertSee('Gambar berikutnya')
        ->assertSee(Storage::disk('public')->url($heroMobil->foto), false)
        ->assertSee(Storage::disk('public')->url($secondHeroMobil->foto), false)
        ->assertSee($heroMobil->nama_mobil)
        ->assertSee($heroMobil->merek.' · '.$heroMobil->tipe)
        ->assertSee('Rp650.000')
        ->assertDontSee('Mobil Tidak Tersedia')
        ->assertSee('hero-slider-progress', false)
        ->assertSee('prefers-reduced-motion', false)
        ->assertSee('visibilitychange', false)
        ->assertSee('if (this.isPaused || this.isDocumentHidden || this.totalSlides < 2 || this.autoplay !== null) return;', false)
        ->assertDontSee('if (this.reducedMotion || this.isPaused', false)
        ->assertSee('this.autoplay = window.setInterval(() => this.next(), 8000);', false)
        ->assertSee('duration-[1200ms]', false)
        ->assertSee('scale-[1.02]', false);
});

test('the landing page keeps a fallback hero and hides controls when no hero photo is valid', function () {
    Mobil::query()->create([
        'nama_mobil' => 'Mobil Tanpa Foto Valid',
        'merek' => 'Toyota',
        'plat_nomor' => 'B 4000 APR',
        'harga_sewa_per_hari' => 400000,
        'foto' => 'mobil/foto-tidak-ada.jpg',
        'status' => 'tersedia',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('images/hero/rentcar-hero.png')
        ->assertDontSee('Gambar sebelumnya')
        ->assertDontSee('Gambar berikutnya');
});

test('the landing page hides slider controls when exactly one valid hero photo is available', function () {
    Storage::fake('public');
    Storage::disk('public')->put('mobil/satu-hero.jpg', 'valid image fixture');

    $mobil = Mobil::query()->create([
        'nama_mobil' => 'Mobil Satu Hero',
        'merek' => 'Suzuki',
        'plat_nomor' => 'B 4500 APR',
        'harga_sewa_per_hari' => 375000,
        'foto' => 'mobil/satu-hero.jpg',
        'status' => 'tersedia',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertViewHas('heroMobils', fn ($heroMobils) => $heroMobils->count() === 1
            && $heroMobils->first()->is($mobil))
        ->assertSee($mobil->nama_mobil)
        ->assertDontSee('Gambar sebelumnya')
        ->assertDontSee('Gambar berikutnya');
});

test('the landing page reflects an updated available car photo on the next request', function () {
    Storage::fake('public');
    Storage::disk('public')->put('mobil/foto-awal.jpg', 'valid image fixture');
    Storage::disk('public')->put('mobil/foto-baru.jpg', 'valid image fixture');

    $mobil = Mobil::query()->create([
        'nama_mobil' => 'Mobil Foto Diperbarui',
        'merek' => 'Daihatsu',
        'plat_nomor' => 'B 4600 APR',
        'harga_sewa_per_hari' => 360000,
        'foto' => 'mobil/foto-awal.jpg',
        'status' => 'tersedia',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url('mobil/foto-awal.jpg'), false);

    $mobil->update(['foto' => 'mobil/foto-baru.jpg']);

    $this->get(route('home'))
        ->assertOk()
        ->assertViewHas('heroMobils', fn ($heroMobils) => $heroMobils->first()->is($mobil->fresh()))
        ->assertSee(Storage::disk('public')->url('mobil/foto-baru.jpg'), false)
        ->assertDontSee(Storage::disk('public')->url('mobil/foto-awal.jpg'), false);
});

test('the landing page exposes category, transmission, and price filter data for available cars', function () {
    $kategoriMobil = KategoriMobil::query()->create([
        'nama_kategori' => 'MPV',
    ]);

    $mobil = Mobil::query()->create([
        'kategori_mobil_id' => $kategoriMobil->id,
        'nama_mobil' => 'Mobil Filter',
        'merek' => 'Honda',
        'transmisi' => 'Automatic',
        'plat_nomor' => 'B 5000 APR',
        'harga_sewa_per_hari' => 425000,
        'status' => 'tersedia',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Semua kategori')
        ->assertSee($kategoriMobil->nama_kategori)
        ->assertSee('Semua transmisi')
        ->assertSee($mobil->transmisi)
        ->assertSee('Harga terendah')
        ->assertSee('Harga tertinggi')
        ->assertSee('data-mobil-card', false)
        ->assertSee('data-category="'.$kategoriMobil->id.'"', false)
        ->assertSee('data-transmission="automatic"', false)
        ->assertSee('data-price="'.$mobil->harga_sewa_per_hari.'"', false)
        ->assertSee('resultCount', false);
});
