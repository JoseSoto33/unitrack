<x-filament-panels::page>
    <div class="flex flex-col gap-4">
        <div class="flex gap-1 rounded-lg border border-gray-200 p-1 dark:border-white/10">
            <button
                wire:click="$set('tab', 'grid')"
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium {{ $tab === 'grid' ? 'bg-primary-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5' }}"
            >
                Cuadro de horario
            </button>
            <button
                wire:click="$set('tab', 'list')"
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium {{ $tab === 'list' ? 'bg-primary-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5' }}"
            >
                Bloques agregados
            </button>
        </div>

        @if ($tab === 'list')
            {{ $this->table }}
        @else
            <livewire:schedule-grid />
        @endif
    </div>
</x-filament-panels::page>
