<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SubjectStatus: string implements HasLabel
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Passed = 'passed';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::InProgress => 'En curso',
            self::Passed => 'Aprobada',
            self::Failed => 'Reprobada',
        };
    }
}
