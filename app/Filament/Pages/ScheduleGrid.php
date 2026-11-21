<?php

namespace App\Filament\Pages;

use App\Models\Period;
use App\Models\Schedule;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use UnitEnum;

class ScheduleGrid extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $navigationLabel = 'Cuadro de horario';

    protected static string|UnitEnum|null $navigationGroup = 'Académico';

    protected static ?string $title = 'Cuadro de horario';

    protected string $view = 'filament.pages.schedule-grid';

    public ?int $periodId = null;

    public string $fromTime = '07:00';

    public string $toTime = '17:00';

    public function mount(): void
    {
        $this->periodId = Period::query()
            ->where('user_id', Filament::auth()->id())
            ->orderByDesc('is_current')
            ->first()?->id;
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function periods(): array
    {
        return Period::query()
            ->where('user_id', Filament::auth()->id())
            ->with('career')
            ->get()
            ->mapWithKeys(fn (Period $period): array => [
                $period->id => $period->career->title.' — '.$period->type->getLabel().' '.$period->number.($period->is_current ? ' (actual)' : ''),
            ])
            ->all();
    }

    /**
     * @return array{slots: list<string>, days: list<int>, cells: array<int, array<string, array{subject: string, modality: string, from: string, to: string, span: int}|string|null>>, invalid: bool}
     */
    #[Computed]
    public function grid(): array
    {
        $days = range(1, 7);

        $windowStart = Carbon::parse(filled($this->fromTime) ? $this->fromTime : '07:00')->setSeconds(0);
        $windowEnd = Carbon::parse(filled($this->toTime) ? $this->toTime : '17:00')->setSeconds(0);

        if ($windowEnd <= $windowStart) {
            return ['slots' => [], 'days' => $days, 'cells' => [], 'invalid' => true];
        }

        $slots = [];

        for ($cursor = $windowStart->copy(); $cursor < $windowEnd; $cursor->addMinutes(15)) {
            $slots[] = $cursor->format('H:i');
        }

        $cells = [];

        $schedules = Schedule::query()
            ->where('user_id', Filament::auth()->id())
            ->whereHas('subject', fn ($query) => $query->where('period_id', $this->periodId))
            ->with(['subject', 'classDays.timeRanges'])
            ->get();

        foreach ($schedules as $schedule) {
            foreach ($schedule->classDays as $classDay) {
                $day = $classDay->day_of_week->value;

                foreach ($classDay->timeRanges as $range) {
                    $from = Carbon::parse($range->starts_at)->setSeconds(0);
                    $to = Carbon::parse($range->ends_at)->setSeconds(0);

                    $visibleFrom = $from->max($windowStart);
                    $visibleTo = $to->min($windowEnd);

                    if ($visibleFrom >= $visibleTo) {
                        continue;
                    }

                    $span = (int) (($visibleTo->getTimestamp() - $visibleFrom->getTimestamp()) / 900);
                    $slot = $visibleFrom->format('H:i');

                    $cells[$day][$slot] = [
                        'subject' => $schedule->subject->name,
                        'modality' => $classDay->effective_modality->getLabel(),
                        'from' => $visibleFrom->format('H:i'),
                        'to' => $visibleTo->format('H:i'),
                        'span' => $span,
                    ];

                    for ($i = 1; $i < $span; $i++) {
                        $cells[$day][$visibleFrom->copy()->addMinutes(15 * $i)->format('H:i')] = 'covered';
                    }
                }
            }
        }

        return ['slots' => $slots, 'days' => $days, 'cells' => $cells, 'invalid' => false];
    }
}
