<?php

use App\Services\ItemBasedRecommendationService;

it('calculates cosine similarity for known vectors', function () {
    $service = new ItemBasedRecommendationService;

    $similarity = $service->cosineSimilarity([1, 1], [1, 0]);

    expect($similarity)->toBeFloat()->toBeGreaterThan(0.70710678 - 0.000001)
        ->toBeLessThan(0.70710678 + 0.000001);
});

it('returns zero cosine similarity for no shared users or zero norms', function () {
    $service = new ItemBasedRecommendationService;

    expect($service->cosineSimilarity([1 => 5], [2 => 4]))->toBe(0.0)
        ->and($service->cosineSimilarity([1 => 0], [1 => 0]))->toBe(0.0)
        ->and($service->cosineSimilarity([], [1 => 5]))->toBe(0.0);
});

it('predicts rating with the documented item based formula', function () {
    $service = new ItemBasedRecommendationService;

    $prediction = $service->predictRating(
        [1 => 5, 2 => 3],
        [
            ['mobil_id' => 1, 'similarity' => 1.0, 'rating' => 5],
            ['mobil_id' => 2, 'similarity' => 0.5, 'rating' => 3],
        ]
    );

    expect($prediction)->not->toBeNull()
        ->and(abs($prediction - (13 / 3)))->toBeLessThan(0.000001);
});

it('does not recommend cars already rated by the customer', function () {
    $service = new ItemBasedRecommendationService;

    $matrix = [
        10 => [1 => 5, 2 => 4],
        20 => [1 => 3, 2 => 5, 3 => 4],
        30 => [1 => 4, 3 => 5],
    ];

    $recommendations = $service->recommend(10, $matrix, 5);

    expect($recommendations)->toBeArray()
        ->and(array_column($recommendations, 'mobil_id'))->not->toContain(1, 2);
});

it('rejects invalid or inconsistent rating rows', function () {
    $service = new ItemBasedRecommendationService;

    $validRows = $service->normalizeValidRatings([
        [
            'user_id' => 1,
            'mobil_id' => 10,
            'rating' => 5,
            'reservasi_status' => 'selesai',
            'reservasi_user_id' => 1,
            'reservasi_mobil_id' => 10,
        ],
        [
            'user_id' => 1,
            'mobil_id' => 11,
            'rating' => 6,
            'reservasi_status' => 'selesai',
            'reservasi_user_id' => 1,
            'reservasi_mobil_id' => 11,
        ],
        [
            'user_id' => 1,
            'mobil_id' => 12,
            'rating' => 4,
            'reservasi_status' => 'disetujui',
            'reservasi_user_id' => 1,
            'reservasi_mobil_id' => 12,
        ],
        [
            'user_id' => 1,
            'mobil_id' => 13,
            'rating' => 4,
            'reservasi_status' => 'selesai',
            'reservasi_user_id' => 99,
            'reservasi_mobil_id' => 13,
        ],
        [
            'user_id' => 1,
            'mobil_id' => 14,
            'rating' => 4,
            'reservasi_status' => 'selesai',
            'reservasi_user_id' => 1,
            'reservasi_mobil_id' => 99,
        ],
        [
            'user_id' => 1,
            'mobil_id' => 15,
            'rating' => 2.5,
            'reservasi_status' => 'selesai',
            'reservasi_user_id' => 1,
            'reservasi_mobil_id' => 15,
        ],
    ]);

    expect($validRows)->toBe([
        ['user_id' => 1, 'mobil_id' => 10, 'rating' => 5],
    ]);
});

it('returns empty results for empty data and zero similarity division', function () {
    $service = new ItemBasedRecommendationService;

    expect($service->recommend(1, [], 5))->toBe([])
        ->and($service->predictRating([1 => 5], []))->toBeNull()
        ->and($service->predictRating([1 => 5], [[
            'mobil_id' => 1,
            'similarity' => 0.0,
            'rating' => 5,
        ]]))->toBeNull();
});

it('limits neighbors to the configured K', function () {
    $service = new ItemBasedRecommendationService(1);

    $matrix = [
        1 => [1 => 5, 2 => 4, 3 => 3],
        2 => [1 => 5, 2 => 1, 3 => 5, 4 => 1],
        3 => [1 => 1, 2 => 1, 3 => 1, 4 => 5],
    ];

    $recommendations = $service->recommend(1, $matrix);

    expect($recommendations)->toBeArray()->not->toBeEmpty();

    foreach ($recommendations as $recommendation) {
        expect($recommendation['neighbor_count'])->toBeLessThanOrEqual(1);
    }
});
