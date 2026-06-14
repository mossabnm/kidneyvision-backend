<?php

declare(strict_types=1);

namespace App\Enums;

enum KidneyCondition: string
{
    case NORMAL = 'Normal';
    case CYST = 'Cyst';
    case TUMOR = 'Tumor';
    case STONE = 'Stone';

    public function description(): string
    {
        return match ($this) {
            self::NORMAL => 'No abnormalities detected',
            self::CYST => 'Cystic formation detected',
            self::TUMOR => 'Tumor mass detected',
            self::STONE => 'Kidney stone detected',
        };
    }

    public function severity(): string
    {
        return match ($this) {
            self::NORMAL => 'low',
            self::CYST => 'medium',
            self::STONE => 'medium',
            self::TUMOR => 'high',
        };
    }
}
