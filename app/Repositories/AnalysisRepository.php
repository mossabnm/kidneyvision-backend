<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\AnalysisRepositoryInterface;
use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AnalysisRepository implements AnalysisRepositoryInterface
{
    public function __construct(
        private readonly Analysis $model,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?Analysis
    {
        return $this->model
            ->with('report')
            ->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->forUser($userId)
            ->with('report')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Analysis
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(Analysis $analysis, array $data): Analysis
    {
        $analysis->update($data);
        return $analysis->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Analysis $analysis): bool
    {
        return (bool) $analysis->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatsByUser(int $userId): array
    {
        $analyses = $this->model->forUser($userId);

        return [
            'total' => $analyses->count(),
            'completed' => (clone $analyses)->where('status', AnalysisStatus::COMPLETED)->count(),
            'pending' => (clone $analyses)->where('status', AnalysisStatus::PENDING)->count(),
            'processing' => (clone $analyses)->where('status', AnalysisStatus::PROCESSING)->count(),
            'failed' => (clone $analyses)->where('status', AnalysisStatus::FAILED)->count(),
            'condition_distribution' => $this->getConditionDistribution($userId),
            'average_confidence' => (clone $analyses)->completed()->avg('confidence'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobalStats(): array
    {
        return [
            'total_analyses' => $this->model->count(),
            'total_completed' => $this->model->completed()->count(),
            'total_users' => $this->model->distinct('user_id')->count('user_id'),
            'average_confidence' => $this->model->completed()->avg('confidence'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRecentByUser(int $userId, int $limit = 5): Collection
    {
        return $this->model
            ->forUser($userId)
            ->with('report')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get condition distribution for a user.
     */
    private function getConditionDistribution(int $userId): array
    {
        return $this->model
            ->forUser($userId)
            ->completed()
            ->selectRaw('prediction, COUNT(*) as count')
            ->groupBy('prediction')
            ->pluck('count', 'prediction')
            ->toArray();
    }
}
