<?php

namespace App\Filament\Resources\Activities\Schemas;

use App\Enums\ActivityModality;
use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Subject;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActivityForm
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
                    ->searchable(),
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Tipo')
                    ->options(ActivityType::class)
                    ->required(),
                Select::make('modality')
                    ->label('Modalidad')
                    ->options(ActivityModality::class)
                    ->required(),
                DatePicker::make('assigned_on')
                    ->label('Fecha de asignación')
                    ->required(),
                DateTimePicker::make('due_at')
                    ->label('Fecha límite de entrega')
                    ->required()
                    ->afterOrEqual('assigned_on'),
                DateTimePicker::make('delivered_at')
                    ->label('Fecha entregado'),
                TextInput::make('percentage')
                    ->label('Porcentaje')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->rule(function (Get $get, ?Model $record): Closure {
                        $recordId = $record?->getKey();

                        return function (string $attribute, mixed $value, Closure $fail) use ($get, $recordId): void {
                            $subjectId = $get('subject_id');

                            if (! $subjectId) {
                                return;
                            }

                            $sum = (float) Activity::query()
                                ->where('subject_id', $subjectId)
                                ->when($recordId, fn ($query) => $query->whereKeyNot($recordId))
                                ->sum('percentage');

                            if ($sum + (float) $value > 100) {
                                $fail('La suma de porcentajes de la materia no puede superar 100.');
                            }
                        };
                    }),
                TextInput::make('grade')
                    ->label('Nota obtenida')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(function (Get $get): ?float {
                        $subject = Subject::query()->find((int) $get('subject_id'));

                        return $subject ? (float) $subject->max_grade : null;
                    }),
            ]);
    }
}
