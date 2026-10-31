<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserBasedRecommendationService
{
    public function __construct(
        private int $neighborCount = 5,
        private int $minimumSharedItems = 2
    ) {
        if ($neighborCount < 1) {
            throw new InvalidArgumentException('Jumlah tetangga terdekat harus minimal 1.');
        }

        if ($minimumSharedItems < 1) {
            throw new InvalidArgumentException('Jumlah mobil bersama minimum harus minimal 1.');
        }
    }

    public function cosineSimilarity(
        array $vectorA,
        array $vectorB,
        ?int $minimumSharedItems = null
    ): float {
        $minimumSharedItems ??= $this->minimumSharedItems;

        if ($minimumSharedItems < 1) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $shared = 0;

        foreach ($vectorA as $valueA) {
            $valueA = (float) $valueA;
            $normA += $valueA * $valueA;
        }

        foreach ($vectorB as $valueB) {
            $valueB = (float) $valueB;
            $normB += $valueB * $valueB;
        }

        foreach ($vectorA as $dimension => $valueA) {
            if (array_key_exists($dimension, $vectorB)) {
                $dotProduct += (float) $valueA * (float) $vectorB[$dimension];
                $shared++;
            }
        }

        if ($shared < $minimumSharedItems || $normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        $denominator = sqrt($normA) * sqrt($normB);
        if ($denominator <= 0.0) {
            return 0.0;
        }

        $similarity = $dotProduct / $denominator;

        return $similarity > 0.0 ? $similarity : 0.0;
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

    public function recommend(
        int $userId,
        array $matrix,
        ?int $neighborCount = null,
        ?int $minimumSharedItems = null
    ): array {
        $k = $neighborCount ?? $this->neighborCount;
        $minimumSharedItems ??= $this->minimumSharedItems;

        if ($k < 1 || $minimumSharedItems < 1) {
            return [];
        }

        if (! array_key_exists($userId, $matrix) || $matrix[$userId] === []) {
            return [];
        }

        $targetRatings = $this->ratingMap($matrix[$userId]);
        if ($targetRatings === []) {
            return [];
        }

        $targetMean = $this->meanRating($userId, $matrix);
        if ($targetMean === null) {
            return [];
        }

        $similarNeighbors = [];

        foreach ($matrix as $otherUserId => $otherRatings) {
            $otherUserId = (int) $otherUserId;
            if ($otherUserId === $userId) {
                continue;
            }

            $otherRatings = $this->ratingMap($otherRatings);
            if ($otherRatings === []) {
                continue;
            }

            $similarity = $this->cosineSimilarity(
                $targetRatings,
                $otherRatings,
                $minimumSharedItems
            );

            if ($similarity <= 0.0) {
                continue;
            }

            $similarNeighbors[] = [
                'user_id' => $otherUserId,
                'similarity' => $similarity,
            ];
        }

        if ($similarNeighbors === []) {
            return [];
        }

        $candidateIds = [];

        foreach ($similarNeighbors as $neighbor) {
            $neighborRatings = $this->ratingMap($matrix[$neighbor['user_id']]);

            foreach (array_keys($neighborRatings) as $mobilId) {
                $mobilId = (int) $mobilId;
                if (! isset($targetRatings[$mobilId])) {
                    $candidateIds[$mobilId] = true;
                }
            }
        }

        if ($candidateIds === []) {
            return [];
        }

        $recommendations = [];

        foreach (array_keys($candidateIds) as $candidateItemId) {
            $candidateItemId = (int) $candidateItemId;
            $candidateNeighbors = [];

            foreach ($similarNeighbors as $neighbor) {
                $neighborRatings = $this->ratingMap($matrix[$neighbor['user_id']]);
                if (! array_key_exists($candidateItemId, $neighborRatings)) {
                    continue;
                }

                $neighborMean = $this->meanRating($neighbor['user_id'], $matrix);
                if ($neighborMean === null) {
                    continue;
                }

                $candidateNeighbors[] = [
                    'user_id' => $neighbor['user_id'],
                    'similarity' => $neighbor['similarity'],
                    'rating' => $neighborRatings[$candidateItemId],
                    'mean_rating' => $neighborMean,
                ];
            }

            usort($candidateNeighbors, function (array $left, array $right): int {
                $similarityComparison = $right['similarity'] <=> $left['similarity'];

                return $similarityComparison !== 0
                    ? $similarityComparison
                    : ($left['user_id'] <=> $right['user_id']);
            });

            $candidateNeighbors = array_slice($candidateNeighbors, 0, $k);
            $prediction = $this->predictRating($targetMean, $candidateNeighbors);

            if ($prediction !== null) {
                $recommendations[] = [
                    'mobil_id' => $candidateItemId,
                    'predicted_rating' => $prediction,
                    'neighbor_count' => count($candidateNeighbors),
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

    public function recommendForUser(
        int $userId,
        ?int $neighborCount = null,
        ?int $minimumSharedItems = null
    ): array {
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

        return $this->recommend(
            $userId,
            $matrix,
            $neighborCount,
            $minimumSharedItems
        );
    }

    public function predictRating(float $targetMean, array $neighbors): ?float
    {
        $weightedDeviation = 0.0;
        $similarityAbsoluteSum = 0.0;

        foreach ($neighbors as $neighbor) {
            if (! is_array($neighbor)
                || ! array_key_exists('similarity', $neighbor)
                || ! array_key_exists('rating', $neighbor)
                || ! array_key_exists('mean_rating', $neighbor)
            ) {
                continue;
            }

            $similarity = (float) $neighbor['similarity'];
            if ($similarity <= 0.0) {
                continue;
            }

            $rating = (float) $neighbor['rating'];
            $neighborMean = (float) $neighbor['mean_rating'];

            $weightedDeviation += $similarity * ($rating - $neighborMean);
            $similarityAbsoluteSum += abs($similarity);
        }

        if ($similarityAbsoluteSum <= 0.0) {
            return null;
        }

        $prediction = $targetMean + ($weightedDeviation / $similarityAbsoluteSum);

        return max(1.0, min(5.0, $prediction));
    }

    public function meanRating(int $userId, array $matrix): ?float
    {
        if (! array_key_exists($userId, $matrix) || $matrix[$userId] === []) {
            return null;
        }

        $ratings = $this->ratingMap($matrix[$userId]);
        if ($ratings === []) {
            return null;
        }

        return array_sum($ratings) / count($ratings);
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

    private function ratingMap(array $items): array
    {
        $result = [];

        foreach ($items as $mobilId => $rating) {
            if (! is_numeric($rating)) {
                continue;
            }

            $value = (float) $rating;
            if ($value < 1.0 || $value > 5.0) {
                continue;
            }

            $result[(int) $mobilId] = $value;
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
