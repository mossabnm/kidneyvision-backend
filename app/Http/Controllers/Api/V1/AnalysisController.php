<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnalysisRequest;
use App\Http\Resources\AnalysisCollection;
use App\Http\Resources\AnalysisResource;
use App\Contracts\Services\AnalysisServiceInterface;
use App\DTOs\AnalysisDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnalysisController extends Controller
{
    public function __construct(
        private readonly AnalysisServiceInterface $analysisService,
    ) {}

    /**
     * Upload kidney image and trigger AI prediction.
     *
     * POST /api/predict
     */
    public function predict(StoreAnalysisRequest $request): JsonResponse
    {
        $storedPath = $request->file('image')->store('analyses', 'public');

        $dto = AnalysisDTO::fromRequest($request, $storedPath);
        $analysis = $this->analysisService->createAnalysis($dto);

        return response()->json([
            'success' => true,
            'message' => 'Analysis completed successfully.',
            'data' => new AnalysisResource($analysis),
        ], Response::HTTP_CREATED);
    }

    /**
     * Get paginated list of user's analyses.
     *
     * GET /api/analyses
     */
    public function index(Request $request): AnalysisCollection
    {
        $perPage = $request->integer('per_page', 15);
        $analyses = $this->analysisService->getUserAnalyses(
            (int) $request->user()->id,
            $perPage,
        );

        return new AnalysisCollection($analyses);
    }

    /**
     * Get a single analysis by ID.
     *
     * GET /api/analyses/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $analysis = $this->analysisService->getAnalysis(
            $id,
            (int) $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'data' => new AnalysisResource($analysis),
        ]);
    }

    /**
     * Delete an analysis (soft delete).
     *
     * DELETE /api/analyses/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->analysisService->deleteAnalysis(
            $id,
            (int) $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Analysis deleted successfully.',
        ]);
    }
}
