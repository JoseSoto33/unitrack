<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Enums\SubjectModality;
use App\Filament\Resources\Schedules\ScheduleResource;
use App\Models\Subject;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Filament::auth()->id();
        $data['modality'] = $this->blockModality($data);

        return $data;
    }

    /**
     * Only hybrid subjects carry a per-block modality.
     *
     * @param  array<string, mixed>  $data
     */
    private function blockModality(array $data): ?string
    {
        $subject = Subject::query()->find((int) ($data['subject_id'] ?? 0));

        return $subject?->modality === SubjectModality::Hybrid
            ? ($data['modality'] ?? null)
            : null;
    }
}
