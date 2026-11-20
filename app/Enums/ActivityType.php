<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ActivityType: string implements HasLabel
{
    case Exam = 'exam';
    case Midterm = 'midterm';
    case Quiz = 'quiz';
    case Workshop = 'workshop';
    case Presentation = 'presentation';
    case Report = 'report';
    case Essay = 'essay';
    case Debate = 'debate';
    case Discussion = 'discussion';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Exam => 'Examen',
            self::Midterm => 'Parcial',
            self::Quiz => 'Quiz',
            self::Workshop => 'Taller',
            self::Presentation => 'Exposición',
            self::Report => 'Informe',
            self::Essay => 'Ensayo',
            self::Debate => 'Debate',
            self::Discussion => 'Discusión',
            self::Other => 'Otro',
        };
    }
}
