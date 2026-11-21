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
            <div class="flex flex-col gap-1">
                <label for="fromTime" class="text-sm font-medium text-gray-700 dark:text-gray-200">Desde</label>
                <x-filament::input id="fromTime" type="time" wire:model.live="fromTime" />
            </div>
            <div class="flex flex-col gap-1">
                <label for="toTime" class="text-sm font-medium text-gray-700 dark:text-gray-200">Hasta</label>
                <x-filament::input id="toTime" type="time" wire:model.live="toTime" />
            </div>
        </div>

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
    </div>
</x-filament-panels::page>
