<?php

namespace App\Livewire;

use App\Models\Period;
use App\Models\Schedule;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ScheduleGrid extends Component
{
    public ?int $periodId = null;

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
     * @return array{slots: list<string>, days: list<int>, cells: array<int, array<string, array{subject: string, modality: string, from: string, to: string, span: int}|string|null>>, empty: bool}
     */
    #[Computed]
    public function grid(): array
    {
        $schedules = $this->ownSchedules();
        $range = $this->timeRange($schedules);

        if ($range === null) {
            return ['slots' => [], 'days' => range(1, 7), 'cells' => [], 'empty' => true];
        }

        [$start, $end] = $range;

        return [
            'slots' => $this->slots($start, $end),
            'days' => range(1, 7),
            'cells' => $this->buildCells($schedules, range(1, 7), $start, $end),
            'empty' => false,
        ];
    }

    public function render(): View
    {
        return view('livewire.schedule-grid');
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

    /**
     * From the earliest block start to the latest block end plus one quarter hour,
     * rounded to quarter hours.
     *
     * @param  Collection<int, Schedule>  $schedules
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function timeRange(Collection $schedules): ?array
    {
        $starts = [];
        $ends = [];

        foreach ($schedules as $schedule) {
            foreach ($schedule->classDays as $classDay) {
                foreach ($classDay->timeRanges as $range) {
                    $starts[] = Carbon::parse($range->starts_at)->setSeconds(0)->getTimestamp();
                    $ends[] = Carbon::parse($range->ends_at)->setSeconds(0)->getTimestamp();
                }
            }
        }

        if ($starts === [] || $ends === []) {
            return null;
        }

        $min = Carbon::createFromTimestamp(min($starts));
        $max = Carbon::createFromTimestamp(max($ends));

        $start = $min->copy()->setTime((int) $min->format('H'), intdiv((int) $min->format('i'), 15) * 15);
        $end = $max->copy()->setTime((int) $max->format('H'), (int) (ceil((int) $max->format('i') / 15) * 15))->addMinutes(15);

        return [$start, $end];
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
