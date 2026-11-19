<?php

namespace App\Filament\Resources\Subjects\Tables;

use App\Enums\SubjectModality;
use App\Enums\SubjectStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->whereHas('period', fn (Builder $periodQuery) => $periodQuery->where('user_id', Filament::auth()->id()))
                ->with(['period.career']))
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period.career.title')
                    ->label('Carrera')
                    ->searchable(),
                TextColumn::make('modality')
                    ->label('Modalidad')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('final_grade')
                    ->label('Nota final')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('credits')
                    ->label('Unidades curriculares')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('modality')
                    ->label('Modalidad')
                    ->options(SubjectModality::class),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(SubjectStatus::class),
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
