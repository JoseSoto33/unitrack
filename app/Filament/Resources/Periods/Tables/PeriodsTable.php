<?php

namespace App\Filament\Resources\Periods\Tables;

use App\Enums\PeriodType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('user_id', Filament::auth()->id())
                ->with('career'))
            ->columns([
                TextColumn::make('career.title')
                    ->label('Carrera')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('number')
                    ->label('Número')
                    ->sortable(),
                TextColumn::make('starts_on')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_on')
                    ->label('Fin')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_current')
                    ->label('Actual')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(PeriodType::class),
                TernaryFilter::make('is_current')
                    ->label('Período actual'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
