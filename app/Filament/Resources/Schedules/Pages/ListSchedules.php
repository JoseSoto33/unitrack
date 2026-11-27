<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected string $view = 'filament.resources.schedules.pages.list-schedules';

    public string $tab = 'grid';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
