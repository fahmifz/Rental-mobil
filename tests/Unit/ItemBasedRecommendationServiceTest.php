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

it('calculates sparse cosine manually with all vector dimensions including zero gaps', function () {
    $service = new ItemBasedRecommendationService;

    $vectorA = [1 => 4, 4 => 2];
    $vectorB = [1 => 4, 2 => 5, 3 => 1];

    $expectedDotProduct = (4 * 4) + (2 * 0) + (5 * 0) + (1 * 0);
    $expectedNormA = sqrt((4 ** 2) + (2 ** 2));
    $expectedNormB = sqrt((4 ** 2) + (5 ** 2) + (1 ** 2));
    $expectedSimilarity = $expectedDotProduct / ($expectedNormA * $expectedNormB);

    expect($expectedDotProduct)->toBe(16)
        ->and(abs($service->cosineSimilarity($vectorA, $vectorB) - $expectedSimilarity))->toBeLessThan(0.000001)
        ->and($service->cosineSimilarity($vectorA, $vectorB))->toBe($service->cosineSimilarity($vectorB, $vectorA));
});

it('returns zero cosine for vectors without shared dimensions or zero norms', function () {
    $service = new ItemBasedRecommendationService;

    expect($service->cosineSimilarity([1 => 5], [2 => 4]))->toBe(0.0)
        ->and($service->cosineSimilarity([1 => 0, 2 => 0], [1 => 0, 2 => 0]))->toBe(0.0)
        ->and($service->cosineSimilarity([], [1 => 3]))->toBe(0.0);
});

it('calculates item based prediction manually without mean centering', function () {
    $service = new ItemBasedRecommendationService;

    $prediction = $service->predictRating(
        [1 => 5, 2 => 1],
        [
            ['mobil_id' => 1, 'similarity' => 0.8, 'rating' => 999],
            ['mobil_id' => 2, 'similarity' => -0.2, 'rating' => 999],
        ]
    );

    $expected = ((0.8 * 5) + ((-0.2) * 1)) / (abs(0.8) + abs(-0.2));

    expect(abs($prediction - 3.8))->toBeLessThan(0.000001)
        ->and(abs($prediction - $expected))->toBeLessThan(0.000001);
});

it('returns null when prediction denominator is zero', function () {
    $service = new ItemBasedRecommendationService;

    expect($service->predictRating([1 => 5], [
        ['mobil_id' => 1, 'similarity' => 0.0, 'rating' => 5],
    ]))->toBeNull();
});

it('orders candidates by predicted rating and uses mobil ID for ties', function () {
    $service = new ItemBasedRecommendationService;

    $matrix = [
        10 => [1 => 5, 2 => 1],
        20 => [1 => 5, 2 => 1, 3 => 5, 4 => 5],
        30 => [1 => 5, 2 => 1, 3 => 5, 4 => 5],
    ];

    $recommendations = $service->recommend(10, $matrix, 5);

    expect(array_column($recommendations, 'mobil_id'))->toBe([3, 4]);
    expect(abs($recommendations[0]['predicted_rating'] - 3.0))->toBeLessThan(0.000001);
    expect(abs($recommendations[1]['predicted_rating'] - 3.0))->toBeLessThan(0.000001);
});

it('selects nearest neighbors before applying K and ranking candidates', function () {
    $service = new ItemBasedRecommendationService;

    $matrix = [
        10 => [1 => 5, 2 => 1],
        20 => [1 => 5, 2 => 1, 3 => 5, 4 => 1],
        30 => [1 => 5, 2 => 1, 3 => 1, 4 => 5],
    ];

    $oneNeighbor = $service->recommend(10, $matrix, 1);
    $fiveNeighbors = $service->recommend(10, $matrix, 5);

    expect($oneNeighbor)->toBeArray()->not->toBeEmpty();

    foreach ($oneNeighbor as $recommendation) {
        expect($recommendation['neighbor_count'])->toBe(1);
    }

    foreach ($fiveNeighbors as $recommendation) {
        expect($recommendation['neighbor_count'])->toBeLessThanOrEqual(2);
    }

    expect(array_column($oneNeighbor, 'mobil_id'))->toBe([3, 4]);
});

it('does not recommend items rated by the target user', function () {
    $service = new ItemBasedRecommendationService;

    $matrix = [
        10 => [1 => 5, 2 => 4],
        20 => [1 => 4, 2 => 3, 3 => 5],
        30 => [1 => 2, 2 => 5, 3 => 4],
    ];

    $ids = array_column($service->recommend(10, $matrix, 5), 'mobil_id');

    expect($ids)->toContain(3)
        ->and($ids)->not->toContain(1)
        ->and($ids)->not->toContain(2);
});

it('averages duplicate user and mobil entries in buildMatrix', function () {
    $service = new ItemBasedRecommendationService;

    $matrix = $service->buildMatrix([
        ['user_id' => 1, 'mobil_id' => 10, 'rating' => 3],
        ['user_id' => 1, 'mobil_id' => 10, 'rating' => 5],
        ['user_id' => 2, 'mobil_id' => 10, 'rating' => 4],
        ['user_id' => 1, 'mobil_id' => 11, 'rating' => 2],
    ]);

    expect($matrix)->toBe([
        1 => [10 => 4.0, 11 => 2.0],
        2 => [10 => 4.0],
    ]);
});

it('normalizes database compatible integer strings and rejects other invalid values', function () {
    $service = new ItemBasedRecommendationService;

    $validRows = $service->normalizeValidRatings([
        [
            'user_id' => 1,
            'mobil_id' => 10,
            'rating' => '5',
            'reservasi_status' => 'selesai',
            'reservasi_user_id' => '1',
            'reservasi_mobil_id' => '10',
        ],
        [
            'user_id' => 1,
            'mobil_id' => 11,
            'rating' => 4.0,
            'reservasi_status' => 'selesai',
            'reservasi_user_id' => 1,
            'reservasi_mobil_id' => 11,
        ],
        [
            'user_id' => 1,
            'mobil_id' => 12,
            'rating' => '2.5',
            'reservasi_status' => 'selesai',
            'reservasi_user_id' => 1,
            'reservasi_mobil_id' => 12,
        ],
    ]);

    expect($validRows)->toBe([
        ['user_id' => 1, 'mobil_id' => 10, 'rating' => 5],
    ]);
});

it('calculates cosine manually for the isolated three car synthetic matrix', function () {
    $service = new ItemBasedRecommendationService;

    $matrix = [
        1 => ['A' => 5, 'B' => 4],
        2 => ['A' => 5, 'C' => 3],
        3 => ['B' => 4, 'C' => 5],
        4 => ['A' => 4, 'B' => 5],
    ];

    $itemVectors = [
        'A' => [1 => 5, 2 => 5, 4 => 4],
        'B' => [1 => 4, 3 => 4, 4 => 5],
        'C' => [2 => 3, 3 => 5],
    ];

    $similarityAB = 40 / (sqrt(66) * sqrt(57));
    $similarityAC = 15 / (sqrt(66) * sqrt(34));
    $similarityBC = 20 / (sqrt(57) * sqrt(34));

    expect(abs($service->cosineSimilarity($itemVectors['A'], $itemVectors['B']) - $similarityAB))
        ->toBeLessThan(0.000001)
        ->and(abs($service->cosineSimilarity($itemVectors['A'], $itemVectors['C']) - $similarityAC))
        ->toBeLessThan(0.000001)
        ->and(abs($service->cosineSimilarity($itemVectors['B'], $itemVectors['C']) - $similarityBC))
        ->toBeLessThan(0.000001)
        ->and($service->cosineSimilarity($itemVectors['A'], $itemVectors['B']))
        ->toBe($service->cosineSimilarity($itemVectors['B'], $itemVectors['A']))
        ->and($matrix)->toBeArray();
});

it('predicts the missing synthetic rating without mean centering and excludes rated cars', function () {
    $service = new ItemBasedRecommendationService(5);

    $matrix = [
        1 => [1 => 5, 2 => 4],
        2 => [1 => 5, 3 => 3],
        3 => [2 => 4, 3 => 5],
        4 => [1 => 4, 2 => 5],
    ];

    $similarityAC = 15 / (sqrt(66) * sqrt(34));
    $similarityBC = 20 / (sqrt(57) * sqrt(34));
    $manualPrediction = ((5 * $similarityAC) + (4 * $similarityBC))
        / (abs($similarityAC) + abs($similarityBC));

    $recommendations = $service->recommend(1, $matrix, 5);

    expect($recommendations)->toHaveCount(1)
        ->and(array_column($recommendations, 'mobil_id'))->toBe([3])
        ->and(abs($recommendations[0]['predicted_rating'] - $manualPrediction))->toBeLessThan(0.000001)
        ->and($recommendations[0]['neighbor_count'])->toBe(2);
});

it('returns no synthetic recommendation when an item has no usable shared user', function () {
    $service = new ItemBasedRecommendationService;

    $matrix = [
        1 => [1 => 5],
        2 => [2 => 4],
    ];

    expect($service->recommend(1, $matrix, 5))->toBe([]);
});
