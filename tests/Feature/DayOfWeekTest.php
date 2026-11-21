<?php

use App\Enums\DayOfWeek;

test('day of week labels are in spanish', function () {
    expect(DayOfWeek::Monday->getLabel())->toBe('Lunes')
        ->and(DayOfWeek::Tuesday->getLabel())->toBe('Martes')
        ->and(DayOfWeek::Wednesday->getLabel())->toBe('Miércoles')
        ->and(DayOfWeek::Thursday->getLabel())->toBe('Jueves')
        ->and(DayOfWeek::Friday->getLabel())->toBe('Viernes')
        ->and(DayOfWeek::Saturday->getLabel())->toBe('Sábado')
        ->and(DayOfWeek::Sunday->getLabel())->toBe('Domingo');
});
