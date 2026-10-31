<?php

use App\Models\Mobil;
use App\Models\Rating;
use App\Models\Reservasi;
use App\Models\User;
use App\Services\UserBasedRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function ubcfCreateMobil(array $overrides = []): Mobil
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

function ubcfCreateUser(): User
{
    return User::factory()->create([
        'role' => 'pelanggan',
    ]);
}

function ubcfCreateReservation(User $user, Mobil $mobil, string $status = 'selesai'): Reservasi
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

function ubcfCreateRating(
    User $user,
    Mobil $mobil,
    int $value,
    string $status = 'selesai',
    ?User $ratingUser = null,
    ?Mobil $ratingMobil = null
): Rating {
    $reservation = ubcfCreateReservation($user, $mobil, $status);

    return Rating::query()->create([
        'reservasi_id' => $reservation->id,
        'user_id' => $ratingUser->id ?? $user->id,
        'mobil_id' => $ratingMobil->id ?? $mobil->id,
        'rating' => $value,
    ]);
}

test('database ratings produce a user based recommendation with independent mean centered calculation', function () {
    $target = ubcfCreateUser();
    $neighbor = ubcfCreateUser();
    $carA = ubcfCreateMobil();
    $carB = ubcfCreateMobil();
    $carC = ubcfCreateMobil();

    ubcfCreateRating($target, $carA, 5);
    ubcfCreateRating($target, $carB, 3);
    ubcfCreateRating($neighbor, $carA, 4);
    ubcfCreateRating($neighbor, $carB, 4);
    ubcfCreateRating($neighbor, $carC, 5);

    $targetMean = 4.0;
    $neighborMean = (4 + 4 + 5) / 3;
    $dot = 5 * 4 + 3 * 4;
    $targetNorm = sqrt(5 ** 2 + 3 ** 2);
    $neighborNorm = sqrt(4 ** 2 + 4 ** 2 + 5 ** 2);
    $similarity = $dot / ($targetNorm * $neighborNorm);
    $manualPrediction = $targetMean
        + $similarity * (5 - $neighborMean) / abs($similarity);

    $recommendations = app(UserBasedRecommendationService::class)
        ->recommendForUser($target->id);

    $candidate = collect($recommendations)->firstWhere('mobil_id', $carC->id);

    expect($candidate)->not->toBeNull()
        ->and(abs($candidate['predicted_rating'] - $manualPrediction))->toBeLessThan(0.000001)
        ->and($candidate['neighbor_count'])->toBe(1);
});

test('ratings from reservations that are not completed are excluded', function () {
    $target = ubcfCreateUser();
    $neighbor = ubcfCreateUser();
    $carA = ubcfCreateMobil();
    $carB = ubcfCreateMobil();

    ubcfCreateRating($target, $carA, 5);
    ubcfCreateRating($neighbor, $carA, 4);
    ubcfCreateRating($neighbor, $carB, 5, 'disetujui');

    expect(app(UserBasedRecommendationService::class)
        ->recommendForUser($target->id))->toBe([]);
});

test('ratings inconsistent with reservation ownership are excluded', function () {
    $target = ubcfCreateUser();
    $wrongUser = ubcfCreateUser();
    $carA = ubcfCreateMobil();
    $carB = ubcfCreateMobil();

    ubcfCreateRating($target, $carA, 5);
    ubcfCreateRating($target, $carB, 4, 'selesai', $wrongUser);

    expect(app(UserBasedRecommendationService::class)
        ->recommendForUser($target->id))->toBe([]);
});

test('user without ratings returns an empty array', function () {
    $target = ubcfCreateUser();
    $neighbor = ubcfCreateUser();
    $carA = ubcfCreateMobil();
    $carB = ubcfCreateMobil();

    ubcfCreateRating($neighbor, $carA, 5);
    ubcfCreateRating($neighbor, $carB, 4);

    expect(app(UserBasedRecommendationService::class)
        ->recommendForUser($target->id))->toBe([]);
});

test('user who rated every candidate receives no recommendation', function () {
    $target = ubcfCreateUser();
    $neighbor = ubcfCreateUser();
    $carA = ubcfCreateMobil();
    $carB = ubcfCreateMobil();

    ubcfCreateRating($target, $carA, 5);
    ubcfCreateRating($target, $carB, 4);
    ubcfCreateRating($neighbor, $carA, 3);
    ubcfCreateRating($neighbor, $carB, 5);

    expect(app(UserBasedRecommendationService::class)
        ->recommendForUser($target->id))->toBe([]);
});

test('upper and lower prediction clamps are verified through database data', function () {
    $upperTarget = ubcfCreateUser();
    $upperNeighbor = ubcfCreateUser();
    $upperA = ubcfCreateMobil();
    $upperB = ubcfCreateMobil();
    $upperC = ubcfCreateMobil();

    ubcfCreateRating($upperTarget, $upperA, 5);
    ubcfCreateRating($upperTarget, $upperB, 5);
    ubcfCreateRating($upperNeighbor, $upperA, 1);
    ubcfCreateRating($upperNeighbor, $upperB, 1);
    ubcfCreateRating($upperNeighbor, $upperC, 5);

    $upperRecommendations = app(UserBasedRecommendationService::class)
        ->recommendForUser($upperTarget->id, 5, 2);
    $upperCandidate = collect($upperRecommendations)->firstWhere('mobil_id', $upperC->id);

    expect($upperCandidate)->not->toBeNull()
        ->and($upperCandidate['predicted_rating'])->toBe(5.0);

    $lowerTarget = ubcfCreateUser();
    $lowerNeighbor = ubcfCreateUser();
    $lowerA = ubcfCreateMobil();
    $lowerB = ubcfCreateMobil();
    $lowerC = ubcfCreateMobil();

    ubcfCreateRating($lowerTarget, $lowerA, 1);
    ubcfCreateRating($lowerTarget, $lowerB, 1);
    ubcfCreateRating($lowerNeighbor, $lowerA, 5);
    ubcfCreateRating($lowerNeighbor, $lowerB, 5);
    ubcfCreateRating($lowerNeighbor, $lowerC, 1);

    $lowerRecommendations = app(UserBasedRecommendationService::class)
        ->recommendForUser($lowerTarget->id, 5, 2);
    $lowerCandidate = collect($lowerRecommendations)->firstWhere('mobil_id', $lowerC->id);

    expect($lowerCandidate)->not->toBeNull()
        ->and($lowerCandidate['predicted_rating'])->toBe(1.0);
});

test('duplicate user and mobil ratings are averaged before recommendation', function () {
    $target = ubcfCreateUser();
    $neighbor = ubcfCreateUser();
    $carA = ubcfCreateMobil();
    $carB = ubcfCreateMobil();
    $carC = ubcfCreateMobil();

    ubcfCreateRating($target, $carA, 3);
    ubcfCreateRating($target, $carA, 5);
    ubcfCreateRating($target, $carB, 4);
    ubcfCreateRating($neighbor, $carA, 4);
    ubcfCreateRating($neighbor, $carB, 4);
    ubcfCreateRating($neighbor, $carC, 5);

    $service = app(UserBasedRecommendationService::class);
    $rows = $service->normalizeValidRatings([
        ['user_id' => $target->id, 'mobil_id' => $carA->id, 'rating' => 3, 'reservasi_status' => 'selesai', 'reservasi_user_id' => $target->id, 'reservasi_mobil_id' => $carA->id],
        ['user_id' => $target->id, 'mobil_id' => $carA->id, 'rating' => 5, 'reservasi_status' => 'selesai', 'reservasi_user_id' => $target->id, 'reservasi_mobil_id' => $carA->id],
        ['user_id' => $target->id, 'mobil_id' => $carB->id, 'rating' => 4, 'reservasi_status' => 'selesai', 'reservasi_user_id' => $target->id, 'reservasi_mobil_id' => $carB->id],
    ]);

    $matrix = $service->buildMatrix($rows);

    expect($matrix[$target->id][$carA->id])->toBe(4.0)
        ->and($service->meanRating($target->id, $matrix))->toBe(4.0)
        ->and(collect($service->recommendForUser($target->id))->pluck('mobil_id'))->toContain($carC->id);
});
