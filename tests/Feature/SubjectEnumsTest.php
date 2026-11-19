<?php

use App\Enums\SubjectModality;
use App\Enums\SubjectStatus;

test('subject modality labels are in spanish', function () {
    expect(SubjectModality::InPerson->getLabel())->toBe('Presencial')
        ->and(SubjectModality::Online->getLabel())->toBe('Online')
        ->and(SubjectModality::Hybrid->getLabel())->toBe('Mixta');
});

test('subject status labels are in spanish', function () {
    expect(SubjectStatus::Pending->getLabel())->toBe('Pendiente')
        ->and(SubjectStatus::InProgress->getLabel())->toBe('En curso')
        ->and(SubjectStatus::Passed->getLabel())->toBe('Aprobada')
        ->and(SubjectStatus::Failed->getLabel())->toBe('Reprobada');
});
