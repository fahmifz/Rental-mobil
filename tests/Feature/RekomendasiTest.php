<?php

use App\Models\Mobil;
use App\Models\User;
use App\Services\ItemBasedRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function rekomendasiCreateMobil(array $overrides = []): Mobil
{
    static $sequence = 0;
    $sequence++;

    return Mobil::query()->create(array_merge([
        'nama_mobil' => 'Mobil '.$sequence,
        'merek' => 'Merek Test',
        'plat_nomor' => 'DD RC '.$sequence,
        'kapasitas_penumpang' => 5,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ], $overrides));
}

function rekomendasiCreatePelanggan(): User
{
    return User::factory()->create([
        'role' => 'pelanggan',
    ]);
}

test('customers must authenticate to access recommendations', function () {
    $this->get(route('pelanggan.rekomendasi.index'))
        ->assertRedirect(route('login'));
});

test('administrators cannot access recommendations', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('pelanggan.rekomendasi.index'))
        ->assertForbidden();
});

test('authenticated customer sees the empty recommendation state', function () {
    $customer = rekomendasiCreatePelanggan();

    $this->mock(ItemBasedRecommendationService::class)
        ->shouldReceive('recommendForUser')
        ->once()
        ->with($customer->id)
        ->andReturn([]);

    $this->actingAs($customer)
        ->get(route('pelanggan.rekomendasi.index'))
        ->assertOk()
        ->assertSee('Belum ada rekomendasi')
        ->assertSee('Rating dan tetangga yang cukup belum tersedia');
});

test('available cars preserve recommendation ranking and unavailable cars are hidden', function () {
    $customer = rekomendasiCreatePelanggan();
    $first = rekomendasiCreateMobil(['nama_mobil' => 'Mobil Ranking Pertama']);
    $hidden = rekomendasiCreateMobil([
        'nama_mobil' => 'Mobil Tidak Tersedia',
        'status' => 'perawatan',
    ]);
    $second = rekomendasiCreateMobil(['nama_mobil' => 'Mobil Ranking Kedua']);

    $this->mock(ItemBasedRecommendationService::class)
        ->shouldReceive('recommendForUser')
        ->once()
        ->with($customer->id)
        ->andReturn([
            ['mobil_id' => $first->id, 'predicted_rating' => 4.9, 'neighbor_count' => 3],
            ['mobil_id' => $hidden->id, 'predicted_rating' => 4.8, 'neighbor_count' => 3],
            ['mobil_id' => $second->id, 'predicted_rating' => 4.5, 'neighbor_count' => 2],
        ]);

    $response = $this->actingAs($customer)
        ->get(route('pelanggan.rekomendasi.index'))
        ->assertOk()
        ->assertSee('Mobil Ranking Pertama')
        ->assertSee('Mobil Ranking Kedua')
        ->assertDontSee('Mobil Tidak Tersedia');

    $response->assertSeeInOrder([
        'Mobil Ranking Pertama',
        'Mobil Ranking Kedua',
    ]);
});
