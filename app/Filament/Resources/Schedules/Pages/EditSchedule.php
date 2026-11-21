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
