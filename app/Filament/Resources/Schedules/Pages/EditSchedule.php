<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Enums\SubjectModality;
use App\Filament\Resources\Schedules\ScheduleResource;
use App\Models\Subject;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
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
