<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CareerStatus: string implements HasLabel
{
    case InProgress = 'in_progress';
    case Graduated = 'graduated';
    case Withdrawn = 'withdrawn';
    case Paused = 'paused';

    public function getLabel(): string
    {
        return match ($this) {
            self::InProgress => 'En curso',
            self::Graduated => 'Graduado',
            self::Withdrawn => 'Retirado',
            self::Paused => 'Pausado',
        };
    }
}
