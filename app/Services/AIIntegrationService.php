<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\AIIntegrationServiceInterface;
use App\DTOs\PredictionResultDTO;
use App\Exceptions\AIServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIIntegrationService implements AIIntegrationServiceInterface
{
    private string $baseUrl;
    private string $predictEndpoint;
    private string $healthEndpoint;
    private int $timeout;
    private int $retryTimes;
    private int $retrySleep;
    private bool $mockEnabled;

    public function __construct()
    {
        $this->baseUrl = config('ai.flask_base_url');
        $this->predictEndpoint = config('ai.predict_endpoint');
        $this->healthEndpoint = config('ai.health_endpoint');
        $this->timeout = config('ai.timeout');
        $this->retryTimes = config('ai.retry_times');
        $this->retrySleep = config('ai.retry_sleep');
        $this->mockEnabled = config('ai.mock_enabled');
    }

    /**
     * {@inheritDoc}
     */
    public function predict(string $imagePath): PredictionResultDTO
    {
        // Use mock in development if Flask isn't running
        if ($this->mockEnabled) {
            Log::info('Using mock AI prediction', ['image' => $imagePath]);
            return PredictionResultDTO::mock();
        }

        try {
            $fullPath = Storage::disk('public')->path($imagePath);

            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->attach('image', file_get_contents($fullPath), basename($fullPath))
                ->post("{$this->baseUrl}{$this->predictEndpoint}");

            if ($response->failed()) {
                throw new AIServiceException(
                    'AI service returned an error: ' . $response->body()
                );
            }

            return PredictionResultDTO::fromAIResponse($response->json());

        } catch (ConnectionException $e) {
            Log::error('Failed to connect to AI service', [
                'error' => $e->getMessage(),
                'base_url' => $this->baseUrl,
            ]);

            throw new AIServiceException(
                'Unable to connect to AI prediction service.',
                previous: $e,
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function healthCheck(): bool
    {
        if ($this->mockEnabled) {
            return true;
        }

        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}{$this->healthEndpoint}");

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('AI health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
