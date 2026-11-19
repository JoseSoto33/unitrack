<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PeriodType: string implements HasLabel
{
    case Annual = 'annual';
    case Quarterly = 'quarterly';
    case Semester = 'semester';

    public function getLabel(): string
    {
        return match ($this) {
            self::Annual => 'Año',
            self::Quarterly => 'Trimestre',
            self::Semester => 'Semestre',
        };
    }
}
