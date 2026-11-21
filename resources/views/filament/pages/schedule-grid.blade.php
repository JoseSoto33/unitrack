<x-filament-panels::page>
    <div wire:poll.5s class="flex flex-col gap-4">
        <div class="max-w-sm">
            <x-filament::input.select wire:model.live="periodId">
                @foreach ($this->periods as $id => $label)
                    <option wire:key="period-{{ $id }}" value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </x-filament::input.select>
        </div>

        @if (count($this->grid['slots']) === 0)
            <x-filament::section>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No hay bloques de horario en este período. Agrega bloques desde «Horarios» para verlos aquí.
                </p>
            </x-filament::section>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Hora</th>
                            @foreach ($this->grid['days'] as $day)
                                <th wire:key="day-{{ $day }}" class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    {{ \App\Enums\DayOfWeek::from($day)->getLabel() }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->grid['slots'] as $slot)
                            <tr wire:key="slot-{{ $slot }}" class="border-b border-gray-100 dark:border-white/5">
                                <td class="px-3 py-1 align-top text-xs font-medium text-gray-500 dark:text-gray-400">{{ $slot }}</td>
                                @foreach ($this->grid['days'] as $day)
                                    @php($cell = $this->grid['cells'][$day][$slot] ?? null)
                                    @if ($cell === 'covered')
                                        {{-- celda cubierta por el rowspan de un bloque anterior --}}
                                    @elseif ($cell instanceof \App\Models\Schedule)
                                        @php($span = (int) ((strtotime($cell->ends_at) - strtotime($cell->starts_at)) / 1800))
                                        <td wire:key="cell-{{ $day }}-{{ $slot }}" rowspan="{{ $span }}" class="border-l border-gray-100 px-2 py-1 align-top dark:border-white/5">
                                            <div class="flex flex-col gap-1 rounded-lg p-2 {{ $cell->effective_modality === \App\Enums\SubjectModality::Online ? 'bg-sky-50 dark:bg-sky-500/10' : 'bg-amber-50 dark:bg-amber-500/10' }}">
                                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $cell->subject->name }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ substr($cell->starts_at, 0, 5) }}–{{ substr($cell->ends_at, 0, 5) }}</span>
                                                <span class="text-xs font-medium {{ $cell->effective_modality === \App\Enums\SubjectModality::Online ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-300' }}">
                                                    {{ $cell->effective_modality->getLabel() }}
                                                </span>
                                            </div>
                                        </td>
                                    @else
                                        <td wire:key="cell-{{ $day }}-{{ $slot }}" class="border-l border-gray-100 dark:border-white/5"></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
