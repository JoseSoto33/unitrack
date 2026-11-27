<?php

namespace App\Filament\Pages;

use App\Models\Period;
use App\Models\Schedule;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
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

    public string $activeView = 'week';

    public string $dayDate = '';

    public string $monthAnchor = '';

    public function mount(): void
    {
        $this->periodId = Period::query()
            ->where('user_id', Filament::auth()->id())
            ->orderByDesc('is_current')
            ->first()?->id;
        $this->dayDate = now()->format('Y-m-d');
        $this->monthAnchor = now()->startOfMonth()->format('Y-m-d');
    }

    public function setView(string $view): void
    {
        if (in_array($view, ['week', 'day', 'month'], true)) {
            $this->activeView = $view;
        }
    }

    public function goToDay(string $date): void
    {
        $this->dayDate = $date;
        $this->monthAnchor = Carbon::parse($date)->startOfMonth()->format('Y-m-d');
        $this->activeView = 'day';
    }

    public function previousMonth(): void
    {
        $this->monthAnchor = Carbon::parse($this->monthAnchor)->subMonthNoOverflow()->startOfMonth()->format('Y-m-d');
    }

    public function nextMonth(): void
    {
        $this->monthAnchor = Carbon::parse($this->monthAnchor)->addMonthNoOverflow()->startOfMonth()->format('Y-m-d');
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

    #[Computed]
    public function period(): ?Period
    {
        return Period::query()
            ->where('user_id', Filament::auth()->id())
            ->find($this->periodId);
    }

    /**
     * @return array{slots: list<string>, days: list<int>, cells: array<int, array<string, array{subject: string, modality: string, from: string, to: string, span: int}|string|null>>, invalid: bool}
     */
    #[Computed]
    public function grid(): array
    {
        $window = $this->window();

        if ($window['invalid']) {
            return ['slots' => [], 'days' => range(1, 7), 'cells' => [], 'invalid' => true];
        }

        return [
            'slots' => $this->slots($window['start'], $window['end']),
            'days' => range(1, 7),
            'cells' => $this->buildCells($this->ownSchedules(), range(1, 7), $window['start'], $window['end']),
            'invalid' => false,
        ];
    }

    /**
     * @return array{date: string, weekday: int, inPeriod: bool, slots: list<string>, cells: array<string, array{subject: string, modality: string, from: string, to: string, span: int}|string|null>, invalid: bool}
     */
    #[Computed]
    public function dayTimeline(): array
    {
        $date = Carbon::parse($this->dayDate)->startOfDay();
        $window = $this->window();
        $inPeriod = $this->dateInPeriod($date);
        $weekday = $date->dayOfWeekIso;

        $cells = [];

        if ($inPeriod && ! $window['invalid']) {
            $cells = $this->buildCells($this->ownSchedules(), [$weekday], $window['start'], $window['end'])[$weekday] ?? [];
        }

        return [
            'date' => $date->format('Y-m-d'),
            'weekday' => $weekday,
            'inPeriod' => $inPeriod,
            'slots' => $window['invalid'] ? [] : $this->slots($window['start'], $window['end']),
            'cells' => $cells,
            'invalid' => $window['invalid'],
        ];
    }

    /**
     * @return array{label: string, weeks: list<list<array{date: string, day: int, inMonth: bool, inPeriod: bool, blocks: list<array{subject: string, from: string, modality: string}>}>>}
     */
    #[Computed]
    public function monthGrid(): array
    {
        $anchor = Carbon::parse($this->monthAnchor)->startOfMonth();
        $byWeekday = [];

        foreach ($this->ownSchedules() as $schedule) {
            foreach ($schedule->classDays as $classDay) {
                foreach ($classDay->timeRanges as $range) {
                    $byWeekday[$classDay->day_of_week->value][] = [
                        'subject' => $schedule->subject->name,
                        'from' => Carbon::parse($range->starts_at)->format('H:i'),
                        'modality' => $classDay->effective_modality->getLabel(),
                    ];
                }
            }
        }

        foreach ($byWeekday as &$blocks) {
            usort($blocks, fn (array $a, array $b): int => strcmp($a['from'], $b['from']));
        }
        unset($blocks);

        $cursor = $anchor->copy()->startOfWeek(Carbon::MONDAY);
        $last = $anchor->copy()->endOfMonth()->endOfWeek(Carbon::MONDAY);
        $weeks = [];

        while ($cursor <= $last) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $date = $cursor->copy();
                $inPeriod = $this->dateInPeriod($date);

                $week[] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => (int) $date->format('j'),
                    'inMonth' => $date->month === $anchor->month,
                    'inPeriod' => $inPeriod,
                    'blocks' => $inPeriod ? ($byWeekday[$date->dayOfWeekIso] ?? []) : [],
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        $anchor->locale('es');

        return [
            'label' => $anchor->translatedFormat('F Y'),
            'weeks' => $weeks,
        ];
    }

    /**
     * @return Collection<int, Schedule>
     */
    private function ownSchedules(): Collection
    {
        return Schedule::query()
            ->where('user_id', Filament::auth()->id())
            ->whereHas('subject', fn ($query) => $query->where('period_id', $this->periodId))
            ->with(['subject', 'classDays.timeRanges'])
            ->get();
    }

    private function dateInPeriod(Carbon $date): bool
    {
        $period = $this->period();

        return $period !== null
            && $date->betweenIncluded($period->starts_on->startOfDay(), $period->ends_on->startOfDay());
    }

    /**
     * @return array{start: Carbon, end: Carbon, invalid: bool}
     */
    private function window(): array
    {
        $start = Carbon::parse(filled($this->fromTime) ? $this->fromTime : '07:00')->setSeconds(0);
        $end = Carbon::parse(filled($this->toTime) ? $this->toTime : '17:00')->setSeconds(0);

        return ['start' => $start, 'end' => $end, 'invalid' => $end <= $start];
    }

    /**
     * @return list<string>
     */
    private function slots(Carbon $start, Carbon $end): array
    {
        $slots = [];

        for ($cursor = $start->copy(); $cursor < $end; $cursor->addMinutes(15)) {
            $slots[] = $cursor->format('H:i');
        }

        return $slots;
    }

    /**
     * @param  Collection<int, Schedule>  $schedules
     * @param  list<int>  $days
     * @return array<int, array<string, array{subject: string, modality: string, from: string, to: string, span: int}|string|null>>
     */
    private function buildCells(Collection $schedules, array $days, Carbon $windowStart, Carbon $windowEnd): array
    {
        $cells = [];

        foreach ($schedules as $schedule) {
            foreach ($schedule->classDays as $classDay) {
                $day = $classDay->day_of_week->value;

                if (! in_array($day, $days, true)) {
                    continue;
                }

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

        return $cells;
    }
}
