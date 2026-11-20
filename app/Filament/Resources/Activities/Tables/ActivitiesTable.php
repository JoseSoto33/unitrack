<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Enums\ActivityModality;
use App\Enums\ActivityType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('user_id', Filament::auth()->id())
                ->with('subject'))
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.name')
                    ->label('Materia')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('due_at')
                    ->label('Fecha límite')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivery_status')
                    ->label('Entrega')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Entregada' => 'success',
                        'Pendiente' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('modality')
                    ->label('Modalidad')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('percentage')
                    ->label('Porcentaje')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' %')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('grade')
                    ->label('Nota')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('net_value')
                    ->label('Valor neto')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(ActivityType::class),
                SelectFilter::make('modality')
                    ->label('Modalidad')
                    ->options(ActivityModality::class),
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
