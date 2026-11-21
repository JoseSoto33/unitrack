<?php

namespace App\Filament\Widgets;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Subject;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\FetchInfo;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ActivityCalendarWidget extends FullCalendarWidget
{
    public Model|string|null $model = Activity::class;

    /**
     * @return Builder<Activity>
     */
    protected function getEloquentQuery(): Builder
    {
        return Activity::query()->where('user_id', Filament::auth()->id());
    }

    /**
     * @return Collection<int, Activity>
     */
    public function fetchEvents(FetchInfo $info): Collection
    {
        $query = $this->getEloquentQuery()
            ->whereBetween('due_at', [$info->start, $info->end])
            ->with('subject');

        $filters = $this->filters ?? [];

        if (filled($filters['subject_id'] ?? null)) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (filled($filters['type'] ?? null)) {
            $query->where('type', $filters['type']);
        }

        if (filled($filters['status'] ?? null)) {
            match ($filters['status']) {
                'delivered' => $query->whereNotNull('delivered_at'),
                'pending' => $query->whereNull('delivered_at')->where('due_at', '>=', now()),
                'overdue' => $query->whereNull('delivered_at')->where('due_at', '<', now()),
                default => null,
            };
        }

        return $query->get();
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subject_id')
                    ->label('Materia')
                    ->options(fn (): array => Subject::query()
                        ->whereHas('period', fn (Builder $query) => $query->where('user_id', Filament::auth()->id()))
                        ->pluck('name', 'id')
                        ->all()),
                Select::make('type')
                    ->label('Tipo')
                    ->options(ActivityType::class),
                Select::make('status')
                    ->label('Estado de entrega')
                    ->options([
                        'delivered' => 'Entregada',
                        'pending' => 'Pendiente',
                        'overdue' => 'Vencida',
                    ]),
            ]);
    }

    public function config(): array
    {
        return [
            'locale' => 'es',
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'editable' => false,
            'selectable' => false,
            'height' => 'auto',
            'nowIndicator' => true,
            'slotMinTime' => '06:00:00',
            'slotMaxTime' => '22:00:00',
            'slotDuration' => '00:15:00',
            'allDaySlot' => false,
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Título'),
                TextEntry::make('subject.name')
                    ->label('Materia'),
                TextEntry::make('type')
                    ->label('Tipo'),
                TextEntry::make('modality')
                    ->label('Modalidad'),
                TextEntry::make('assigned_on')
                    ->label('Fecha de asignación')
                    ->date(),
                TextEntry::make('due_at')
                    ->label('Fecha límite')
                    ->dateTime(),
                TextEntry::make('delivered_at')
                    ->label('Fecha entregado')
                    ->dateTime()
                    ->placeholder('Sin entregar'),
                TextEntry::make('delivery_status')
                    ->label('Estado de entrega'),
                TextEntry::make('percentage')
                    ->label('Porcentaje')
                    ->suffix(' %'),
                TextEntry::make('grade')
                    ->label('Nota obtenida')
                    ->placeholder('Sin calificar'),
            ]);
    }

    /**
     * @return array<int, Action>
     */
    protected function modalActions(): array
    {
        return [];
    }
}
