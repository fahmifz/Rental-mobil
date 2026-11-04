<?php

use App\Models\Mobil;
use App\Models\Rating;
use App\Models\Reservasi;
use App\Models\User;
use App\Services\UserBasedRecommendationService;
use Database\Seeders\CollaborativeFilteringDummySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('the collaborative filtering dummy seeder creates valid and reusable UBCF data', function () {
    $existingUser = User::factory()->create([
        'name' => 'Pelanggan Asli',
        'email' => 'pelanggan-asli@example.test',
        'role' => 'pelanggan',
    ]);
    $existingMobil = Mobil::query()->create([
        'nama_mobil' => 'Mobil Asli',
        'merek' => 'Toyota',
        'plat_nomor' => 'DD 1234 ASLI',
        'kapasitas_penumpang' => 5,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ]);

    $this->seed(CollaborativeFilteringDummySeeder::class);

    $dummyUsers = User::query()
        ->where('email', 'like', 'cf-dummy-pelanggan-%@example.test')
        ->get();
    $dummyMobils = Mobil::query()
        ->where('plat_nomor', 'like', 'CFD-%-RCM')
        ->get();
    $dummyReservations = Reservasi::query()
        ->whereIn('user_id', $dummyUsers->pluck('id'))
        ->get();
    $dummyRatings = Rating::query()
        ->whereIn('user_id', $dummyUsers->pluck('id'))
        ->with('reservasi')
        ->get();

    expect($dummyUsers)->toHaveCount(15)
        ->and($dummyUsers->every(fn (User $user) => $user->isPelanggan()))->toBeTrue()
        ->and(Hash::check(CollaborativeFilteringDummySeeder::DEMO_PASSWORD, $dummyUsers->first()->password))->toBeTrue()
        ->and($dummyMobils)->toHaveCount(10)
        ->and($dummyReservations)->toHaveCount(75)
        ->and($dummyRatings)->toHaveCount(75)
        ->and($dummyRatings->every(fn (Rating $rating) => $rating->reservasi->status === 'selesai'))->toBeTrue();

    expect($existingUser->fresh()->name)->toBe('Pelanggan Asli')
        ->and($existingUser->fresh()->email)->toBe('pelanggan-asli@example.test')
        ->and($existingMobil->fresh()->nama_mobil)->toBe('Mobil Asli')
        ->and($existingMobil->fresh()->plat_nomor)->toBe('DD 1234 ASLI');

    $this->seed(CollaborativeFilteringDummySeeder::class);

    expect(User::query()->where('email', 'like', 'cf-dummy-pelanggan-%@example.test')->count())->toBe(15)
        ->and(Mobil::query()->where('plat_nomor', 'like', 'CFD-%-RCM')->count())->toBe(10)
        ->and(Reservasi::query()->whereIn('user_id', $dummyUsers->pluck('id'))->count())->toBe(75)
        ->and(Rating::query()->whereIn('user_id', $dummyUsers->pluck('id'))->count())->toBe(75);
});

test('a seeded demonstration customer receives user based recommendations', function () {
    $this->seed(CollaborativeFilteringDummySeeder::class);

    $demonstrationCustomer = User::query()
        ->where('email', 'cf-dummy-pelanggan-01@example.test')
        ->firstOrFail();
    $recommendations = app(UserBasedRecommendationService::class)
        ->recommendForUser($demonstrationCustomer->id);

    expect($recommendations)->not->toBeEmpty()
        ->and(collect($recommendations)->pluck('mobil_id')->every(
            fn (mixed $mobilId): bool => is_int($mobilId)
        ))->toBeTrue();
});
