<?php

namespace App\Filament\Resources\Schedules\Tables;

use App\Enums\DayOfWeek;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('user_id', Filament::auth()->id())
                ->with('subject'))
            ->columns([
                TextColumn::make('subject.name')
                    ->label('Materia')
                    ->searchable(),
                TextColumn::make('day_of_week')
                    ->label('Día')
                    ->badge()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->time()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Fin')
                    ->time()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label('Día')
                    ->options(DayOfWeek::class),
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
