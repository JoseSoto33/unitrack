<?php

namespace App\Filament\Resources\Universities\Pages;

use App\Filament\Resources\Universities\UniversityResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateUniversity extends CreateRecord
{
    protected static string $resource = UniversityResource::class;

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
