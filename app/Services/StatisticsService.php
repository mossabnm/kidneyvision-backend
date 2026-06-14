<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\AnalysisRepositoryInterface;
use App\Contracts\Services\StatisticsServiceInterface;

class StatisticsService implements StatisticsServiceInterface
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analysisRepository,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getUserStatistics(int $userId): array
    {
        $stats = $this->analysisRepository->getStatsByUser($userId);
        $recent = $this->analysisRepository->getRecentByUser($userId, 5);

        return [
            'total_analyses' => $stats['total'],
            'completed' => $stats['completed'],
            'pending' => $stats['pending'],
            'processing' => $stats['processing'],
            'failed' => $stats['failed'],
            'success_rate' => $stats['total'] > 0
                ? round(($stats['completed'] / $stats['total']) * 100, 2)
                : 0,
            'average_confidence' => $stats['average_confidence'] ? round((float) $stats['average_confidence'], 2) : null,
            'condition_distribution' => $stats['condition_distribution'],
            'recent_analyses' => $recent,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobalStatistics(): array
    {
        return $this->analysisRepository->getGlobalStats();
    }
}
