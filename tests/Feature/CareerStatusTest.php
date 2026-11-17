<?php

use App\Enums\CareerStatus;

test('career status labels are in spanish', function () {
    expect(CareerStatus::InProgress->getLabel())->toBe('En curso')
        ->and(CareerStatus::Graduated->getLabel())->toBe('Graduado')
        ->and(CareerStatus::Withdrawn->getLabel())->toBe('Retirado')
        ->and(CareerStatus::Paused->getLabel())->toBe('Pausado');
});
