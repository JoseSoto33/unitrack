<?php

use App\Enums\ActivityModality;
use App\Enums\ActivityType;

test('activity type labels are in spanish', function () {
    expect(ActivityType::Exam->getLabel())->toBe('Examen')
        ->and(ActivityType::Midterm->getLabel())->toBe('Parcial')
        ->and(ActivityType::Quiz->getLabel())->toBe('Quiz')
        ->and(ActivityType::Workshop->getLabel())->toBe('Taller')
        ->and(ActivityType::Presentation->getLabel())->toBe('Exposición')
        ->and(ActivityType::Report->getLabel())->toBe('Informe')
        ->and(ActivityType::Essay->getLabel())->toBe('Ensayo')
        ->and(ActivityType::Debate->getLabel())->toBe('Debate')
        ->and(ActivityType::Discussion->getLabel())->toBe('Discusión')
        ->and(ActivityType::Other->getLabel())->toBe('Otro');
});

test('activity modality labels are in spanish', function () {
    expect(ActivityModality::Individual->getLabel())->toBe('Individual')
        ->and(ActivityModality::Pair->getLabel())->toBe('Parejas')
        ->and(ActivityModality::Group->getLabel())->toBe('Grupal');
});
