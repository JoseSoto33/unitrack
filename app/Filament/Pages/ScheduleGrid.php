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
     * @return array{slots: list<string>, days: list<int>, cells: array<int, array<string, Schedule|string|null>>}
     */
    #[Computed]
    public function grid(): array
    {
        $days = range(1, 7);

        $blocks = Schedule::query()
            ->where('user_id', Filament::auth()->id())
            ->whereHas('subject', fn ($query) => $query->where('period_id', $this->periodId))
            ->with('subject')
            ->get();

        if ($blocks->isEmpty()) {
            return ['slots' => [], 'days' => $days, 'cells' => []];
        }

        $first = Carbon::parse($blocks->min('starts_at'));
        $last = Carbon::parse($blocks->max('ends_at'));

        $start = $first->copy()->setTime((int) $first->format('H'), intdiv((int) $first->format('i'), 30) * 30);
        $end = $last->copy()->setTime((int) $last->format('H'), (int) ceil((int) $last->format('i') / 30) * 30);

        $slots = [];

        for ($cursor = $start->copy(); $cursor < $end; $cursor->addMinutes(30)) {
            $slots[] = $cursor->format('H:i');
        }

        $cells = [];

        foreach ($blocks as $block) {
            $day = $block->day_of_week->value;
            $from = Carbon::parse($block->starts_at);
            $span = (int) ((strtotime($block->ends_at) - strtotime($block->starts_at)) / 1800);

            $cells[$day][$from->format('H:i')] = $block;

            for ($i = 1; $i < $span; $i++) {
                $cells[$day][$from->copy()->addMinutes(30 * $i)->format('H:i')] = 'covered';
            }
        }

        return ['slots' => $slots, 'days' => $days, 'cells' => $cells];
    }
}
