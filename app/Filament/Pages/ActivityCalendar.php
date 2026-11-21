<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActivityCalendarWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ActivityCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Calendario';

    protected static string|UnitEnum|null $navigationGroup = 'Académico';

    protected static ?string $title = 'Calendario de actividades';

    protected string $view = 'filament.pages.activity-calendar';

    protected function getHeaderWidgets(): array
    {
        return [
            ActivityCalendarWidget::class,
        ];
    }
}
