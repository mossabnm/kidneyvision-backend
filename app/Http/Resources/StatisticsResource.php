<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    /**
     * Remove the data wrapper.
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'data' => [
                'total_analyses' => $this->resource['total_analyses'],
                'completed' => $this->resource['completed'],
                'pending' => $this->resource['pending'],
                'processing' => $this->resource['processing'],
                'failed' => $this->resource['failed'],
                'success_rate' => $this->resource['success_rate'],
                'average_confidence' => $this->resource['average_confidence'],
                'condition_distribution' => $this->resource['condition_distribution'],
                'recent_analyses' => AnalysisResource::collection($this->resource['recent_analyses']),
            ],
        ];
    }
}
