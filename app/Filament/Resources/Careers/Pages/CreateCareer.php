<?php

namespace App\Filament\Resources\Careers\Pages;

use App\Filament\Resources\Careers\CareerResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateCareer extends CreateRecord
{
    protected static string $resource = CareerResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Filament::auth()->id();

        return $data;
    }
}
