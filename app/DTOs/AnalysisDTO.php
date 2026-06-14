<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Http\Requests\StoreAnalysisRequest;

final readonly class AnalysisDTO
{
    public function __construct(
        public string $imagePath,
        public string $originalFilename,
        public int $userId,
    ) {}

    /**
     * Create DTO from a validated request.
     */
    public static function fromRequest(StoreAnalysisRequest $request, string $storedPath): self
    {
        return new self(
            imagePath: $storedPath,
            originalFilename: $request->file('image')->getClientOriginalName(),
            userId: (int) $request->user()->id,
        );
    }
}
