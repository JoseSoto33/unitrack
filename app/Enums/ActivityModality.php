<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ActivityModality: string implements HasLabel
{
    case Individual = 'individual';
    case Pair = 'pair';
    case Group = 'group';

    public function getLabel(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::Pair => 'Parejas',
            self::Group => 'Grupal',
        };
    }
}
