<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\AnalysisRepositoryInterface;
use App\Contracts\Services\AIIntegrationServiceInterface;
use App\Contracts\Services\AnalysisServiceInterface;
use App\DTOs\AnalysisDTO;
use App\Enums\AnalysisStatus;
use App\Exceptions\AnalysisNotFoundException;
use App\Models\Analysis;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalysisService implements AnalysisServiceInterface
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analysisRepository,
        private readonly AIIntegrationServiceInterface $aiService,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function createAnalysis(AnalysisDTO $dto): Analysis
    {
        return DB::transaction(function () use ($dto) {
            // 1. Create analysis record with pending status
            $analysis = $this->analysisRepository->create([
                'user_id' => $dto->userId,
                'image_path' => $dto->imagePath,
                'original_filename' => $dto->originalFilename,
                'status' => AnalysisStatus::PENDING,
            ]);

            // 2. Update status to processing
            $this->analysisRepository->update($analysis, [
                'status' => AnalysisStatus::PROCESSING,
            ]);

            try {
                // 3. Send image to AI service for prediction
                $result = $this->aiService->predict($dto->imagePath);

                // 4. Update analysis with prediction result
                $analysis = $this->analysisRepository->update($analysis, [
                    'prediction' => $result->prediction,
                    'confidence' => $result->confidence,
                    'status' => AnalysisStatus::COMPLETED,
                    'ai_response_payload' => $result->rawPayload,
                    'processed_at' => now(),
                ]);

                // 5. Generate report
                $this->generateReport($analysis, $result->prediction, $result->confidence);

            } catch (\Throwable $e) {
                Log::error('AI prediction failed', [
                    'analysis_id' => $analysis->id,
                    'error' => $e->getMessage(),
                ]);

                $analysis = $this->analysisRepository->update($analysis, [
                    'status' => AnalysisStatus::FAILED,
                    'ai_response_payload' => ['error' => $e->getMessage()],
                ]);
            }

            return $analysis->load('report');
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getAnalysis(int $id, int $userId): Analysis
    {
        $analysis = $this->analysisRepository->findById($id);

        if (!$analysis || $analysis->user_id !== $userId) {
            throw new AnalysisNotFoundException();
        }

        return $analysis;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserAnalyses(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->analysisRepository->findByUser($userId, $perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAnalysis(int $id, int $userId): bool
    {
        $analysis = $this->getAnalysis($id, $userId);
        return $this->analysisRepository->delete($analysis);
    }

    /**
     * Generate a diagnostic report from the AI prediction.
     */
    private function generateReport(Analysis $analysis, string $prediction, float $confidence): void
    {
        $recommendations = $this->buildRecommendations($prediction, $confidence);

        Report::create([
            'analysis_id' => $analysis->id,
            'title' => "Kidney Analysis Report — {$prediction}",
            'summary' => $this->buildSummary($prediction, $confidence),
            'recommendations' => $recommendations,
            'metadata' => [
                'generated_at' => now()->toISOString(),
                'model_version' => $analysis->ai_response_payload['model_version'] ?? 'unknown',
            ],
        ]);
    }

    /**
     * Build a human-readable summary.
     */
    private function buildSummary(string $prediction, float $confidence): string
    {
        return "AI analysis detected: {$prediction} with {$confidence}% confidence. "
             . "This is an automated preliminary assessment and should be reviewed by a qualified medical professional.";
    }

    /**
     * Build recommendations based on the prediction.
     */
    private function buildRecommendations(string $prediction, float $confidence): array
    {
        $base = ['Consult with a nephrologist for professional medical advice.'];

        return match (strtolower($prediction)) {
            'normal' => array_merge($base, [
                'Continue regular check-ups.',
                'Maintain a healthy diet and hydration.',
            ]),
            'cyst' => array_merge($base, [
                'Schedule follow-up imaging in 6-12 months.',
                'Monitor for any symptoms such as pain or blood in urine.',
            ]),
            'tumor' => array_merge($base, [
                'Urgent: Schedule an appointment with an oncologist.',
                'Additional imaging (CT/MRI) recommended.',
                'Biopsy may be required for definitive diagnosis.',
            ]),
            'stone' => array_merge($base, [
                'Increase fluid intake significantly.',
                'Pain management may be necessary.',
                'Follow-up to assess stone size and position.',
            ]),
            default => $base,
        };
    }
}
