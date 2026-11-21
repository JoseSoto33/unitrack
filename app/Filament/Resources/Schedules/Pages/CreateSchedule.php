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

        return $this->normalizeModalities($data);
    }

    /**
     * Only hybrid subjects carry a per-day modality.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeModalities(array $data): array
    {
        $subject = Subject::query()->find((int) ($data['subject_id'] ?? 0));

        if ($subject?->modality === SubjectModality::Hybrid) {
            return $data;
        }

        foreach (($data['classDays'] ?? []) as $index => $day) {
            $data['classDays'][$index]['modality'] = null;
        }

        return $data;
    }
}
