<?php

namespace App\Filament\Resources\Subjects\Schemas;

use App\Enums\SubjectModality;
use App\Enums\SubjectStatus;
use App\Models\Period;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('period_id')
                    ->label('Período')
                    ->options(fn (): array => Period::query()
                        ->where('user_id', Filament::auth()->id())
                        ->with('career')
                        ->get()
                        ->mapWithKeys(fn (Period $period): array => [
                            $period->id => $period->career->title.' — '.$period->type->getLabel().' '.$period->number,
                        ])
                        ->all())
                    ->in(fn (): array => Period::query()
                        ->where('user_id', Filament::auth()->id())
                        ->pluck('id')
                        ->all())
                    ->required()
                    ->searchable(),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('professor')
                    ->label('Profesor')
                    ->maxLength(255),
                TextInput::make('credits')
                    ->label('Unidades curriculares')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(127),
                Select::make('modality')
                    ->label('Modalidad')
                    ->options(SubjectModality::class)
                    ->required(),
                TextInput::make('max_grade')
                    ->label('Escala máxima')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->maxValue(999.99),
                TextInput::make('min_passing_grade')
                    ->label('Nota mínima aprobatoria')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(999.99)
                    ->lte('max_grade'),
                TextInput::make('final_grade')
                    ->label('Nota final')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(999.99)
                    ->lte('max_grade'),
                Select::make('status')
                    ->label('Estado')
                    ->options(SubjectStatus::class)
                    ->default('pending')
                    ->required(),
            ]);
    }
}
