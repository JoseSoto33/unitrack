<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Enums\DayOfWeek;
use App\Enums\SubjectModality;
use App\Models\Schedule;
use App\Models\Subject;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subject_id')
                    ->label('Materia')
                    ->options(fn (): array => Subject::query()
                        ->whereHas('period', fn (Builder $query) => $query->where('user_id', Filament::auth()->id()))
                        ->pluck('name', 'id')
                        ->all())
                    ->in(fn (): array => Subject::query()
                        ->whereHas('period', fn (Builder $query) => $query->where('user_id', Filament::auth()->id()))
                        ->pluck('id')
                        ->all())
                    ->required()
                    ->searchable()
                    ->live(),
                Select::make('modality')
                    ->label('Modalidad del bloque')
                    ->options([
                        SubjectModality::InPerson->value => SubjectModality::InPerson->getLabel(),
                        SubjectModality::Online->value => SubjectModality::Online->getLabel(),
                    ])
                    ->visible(fn (Get $get): bool => self::subjectIsHybrid($get))
                    ->required(fn (Get $get): bool => self::subjectIsHybrid($get))
                    ->in([SubjectModality::InPerson->value, SubjectModality::Online->value]),
                Select::make('day_of_week')
                    ->label('Día')
                    ->options(DayOfWeek::class)
                    ->required(),
                TimePicker::make('starts_at')
                    ->label('Hora de inicio')
                    ->required(),
                TimePicker::make('ends_at')
                    ->label('Hora de fin')
                    ->required()
                    ->after('starts_at')
                    ->rule(function (Get $get, ?Model $record): Closure {
                        $recordId = $record?->getKey();

                        return function (string $attribute, mixed $value, Closure $fail) use ($get, $recordId): void {
                            $subjectId = $get('subject_id');
                            $day = $get('day_of_week');
                            $starts = $get('starts_at');

                            if (! $subjectId || ! $day || ! $starts || ! $value) {
                                return;
                            }

                            $periodId = Subject::query()->findOrFail((int) $subjectId)->period_id;
                            $startsAt = Carbon::parse($starts)->format('H:i:s');
                            $endsAt = Carbon::parse($value)->format('H:i:s');

                            $overlaps = Schedule::query()
                                ->where('user_id', Filament::auth()->id())
                                ->where('day_of_week', $day)
                                ->whereHas('subject', fn (Builder $query) => $query->where('period_id', $periodId))
                                ->when($recordId, fn ($query) => $query->whereKeyNot($recordId))
                                ->where('starts_at', '<', $endsAt)
                                ->where('ends_at', '>', $startsAt)
                                ->exists();

                            if ($overlaps) {
                                $fail('Este bloque se solapa con otro horario del mismo día y período.');
                            }
                        };
                    }),
            ]);
    }

    private static function subjectIsHybrid(Get $get): bool
    {
        $subjectId = $get('subject_id');

        if (! $subjectId) {
            return false;
        }

        return Subject::query()->find((int) $subjectId)?->modality === SubjectModality::Hybrid;
    }
}
