<?php

use App\Services\UserBasedRecommendationService;

it('calculates user cosine manually with shared dimensions and full-user norms', function () {
    $service = new UserBasedRecommendationService;

    $userA = [1 => 4, 2 => 4];
    $userB = [1 => 2, 2 => 5, 3 => 5];

    $dotProduct = 4 * 2 + 4 * 5;
    $normA = sqrt(4 ** 2 + 4 ** 2);
    $normB = sqrt(2 ** 2 + 5 ** 2 + 5 ** 2);
    $expected = $dotProduct / ($normA * $normB);

    expect(abs($service->cosineSimilarity($userA, $userB, 2) - $expected))
        ->toBeLessThan(0.000001)
        ->and($service->cosineSimilarity($userA, $userB, 2))
        ->toBe($service->cosineSimilarity($userB, $userA, 2));
});

it('requires two shared items by default and returns zero for no shared dimensions or zero norms', function () {
    $service = new UserBasedRecommendationService;

    expect($service->cosineSimilarity([1 => 5, 2 => 4], [2 => 4, 3 => 5], 2))->toBe(0.0)
        ->and($service->cosineSimilarity([1 => 5], [1 => 2, 2 => 4], 2))->toBe(0.0)
        ->and($service->cosineSimilarity([1 => 0, 2 => 0], [1 => 0, 2 => 0], 2))->toBe(0.0)
        ->and($service->cosineSimilarity([], [1 => 5], 2))->toBe(0.0)
        ->and($service->cosineSimilarity([1 => 1, 2 => 1], [1 => -1, 2 => -1], 2))->toBe(0.0);
});

it('does not treat missing ratings as zero values in option b cosine', function () {
    $service = new UserBasedRecommendationService;

    $userA = [1 => 5, 2 => 4];
    $userB = [1 => 4, 2 => 4, 3 => 5];

    $dot = 5 * 4 + 4 * 4;
    $expected = $dot / (sqrt(5 ** 2 + 4 ** 2) * sqrt(4 ** 2 + 4 ** 2 + 5 ** 2));

    expect(abs($service->cosineSimilarity($userA, $userB, 2) - $expected))
        ->toBeLessThan(0.000001);
});

it('predicts with mean centering and includes zero deviation in denominator', function () {
    $service = new UserBasedRecommendationService;

    $prediction = $service->predictRating(4.0, [
        ['user_id' => 3, 'similarity' => 0.8164965809277260, 'rating' => 1, 'mean_rating' => 1.0],
        ['user_id' => 2, 'similarity' => 0.6735753147432708, 'rating' => 5, 'mean_rating' => 4.0],
    ]);

    $weightedDeviation = (0.8164965809277260 * (1 - 1))
        + (0.6735753147432708 * (5 - 4));
    $absoluteSimilarity = 0.8164965809277260 + 0.6735753147432708;
    $expected = 4.0 + ($weightedDeviation / $absoluteSimilarity);

    expect(abs($prediction - 4.452042))->toBeLessThan(0.000001)
        ->and(abs($prediction - $expected))->toBeLessThan(0.000001);
});

it('includes a zero-deviation neighbor in neighbor count and denominator', function () {
    $service = new UserBasedRecommendationService;

    $prediction = $service->predictRating(4.0, [
        ['user_id' => 3, 'similarity' => 0.816496580927726, 'rating' => 1, 'mean_rating' => 1.0],
        ['user_id' => 2, 'similarity' => 0.6735753147432708, 'rating' => 5, 'mean_rating' => 4.0],
    ]);

    expect($prediction)->not->toBeNull()
        ->and(abs($prediction - 4.452042))->toBeLessThan(0.000001);

    $matrix = [
        1 => [1 => 4, 2 => 4],
        2 => [1 => 2, 2 => 5, 3 => 5],
        3 => [1 => 1, 2 => 1, 3 => 1],
    ];

    $recommendations = $service->recommend(1, $matrix, 2, 2);
    $candidate = collect($recommendations)->firstWhere('mobil_id', 3);

    expect($candidate)->not->toBeNull()
        ->and($candidate['neighbor_count'])->toBe(2);
});

it('clamps predictions below one and above five', function () {
    $service = new UserBasedRecommendationService;

    expect($service->predictRating(4.9, [
        ['user_id' => 2, 'similarity' => 0.75, 'rating' => 5, 'mean_rating' => 1.0],
    ]))->toBe(5.0)
        ->and($service->predictRating(2.0, [
            ['user_id' => 2, 'similarity' => 0.60, 'rating' => 1, 'mean_rating' => 5.0],
        ]))->toBe(1.0);
});

it('returns null for zero denominator', function () {
    $service = new UserBasedRecommendationService;

    expect($service->predictRating(4.0, []))->toBeNull()
        ->and($service->predictRating(4.0, [
            ['user_id' => 2, 'similarity' => 0.0, 'rating' => 5, 'mean_rating' => 4.0],
        ]))->toBeNull();
});

it('normalizes valid ratings and rejects invalid reservation rows', function () {
    $service = new UserBasedRecommendationService;

    $valid = $service->normalizeValidRatings([
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
    ]);

    expect($valid)->toBe([
        ['user_id' => 1, 'mobil_id' => 10, 'rating' => 5],
    ]);
});

it('averages duplicate user mobil ratings and averages means from the aggregate matrix', function () {
    $service = new UserBasedRecommendationService;

    $matrix = $service->buildMatrix([
        ['user_id' => 1, 'mobil_id' => 10, 'rating' => 3],
        ['user_id' => 1, 'mobil_id' => 10, 'rating' => 5],
        ['user_id' => 1, 'mobil_id' => 11, 'rating' => 2],
    ]);

    expect($matrix)->toBe([
        1 => [10 => 4.0, 11 => 2.0],
    ])->and($service->meanRating(1, $matrix))->toBe(3.0);
});

it('does not recommend cars already rated by the target user', function () {
    $service = new UserBasedRecommendationService;

    $matrix = [
        1 => [1 => 5, 2 => 4],
        2 => [1 => 5, 2 => 4, 3 => 5],
        3 => [1 => 4, 2 => 3, 3 => 4],
    ];

    $ids = array_column($service->recommend(1, $matrix, 5, 2), 'mobil_id');

    expect($ids)->toContain(3)
        ->and($ids)->not->toContain(1)
        ->and($ids)->not->toContain(2);
});

it('selects neighbors per candidate and applies K with user ID ties', function () {
    $service = new UserBasedRecommendationService(5, 2);

    $matrix = [
        1 => [1 => 4, 2 => 4],
        2 => [1 => 2, 2 => 5, 3 => 5],
        3 => [1 => 1, 2 => 1, 3 => 1],
        4 => [1 => 5, 2 => 5, 3 => 5],
    ];

    $one = $service->recommend(1, $matrix, 1, 2);
    $two = $service->recommend(1, $matrix, 2, 2);

    $oneCandidate = collect($one)->firstWhere('mobil_id', 3);
    $twoCandidate = collect($two)->firstWhere('mobil_id', 3);

    expect($oneCandidate['neighbor_count'])->toBe(1)
        ->and($twoCandidate['neighbor_count'])->toBe(2);
});

it('orders recommendations by full precision and uses mobil ID for ties', function () {
    $service = new UserBasedRecommendationService;

    $matrix = [
        1 => [1 => 4, 2 => 4],
        2 => [1 => 2, 2 => 5, 3 => 5, 4 => 5],
        3 => [1 => 1, 2 => 1, 3 => 5, 4 => 5],
    ];

    $recommendations = $service->recommend(1, $matrix, 5, 2);

    expect(array_column($recommendations, 'mobil_id'))->toBe([3, 4]);
});

it('returns empty for new or unrated users, low shared items, K below one, and no neighbors', function () {
    $service = new UserBasedRecommendationService;

    expect($service->recommend(99, [], 5, 2))->toBe([])
        ->and($service->recommend(1, [1 => []], 5, 2))->toBe([])
        ->and($service->recommend(1, [1 => [1 => 5]], 0, 2))->toBe([])
        ->and($service->recommend(1, [1 => [1 => 5], 2 => [2 => 4]], 5, 2))->toBe([]);
});

it('verifies the complete four-user cosine and recommendation dataset mathematically', function () {
    $service = new UserBasedRecommendationService;

    $matrix = [
        1 => [1 => 4, 2 => 4],
        2 => [1 => 2, 2 => 5, 3 => 5],
        3 => [1 => 1, 2 => 1, 3 => 1],
        4 => [4 => 3],
    ];

    $dot12 = (4 * 2) + (4 * 5);
    $cosine12 = $dot12 / (
        sqrt((4 ** 2) + (4 ** 2))
        * sqrt((2 ** 2) + (5 ** 2) + (5 ** 2))
    );

    $dot13 = (4 * 1) + (4 * 1);
    $cosine13 = $dot13 / (
        sqrt((4 ** 2) + (4 ** 2))
        * sqrt((1 ** 2) + (1 ** 2) + (1 ** 2))
    );

    expect(abs($service->cosineSimilarity($matrix[1], $matrix[2], 2) - 0.673575314054563))
        ->toBeLessThan(0.000001)
        ->and(abs($service->cosineSimilarity($matrix[1], $matrix[3], 2) - 0.816496580927726))
        ->toBeLessThan(0.000001)
        ->and(abs($cosine12 - 0.673575314054563))->toBeLessThan(0.000001)
        ->and(abs($cosine13 - 0.816496580927726))->toBeLessThan(0.000001)
        ->and($service->cosineSimilarity($matrix[1], $matrix[4], 2))->toBe(0.0);

    $oneNeighbor = $service->recommend(1, $matrix, 1, 2);
    expect($oneNeighbor)->toHaveCount(1)
        ->and($oneNeighbor[0]['mobil_id'])->toBe(3)
        ->and(abs($oneNeighbor[0]['predicted_rating'] - 4.0))->toBeLessThan(0.000001)
        ->and($oneNeighbor[0]['neighbor_count'])->toBe(1);

    $targetMean = 4.0;
    $neighbor2Mean = (2 + 5 + 5) / 3;
    $neighbor3Mean = (1 + 1 + 1) / 3;

    $manualPrediction = $targetMean + (
        ($cosine12 * (5 - $neighbor2Mean))
        + ($cosine13 * (1 - $neighbor3Mean))
    ) / (abs($cosine12) + abs($cosine13));

    $twoNeighbors = $service->recommend(1, $matrix, 2, 2);
    expect($twoNeighbors)->toHaveCount(1)
        ->and($twoNeighbors[0]['mobil_id'])->toBe(3)
        ->and(abs($manualPrediction - 4.452042157376956))->toBeLessThan(0.000001)
        ->and(abs($twoNeighbors[0]['predicted_rating'] - 4.452042157376956))->toBeLessThan(0.000001)
        ->and($twoNeighbors[0]['neighbor_count'])->toBe(2);
});
