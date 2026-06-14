<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class PredictionResultDTO
{
    public function __construct(
        public string $prediction,
        public float $confidence,
        public array $rawPayload = [],
    ) {}

    public static function fromAIResponse(array $response): self
    {
        return new self(
            prediction: $response['prediction'] ?? 'Unknown',
            confidence: (float) ($response['confidence'] ?? 0.0),
            rawPayload: $response,
        );
    }

    /**
     * Create a mock prediction for development.
     */
    public static function mock(): self
    {
        $conditions = ['Normal', 'Cyst', 'Tumor', 'Stone'];
        $prediction = $conditions[array_rand($conditions)];

        return new self(
            prediction: $prediction,
            confidence: round(mt_rand(7000, 9900) / 100, 2),
            rawPayload: [
                'prediction' => $prediction,
                'confidence' => round(mt_rand(7000, 9900) / 100, 2),
                'model_version' => 'mock-v1.0',
                'mock' => true,
            ],
        );
    }
}
