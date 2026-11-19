<?php

use App\Enums\PeriodType;

test('period type labels are in spanish', function () {
    expect(PeriodType::Annual->getLabel())->toBe('Año')
        ->and(PeriodType::Quarterly->getLabel())->toBe('Trimestre')
        ->and(PeriodType::Semester->getLabel())->toBe('Semestre');
});
