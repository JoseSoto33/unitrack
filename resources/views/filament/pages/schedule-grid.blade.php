<x-filament-panels::page>
    <div wire:poll.5s class="flex flex-col gap-4">
        <div class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1">
                <label for="periodId" class="text-sm font-medium text-gray-700 dark:text-gray-200">Período</label>
                <x-filament::input.select id="periodId" wire:model.live="periodId" class="max-w-sm">
                    @foreach ($this->periods as $id => $label)
                        <option wire:key="period-{{ $id }}" value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </div>
            <div class="flex gap-1 rounded-lg border border-gray-200 p-1 dark:border-white/10">
                @foreach (['week' => 'Semana', 'day' => 'Día', 'month' => 'Mes'] as $key => $label)
                    <button
                        wire:key="view-{{ $key }}"
                        wire:click="setView('{{ $key }}')"
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium {{ $activeView === $key ? 'bg-primary-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            @if ($activeView !== 'month')
                <div class="flex flex-col gap-1">
                    <label for="fromTime" class="text-sm font-medium text-gray-700 dark:text-gray-200">Desde</label>
                    <x-filament::input.select id="fromTime" wire:model.live="fromTime">
                        @foreach (\App\Support\TimeSlots::options() as $value => $label)
                            <option wire:key="from-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </div>
                <div class="flex flex-col gap-1">
                    <label for="toTime" class="text-sm font-medium text-gray-700 dark:text-gray-200">Hasta</label>
                    <x-filament::input.select id="toTime" wire:model.live="toTime">
                        @foreach (\App\Support\TimeSlots::options() as $value => $label)
                            <option wire:key="to-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </div>
            @endif
            @if ($activeView === 'day')
                <div class="flex flex-col gap-1">
                    <label for="dayDate" class="text-sm font-medium text-gray-700 dark:text-gray-200">Fecha</label>
                    <x-filament::input id="dayDate" type="date" wire:model.live="dayDate" />
                </div>
            @endif
            @if ($activeView === 'month')
                <div class="flex items-center gap-2">
                    <x-filament::button wire:click="previousMonth" icon="heroicon-o-chevron-left" color="gray" />
                    <span class="min-w-40 text-center text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $this->monthGrid['label'] }}</span>
                    <x-filament::button wire:click="nextMonth" icon="heroicon-o-chevron-right" color="gray" />
                </div>
            @endif
        </div>

        @if ($activeView === 'week')
            @if ($this->grid['invalid'])
                <x-filament::section>
                    <p class="text-sm font-medium text-danger-600 dark:text-danger-400">
                        La hora de inicio debe ser anterior a la hora final.
                    </p>
                </x-filament::section>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <table class="w-full table-fixed border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="w-24 border border-gray-200 px-3 py-2 text-left font-semibold text-gray-700 dark:border-white/10 dark:text-gray-200">Hora</th>
                                @foreach ($this->grid['days'] as $day)
                                    <th wire:key="day-{{ $day }}" class="border border-gray-200 px-3 py-2 text-left font-semibold text-gray-700 dark:border-white/10 dark:text-gray-200">
                                        {{ \App\Enums\DayOfWeek::from($day)->getLabel() }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->grid['slots'] as $slot)
                                <tr wire:key="slot-{{ $slot }}">
                                    <td class="h-10 border border-gray-200 px-3 py-1 align-top text-xs font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">{{ $slot }}</td>
                                    @foreach ($this->grid['days'] as $day)
                                        @php($cell = $this->grid['cells'][$day][$slot] ?? null)
                                        @if ($cell === 'covered')
                                            {{-- celda cubierta por el rowspan de un bloque anterior --}}
                                        @elseif (is_array($cell))
                                            <td wire:key="cell-{{ $day }}-{{ $slot }}" rowspan="{{ $cell['span'] }}" class="border border-gray-200 px-1 py-1 align-top dark:border-white/10">
                                                <div class="flex flex-col gap-0.5 rounded-lg bg-amber-50 p-2 dark:bg-amber-500/10">
                                                    <span class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $cell['subject'] }}</span>
                                                    <span class="text-[12px] text-gray-500 dark:text-gray-400">{{ $cell['from'] }}–{{ $cell['to'] }}</span>
                                                    <span class="text-[12px] font-medium text-amber-700 dark:text-amber-300">{{ $cell['modality'] }}</span>
                                                </div>
                                            </td>
                                        @else
                                            <td wire:key="cell-{{ $day }}-{{ $slot }}" class="h-10 border border-gray-200 dark:border-white/10"></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @elseif ($activeView === 'day')
            @if (! $this->dayTimeline['inPeriod'])
                <x-filament::section>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        La fecha seleccionada está fuera del rango del período elegido.
                    </p>
                </x-filament::section>
            @elseif ($this->dayTimeline['invalid'])
                <x-filament::section>
                    <p class="text-sm font-medium text-danger-600 dark:text-danger-400">
                        La hora de inicio debe ser anterior a la hora final.
                    </p>
                </x-filament::section>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <table class="w-full table-fixed border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="w-24 border border-gray-200 px-3 py-2 text-left font-semibold text-gray-700 dark:border-white/10 dark:text-gray-200">Hora</th>
                                <th class="border border-gray-200 px-3 py-2 text-left font-semibold text-gray-700 dark:border-white/10 dark:text-gray-200">
                                    {{ \App\Enums\DayOfWeek::from($this->dayTimeline['weekday'])->getLabel() }} {{ \Illuminate\Support\Carbon::parse($this->dayTimeline['date'])->format('d/m/Y') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->dayTimeline['slots'] as $slot)
                                <tr wire:key="day-slot-{{ $slot }}">
                                    <td class="h-10 border border-gray-200 px-3 py-1 align-top text-xs font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">{{ $slot }}</td>
                                    @php($cell = $this->dayTimeline['cells'][$slot] ?? null)
                                    @if ($cell === 'covered')
                                        {{-- celda cubierta por el rowspan de un bloque anterior --}}
                                    @elseif (is_array($cell))
                                        <td wire:key="day-cell-{{ $slot }}" rowspan="{{ $cell['span'] }}" class="border border-gray-200 px-1 py-1 align-top dark:border-white/10">
                                            <div class="flex flex-col gap-0.5 rounded-lg bg-amber-50 p-2 dark:bg-amber-500/10">
                                                <span class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $cell['subject'] }}</span>
                                                <span class="text-[12px] text-gray-500 dark:text-gray-400">{{ $cell['from'] }}–{{ $cell['to'] }}</span>
                                                <span class="text-[12px] font-medium text-amber-700 dark:text-amber-300">{{ $cell['modality'] }}</span>
                                            </div>
                                        </td>
                                    @else
                                        <td wire:key="day-cell-{{ $slot }}" class="h-10 border border-gray-200 dark:border-white/10"></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="grid grid-cols-7">
                    @foreach (range(1, 7) as $day)
                        <div wire:key="month-head-{{ $day }}" class="border border-gray-200 px-2 py-2 text-center text-sm font-semibold text-gray-700 dark:border-white/10 dark:text-gray-200">
                            {{ \App\Enums\DayOfWeek::from($day)->getLabel() }}
                        </div>
                    @endforeach
                    @foreach ($this->monthGrid['weeks'] as $weekIndex => $week)
                        @foreach ($week as $cell)
                            <div wire:key="month-{{ $cell['date'] }}" class="min-h-24 border border-gray-200 p-1 align-top dark:border-white/10 {{ $cell['inMonth'] ? '' : 'bg-gray-50 dark:bg-white/5' }}">
                                <button
                                    wire:click="goToDay('{{ $cell['date'] }}')"
                                    type="button"
                                    class="mb-1 rounded px-1 text-xs font-semibold {{ $cell['inMonth'] && $cell['inPeriod'] ? 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5' : 'text-gray-400 dark:text-gray-500' }}"
                                >
                                    {{ $cell['day'] }}
                                </button>
                                <div class="flex flex-col gap-1">
                                    @foreach ($cell['blocks'] as $blockIndex => $block)
                                        <button
                                            wire:key="month-{{ $cell['date'] }}-block-{{ $blockIndex }}"
                                            wire:click="goToDay('{{ $cell['date'] }}')"
                                            type="button"
                                            class="flex flex-col rounded-md bg-amber-50 px-1.5 py-1 text-left dark:bg-amber-500/10"
                                        >
                                            <span class="truncate text-[12px] font-semibold text-gray-900 dark:text-gray-100">{{ $block['subject'] }}</span>
                                            <span class="text-[12px] text-gray-500 dark:text-gray-400">{{ $block['from'] }} · {{ $block['modality'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
