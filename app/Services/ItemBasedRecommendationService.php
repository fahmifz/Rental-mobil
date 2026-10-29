<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ItemBasedRecommendationService
{
    public function __construct(private int $neighborCount = 5)
    {
        if ($neighborCount < 1) {
            throw new InvalidArgumentException('Jumlah tetangga terdekat harus minimal 1.');
        }
    }

    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $shared = 0;

        foreach ($vectorA as $dimension => $valueA) {
            $valueA = (float) $valueA;
            $normA += $valueA * $valueA;

            if (array_key_exists($dimension, $vectorB)) {
                $valueB = (float) $vectorB[$dimension];
                $dotProduct += $valueA * $valueB;
                $shared++;
            }
        }

        foreach ($vectorB as $valueB) {
            $valueB = (float) $valueB;
            $normB += $valueB * $valueB;
        }

        if ($shared === 0 || $normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        $denominator = sqrt($normA) * sqrt($normB);

        return $denominator <= 0.0
            ? 0.0
            : $dotProduct / $denominator;
    }

    public function normalizeValidRatings(array $rows): array
    {
        $validRows = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $required = [
                'user_id',
                'mobil_id',
                'rating',
                'reservasi_status',
                'reservasi_user_id',
                'reservasi_mobil_id',
            ];

            foreach ($required as $key) {
                if (! array_key_exists($key, $row)) {
                    continue 2;
                }
            }

            if ($row['reservasi_status'] !== 'selesai') {
                continue;
            }

            if ((string) $row['user_id'] !== (string) $row['reservasi_user_id']) {
                continue;
            }

            if ((string) $row['mobil_id'] !== (string) $row['reservasi_mobil_id']) {
                continue;
            }

            if (! $this->isIntegerInRatingRange($row['rating'])) {
                continue;
            }

            $validRows[] = [
                'user_id' => (int) $row['user_id'],
                'mobil_id' => (int) $row['mobil_id'],
                'rating' => (int) $row['rating'],
            ];
        }

        return $validRows;
    }

    public function recommend(int $userId, array $matrix, ?int $neighborCount = null): array
    {
        if (! array_key_exists($userId, $matrix) || $matrix[$userId] === []) {
            return [];
        }

        $userRatings = $this->integerOrAverageMap($matrix[$userId]);
        if ($userRatings === []) {
            return [];
        }

        $ratedItemIds = array_keys($userRatings);
        $itemVectors = $this->buildItemVectors($matrix);
        $k = $neighborCount ?? $this->neighborCount;

        if ($k < 1) {
            return [];
        }

        $recommendations = [];

        foreach (array_keys($itemVectors) as $candidateItemId) {
            $candidateItemId = (int) $candidateItemId;

            if (isset($userRatings[$candidateItemId])) {
                continue;
            }

            $neighbors = [];

            foreach ($ratedItemIds as $ratedItemId) {
                if (! isset($itemVectors[$ratedItemId])) {
                    continue;
                }

                $similarity = $this->cosineSimilarity(
                    $itemVectors[$candidateItemId],
                    $itemVectors[$ratedItemId]
                );

                if ($similarity != 0.0) {
                    $neighbors[] = [
                        'mobil_id' => $ratedItemId,
                        'similarity' => $similarity,
                        'rating' => $userRatings[$ratedItemId],
                    ];
                }
            }

            usort($neighbors, function (array $left, array $right): int {
                $similarityComparison = $right['similarity'] <=> $left['similarity'];

                return $similarityComparison !== 0
                    ? $similarityComparison
                    : ($left['mobil_id'] <=> $right['mobil_id']);
            });

            $neighbors = array_slice($neighbors, 0, $k);
            $prediction = $this->predictRating($userRatings, $neighbors);

            if ($prediction !== null) {
                $recommendations[] = [
                    'mobil_id' => $candidateItemId,
                    'predicted_rating' => $prediction,
                    'neighbor_count' => count($neighbors),
                ];
            }
        }

        usort($recommendations, function (array $left, array $right): int {
            $scoreComparison = $right['predicted_rating'] <=> $left['predicted_rating'];

            return $scoreComparison !== 0
                ? $scoreComparison
                : ($left['mobil_id'] <=> $right['mobil_id']);
        });

        return $recommendations;
    }

    public function recommendForUser(int $userId, ?int $neighborCount = null): array
    {
        $rows = DB::table('ratings')
            ->join('reservasis', 'reservasis.id', '=', 'ratings.reservasi_id')
            ->select([
                'ratings.user_id as user_id',
                'ratings.mobil_id as mobil_id',
                'ratings.rating as rating',
                'reservasis.status as reservasi_status',
                'reservasis.user_id as reservasi_user_id',
                'reservasis.mobil_id as reservasi_mobil_id',
            ])
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->all();

        $validRatings = $this->normalizeValidRatings($rows);
        if ($validRatings === []) {
            return [];
        }

        $matrix = $this->buildMatrix($validRatings);

        return $this->recommend($userId, $matrix, $neighborCount);
    }

    public function predictRating(array $userRatings, array $neighbors): ?float
    {
        $weightedSum = 0.0;
        $similarityAbsoluteSum = 0.0;

        foreach ($neighbors as $neighbor) {
            if (! is_array($neighbor)
                || ! array_key_exists('mobil_id', $neighbor)
                || ! array_key_exists('similarity', $neighbor)
            ) {
                continue;
            }

            $mobilId = (int) $neighbor['mobil_id'];
            if (! array_key_exists($mobilId, $userRatings)) {
                continue;
            }

            $similarity = (float) $neighbor['similarity'];
            $rating = (float) $userRatings[$mobilId];

            $weightedSum += $similarity * $rating;
            $similarityAbsoluteSum += abs($similarity);
        }

        if ($similarityAbsoluteSum <= 0.0) {
            return null;
        }

        return $weightedSum / $similarityAbsoluteSum;
    }

    public function buildMatrix(array $validRatings): array
    {
        $totals = [];
        $counts = [];

        foreach ($validRatings as $row) {
            if (! is_array($row)
                || ! isset($row['user_id'], $row['mobil_id'], $row['rating'])
            ) {
                continue;
            }

            $userId = (int) $row['user_id'];
            $mobilId = (int) $row['mobil_id'];

            if (! $this->isIntegerInRatingRange($row['rating'])) {
                continue;
            }

            $totals[$userId][$mobilId] = ($totals[$userId][$mobilId] ?? 0.0)
                + (float) $row['rating'];
            $counts[$userId][$mobilId] = ($counts[$userId][$mobilId] ?? 0) + 1;
        }

        $matrix = [];
        foreach ($totals as $userId => $items) {
            foreach ($items as $mobilId => $total) {
                $matrix[$userId][$mobilId] = $total / $counts[$userId][$mobilId];
            }
        }

        return $matrix;
    }

    private function buildItemVectors(array $matrix): array
    {
        $itemVectors = [];

        foreach ($matrix as $userId => $items) {
            foreach ($items as $mobilId => $rating) {
                $itemVectors[$mobilId][(int) $userId] = (float) $rating;
            }
        }

        return $itemVectors;
    }

    private function integerOrAverageMap(array $items): array
    {
        $result = [];

        foreach ($items as $mobilId => $rating) {
            if (! is_numeric($rating) || (float) $rating < 1.0 || (float) $rating > 5.0) {
                continue;
            }

            $value = (float) $rating;
            $result[(int) $mobilId] = abs($value - round($value)) < 0.0000001
                ? (int) round($value)
                : $value;
        }

        return $result;
    }

    private function isIntegerInRatingRange(mixed $rating): bool
    {
        if (is_int($rating)) {
            return $rating >= 1 && $rating <= 5;
        }

        return is_string($rating) && preg_match('/^[1-5]$/', $rating) === 1;
    }
}
