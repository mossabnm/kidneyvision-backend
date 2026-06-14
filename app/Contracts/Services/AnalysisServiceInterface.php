<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\AnalysisDTO;
use App\Http\Resources\AnalysisResource;
use App\Models\Analysis;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AnalysisServiceInterface
{
    /**
     * Create a new analysis and trigger AI prediction.
     */
    public function createAnalysis(AnalysisDTO $dto): Analysis;

    /**
     * Get a single analysis by ID for a specific user.
     */
    public function getAnalysis(int $id, int $userId): Analysis;

    /**
     * Get paginated analyses for a user.
     */
    public function getUserAnalyses(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Delete an analysis (soft delete).
     */
    public function deleteAnalysis(int $id, int $userId): bool;
}
