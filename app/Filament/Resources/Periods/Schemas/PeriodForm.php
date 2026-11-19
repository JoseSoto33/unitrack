<?php

namespace App\Filament\Resources\Periods\Schemas;

use App\Enums\PeriodType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('career_id')
                    ->label('Carrera')
                    ->relationship('career', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('type')
                    ->label('Tipo')
                    ->options(PeriodType::class)
                    ->required(),
                TextInput::make('number')
                    ->label('Número')
                    ->required()
                    ->integer()
                    ->minValue(1),
                DatePicker::make('starts_on')
                    ->label('Fecha de inicio')
                    ->required(),
                DatePicker::make('ends_on')
                    ->label('Fecha de fin')
                    ->required()
                    ->after('starts_on'),
                Toggle::make('is_current')
                    ->label('Período actual')
                    ->default(false),
            ]);
    }
}
