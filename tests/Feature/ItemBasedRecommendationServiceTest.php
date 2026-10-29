<?php

use App\Models\Mobil;
use App\Models\Rating;
use App\Models\Reservasi;
use App\Models\User;
use App\Services\ItemBasedRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function ibcfCreateMobil(array $overrides = []): Mobil
{
    static $sequence = 0;
    $sequence++;

    return Mobil::query()->create(array_merge([
        'nama_mobil' => 'Mobil Test '.$sequence,
        'merek' => 'Merek Test',
        'tipe' => 'Sedan',
        'plat_nomor' => 'DD TEST '.$sequence,
        'tahun' => 2024,
        'transmisi' => 'Automatic',
        'kapasitas_penumpang' => 5,
        'harga_sewa_per_hari' => 350000,
        'status' => 'tersedia',
    ], $overrides));
}

function ibcfCreateUser(): User
{
    return User::factory()->create([
        'role' => 'pelanggan',
    ]);
}

function ibcfCreateReservation(User $user, Mobil $mobil, string $status = 'selesai'): Reservasi
{
    return $user->reservasis()->create([
        'mobil_id' => $mobil->id,
        'tanggal_mulai' => today()->subDays(10)->toDateString(),
        'tanggal_selesai' => today()->subDays(5)->toDateString(),
        'durasi_sewa' => 5,
        'tarif_harian' => 350000,
        'total_biaya' => 1750000,
        'status' => $status,
    ]);
}

function ibcfCreateRating(
    User $user,
    Mobil $mobil,
    int $value,
    string $status = 'selesai',
    ?User $ratingUser = null,
    ?Mobil $ratingMobil = null
): Rating {
    $reservation = ibcfCreateReservation($user, $mobil, $status);

    return Rating::query()->create([
        'reservasi_id' => $reservation->id,
        'user_id' => $ratingUser->id ?? $user->id,
        'mobil_id' => $ratingMobil->id ?? $mobil->id,
        'rating' => $value,
    ]);
}

test('service reads valid database ratings and returns a recommendation', function () {
    $target = ibcfCreateUser();
    $other = ibcfCreateUser();
    $carA = ibcfCreateMobil(['nama_mobil' => 'Mobil A']);
    $carB = ibcfCreateMobil(['nama_mobil' => 'Mobil B']);

    ibcfCreateRating($target, $carA, 5);
    ibcfCreateRating($other, $carA, 3);
    ibcfCreateRating($other, $carB, 5);

    $recommendations = app(ItemBasedRecommendationService::class)
        ->recommendForUser($target->id);

    expect($recommendations)->toBeArray()->not->toBeEmpty()
        ->and(array_column($recommendations, 'mobil_id'))->toContain($carB->id)
        ->and($recommendations[0])->toHaveKeys(['mobil_id', 'predicted_rating', 'neighbor_count']);
});

test('ratings from reservations not completed are excluded', function () {
    $target = ibcfCreateUser();
    $other = ibcfCreateUser();
    $carA = ibcfCreateMobil(['nama_mobil' => 'Mobil A']);
    $carB = ibcfCreateMobil(['nama_mobil' => 'Mobil B']);

    ibcfCreateRating($target, $carA, 5);
    ibcfCreateRating($other, $carA, 4);
    ibcfCreateRating($other, $carB, 5, 'disetujui');

    $recommendations = app(ItemBasedRecommendationService::class)
        ->recommendForUser($target->id);

    expect($recommendations)->toBe([]);
});

test('ratings with inconsistent user or car references are excluded', function () {
    $target = ibcfCreateUser();
    $wrongUser = ibcfCreateUser();
    $carA = ibcfCreateMobil(['nama_mobil' => 'Mobil A']);
    $carB = ibcfCreateMobil(['nama_mobil' => 'Mobil B']);
    $wrongCar = ibcfCreateMobil(['nama_mobil' => 'Wrong Car']);

    ibcfCreateRating($target, $carA, 5);
    ibcfCreateRating($target, $carB, 4, 'selesai', $wrongUser);
    ibcfCreateRating($target, $wrongCar, 3, 'selesai', null, $carA);

    $recommendations = app(ItemBasedRecommendationService::class)
        ->recommendForUser($target->id);

    expect($recommendations)->toBe([]);
});

test('user without ratings returns an empty recommendation array', function () {
    $userWithoutRatings = ibcfCreateUser();
    $other = ibcfCreateUser();
    $carA = ibcfCreateMobil(['nama_mobil' => 'Mobil A']);
    $carB = ibcfCreateMobil(['nama_mobil' => 'Mobil B']);

    ibcfCreateRating($other, $carA, 5);
    ibcfCreateRating($other, $carB, 4);

    $recommendations = app(ItemBasedRecommendationService::class)
        ->recommendForUser($userWithoutRatings->id);

    expect($recommendations)->toBe([]);
});

test('user that rated every available candidate receives no recommendation', function () {
    $target = ibcfCreateUser();
    $other = ibcfCreateUser();
    $carA = ibcfCreateMobil(['nama_mobil' => 'Mobil A']);
    $carB = ibcfCreateMobil(['nama_mobil' => 'Mobil B']);

    ibcfCreateRating($target, $carA, 5);
    ibcfCreateRating($target, $carB, 4);
    ibcfCreateRating($other, $carA, 3);
    ibcfCreateRating($other, $carB, 5);

    $recommendations = app(ItemBasedRecommendationService::class)
        ->recommendForUser($target->id);

    expect($recommendations)->toBe([]);
});

test('database ratings produce a prediction matching an independent manual calculation', function () {
    $target = ibcfCreateUser();
    $otherA = ibcfCreateUser();
    $otherB = ibcfCreateUser();
    $carA = ibcfCreateMobil(['nama_mobil' => 'Mobil A']);
    $carB = ibcfCreateMobil(['nama_mobil' => 'Mobil B']);
    $carC = ibcfCreateMobil(['nama_mobil' => 'Mobil C']);

    ibcfCreateRating($target, $carA, 5);
    ibcfCreateRating($target, $carC, 1);

    ibcfCreateRating($otherA, $carA, 3);
    ibcfCreateRating($otherA, $carC, 4);
    ibcfCreateRating($otherA, $carB, 5);

    ibcfCreateRating($otherB, $carA, 2);
    ibcfCreateRating($otherB, $carC, 5);
    ibcfCreateRating($otherB, $carB, 4);

    $vectorA = [$target->id => 5, $otherA->id => 3, $otherB->id => 2];
    $vectorC = [$target->id => 1, $otherA->id => 4, $otherB->id => 5];
    $vectorB = [$otherA->id => 5, $otherB->id => 4];

    $dotAB = 3 * 5 + 2 * 4;
    $normA = sqrt(5 ** 2 + 3 ** 2 + 2 ** 2);
    $normB = sqrt(5 ** 2 + 4 ** 2);
    $similarityAB = $dotAB / ($normA * $normB);

    $dotCB = 4 * 5 + 5 * 4;
    $normC = sqrt(1 ** 2 + 4 ** 2 + 5 ** 2);
    $similarityCB = $dotCB / ($normC * $normB);

    $manualPrediction = (
        ($similarityAB * 5) + ($similarityCB * 1)
    ) / (
        abs($similarityAB) + abs($similarityCB)
    );

    expect($vectorB)->not->toHaveKey($target->id);

    $recommendations = app(ItemBasedRecommendationService::class)
        ->recommendForUser($target->id);

    $candidate = collect($recommendations)
        ->firstWhere('mobil_id', $carB->id);

    expect($candidate)->not->toBeNull()
        ->and(abs($candidate['predicted_rating'] - $manualPrediction))->toBeLessThan(0.000001)
        ->and(abs($candidate['predicted_rating'] - 2.505))->toBeLessThan(0.01);
});
