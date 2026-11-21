<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Enums\DayOfWeek;
use App\Enums\SubjectModality;
use App\Models\Subject;
use App\Models\TimeRange;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
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
                Repeater::make('classDays')
                    ->label('Días de clase')
                    ->relationship()
                    ->schema([
                        Select::make('day_of_week')
                            ->label('Día')
                            ->options(DayOfWeek::class)
                            ->required(),
                        Select::make('modality')
                            ->label('Modalidad')
                            ->options([
                                SubjectModality::InPerson->value => SubjectModality::InPerson->getLabel(),
                                SubjectModality::Online->value => SubjectModality::Online->getLabel(),
                            ])
                            ->placeholder('Hereda de la materia')
                            ->disabled(fn (Get $get): bool => ! self::subjectIsHybrid($get))
                            ->required(fn (Get $get): bool => self::subjectIsHybrid($get))
                            ->in([SubjectModality::InPerson->value, SubjectModality::Online->value]),
                        Repeater::make('timeRanges')
                            ->label('Rangos de hora')
                            ->relationship()
                            ->schema([
                                TimePicker::make('starts_at')
                                    ->label('Inicio')
                                    ->required(),
                                TimePicker::make('ends_at')
                                    ->label('Fin')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Añadir rango')
                            ->rule(fn (Get $get, ?Model $record): Closure => self::validateRanges($get, $record)),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Añadir día')
                    ->rule(fn (): Closure => self::validateDistinctDays()),
            ]);
    }

    private static function subjectIsHybrid(Get $get): bool
    {
        $subjectId = $get('../../subject_id');

        if (! $subjectId) {
            return false;
        }

        return Subject::query()->find((int) $subjectId)?->modality === SubjectModality::Hybrid;
    }

    private static function validateDistinctDays(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $days = array_column(array_filter(is_array($value) ? $value : [], 'is_array'), 'day_of_week');

            if (count($days) !== count(array_unique($days))) {
                $fail('No puedes repetir el mismo día de la semana.');
            }
        };
    }

    private static function validateRanges(Get $get, ?Model $record): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
            $ranges = array_values(array_filter(
                is_array($value) ? $value : [],
                fn (mixed $range): bool => is_array($range) && filled($range['starts_at'] ?? null) && filled($range['ends_at'] ?? null),
            ));

            if ($ranges === []) {
                return;
            }

            $parsed = [];

            foreach ($ranges as $range) {
                $start = Carbon::parse($range['starts_at'])->format('H:i:s');
                $end = Carbon::parse($range['ends_at'])->format('H:i:s');

                if ($end <= $start) {
                    $fail('Cada rango debe terminar después de empezar.');

                    return;
                }

                if (strtotime($end) - strtotime($start) < 1800) {
                    $fail('Cada rango debe durar al menos 30 minutos.');

                    return;
                }

                $parsed[] = [$start, $end];
            }

            foreach ($parsed as $i => [$start, $end]) {
                foreach ($parsed as $j => [$otherStart, $otherEnd]) {
                    if ($i !== $j && $start < $otherEnd && $end > $otherStart) {
                        $fail('Los rangos de un mismo día no pueden repetirse ni solaparse.');

                        return;
                    }
                }
            }

            $day = $get('day_of_week');
            $subjectId = $get('../../subject_id');

            if (! $day || ! $subjectId) {
                return;
            }

            $periodId = Subject::query()->find((int) $subjectId)?->period_id;

            $existing = TimeRange::query()
                ->whereHas('classDay', fn (Builder $query) => $query
                    ->where('day_of_week', $day)
                    ->whereHas('schedule', fn (Builder $query) => $query
                        ->where('user_id', Filament::auth()->id())
                        ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
                        ->whereHas('subject', fn (Builder $query) => $query->where('period_id', $periodId))))
                ->get();

            foreach ($parsed as [$start, $end]) {
                foreach ($existing as $other) {
                    if ($start < $other->ends_at && $end > $other->starts_at) {
                        $fail('Este rango se solapa con el horario de otra materia del mismo día y período.');

                        return;
                    }
                }
            }
        };
    }
}
