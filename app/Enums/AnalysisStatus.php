<?php

declare(strict_types=1);

namespace App\Enums;

enum AnalysisStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#FFA500',
            self::PROCESSING => '#3B82F6',
            self::COMPLETED => '#10B981',
            self::FAILED => '#EF4444',
        };
    }
}
