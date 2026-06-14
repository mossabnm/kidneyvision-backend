<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Contracts\Services\AIIntegrationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestAnalysisController extends Controller
{
    public function __construct(
        private readonly AIIntegrationServiceInterface $aiService,
    ) {}

    /**
     * Upload kidney image and trigger AI prediction as a guest.
     * Does not save to database.
     *
     * POST /api/guest-predict
     */
    public function predict(Request $request): JsonResponse
    {
        $ip = $request->ip();
        $usageCount = \Illuminate\Support\Facades\Cache::get("guest_uploads_{$ip}", 0);

        if ($usageCount >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the limit of 5 free guest analyses. Please create a professional account to continue.'
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240'], // 10MB max
        ]);

        // Store temporarily
        $storedPath = $request->file('image')->store('guest_analyses', 'public');

        try {
            // Trigger AI Prediction
            $result = $this->aiService->predict($storedPath);

            // Increment usage count
            \Illuminate\Support\Facades\Cache::put("guest_uploads_{$ip}", $usageCount + 1, now()->addDays(30));

            return response()->json([
                'success' => true,
                'message' => 'Analysis completed successfully.',
                'data' => [
                    'prediction' => $result->prediction,
                    'confidence' => $result->confidence,
                    'image_url' => $storedPath,
                    'usage_count' => $usageCount + 1,
                    'usage_limit' => 5,
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI Prediction failed.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
