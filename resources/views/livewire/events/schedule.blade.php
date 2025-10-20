<?php

use App\Models\Event;
use function Livewire\Volt\{state, computed, mount, layout};

layout('components.layouts.public');

state(['event', 'selectedSport' => 'all']);

mount(function (Event $event) {
    $this->event = $event->load(['products.eventTimeSlot', 'products.division']);
});

$timeSlots = computed(function () {
    return $this->event->products()
        ->whereNotNull('event_time_slot_id')
        ->with('eventTimeSlot')
        ->get()
        ->pluck('eventTimeSlot')
        ->unique('id')
        ->sortBy('start_time')
        ->values();
});

$sports = computed(function () {
    return $this->event->products()
        ->where('type', 'team_registration')
        ->whereNotNull('sport_name')
        ->get()
        ->pluck('sport_name')
        ->unique()
        ->sort()
        ->values();
});

$scheduleData = computed(function () {
    $data = [];

    foreach ($this->timeSlots as $timeSlot) {
        $timeSlotData = [
            'time_slot' => $timeSlot,
            'sports' => []
        ];

        foreach ($this->sports as $sport) {
            if ($this->selectedSport !== 'all' && $this->selectedSport !== $sport) {
                continue;
            }

            $products = $this->event->products()
                ->where('type', 'team_registration')
                ->where('sport_name', $sport)
                ->where('event_time_slot_id', $timeSlot->id)
                ->with('division')
                ->get();

            if ($products->isNotEmpty()) {
                $timeSlotData['sports'][$sport] = $products;
            }
        }

        if (!empty($timeSlotData['sports'])) {
            $data[] = $timeSlotData;
        }
    }

    return $data;
});

// Sport color mapping for visual differentiation
$sportColors = [
    'Basketball' => 'orange',
    'Futsal' => 'green',
    'Volleyball' => 'blue',
    'Cricket' => 'purple',
    'Dodgeball' => 'red',
    'Field Hockey' => 'pink',
    'Pickleball' => 'yellow',
    'Wiffle Ball' => 'cyan',
    'Lacrosse' => 'indigo',
    'Rollerhockey' => 'gray',
    'Handball' => 'lime',
    'Cornhole' => 'amber',
];

?>

<div>
    <!-- Header -->
    <div class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <div>
                    <flux:heading size="xl" class="mb-2">Event Schedule</flux:heading>
                    <flux:text>{{ $event->name }}</flux:text>
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                        {{ $event->start_date->format('F j') }} - {{ $event->end_date->format('j, Y') }}
                    </flux:text>
                </div>
                <flux:button variant="ghost" href="{{ route('events.show', $event) }}" icon="arrow-left">
                    Back to Event
                </flux:button>
            </div>

            <!-- Sport Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <flux:text class="font-medium">Filter by Sport:</flux:text>
                <flux:select wire:model.live="selectedSport" class="w-full sm:w-64">
                    <option value="all">All Sports</option>
                    @foreach ($this->sports as $sport)
                        <option value="{{ $sport }}">{{ $sport }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>
    </div>

    <!-- Schedule Timeline -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        @if (empty($this->scheduleData))
            <div class="text-center py-12">
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    No scheduled activities found for this event.
                </flux:text>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($this->scheduleData as $timeSlotData)
                    @php
                        $timeSlot = $timeSlotData['time_slot'];
                        $startTime = $timeSlot->start_time;
                        $endTime = $timeSlot->end_time;
                    @endphp

                    <!-- Time Slot Card -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <!-- Time Header -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <div>
                                    <flux:heading size="lg" class="mb-1">
                                        {{ $startTime->format('l, F j, Y') }}
                                    </flux:heading>
                                    <flux:text class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                        {{ $startTime->format('g:i A') }} - {{ $endTime->format('g:i A') }}
                                    </flux:text>
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ count($timeSlotData['sports']) }} {{ Str::plural('sport', count($timeSlotData['sports'])) }}
                                </div>
                            </div>
                        </div>

                        <!-- Sports Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($timeSlotData['sports'] as $sportName => $products)
                                    <div class="space-y-3">
                                        <!-- Sport Name Header -->
                                        <div class="flex items-center gap-2 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                            <div class="w-1 h-6 rounded-full bg-{{ $sportColors[$sportName] ?? 'blue' }}-500"></div>
                                            <flux:heading size="sm" class="font-semibold">
                                                {{ $sportName }}
                                            </flux:heading>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                ({{ $products->count() }})
                                            </span>
                                        </div>

                                        <!-- Divisions List -->
                                        <div class="space-y-2">
                                            @foreach ($products as $product)
                                                <a href="{{ route('events.register', [$event, $product]) }}"
                                                   class="block group">
                                                    <div class="flex items-center justify-between p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-{{ $sportColors[$sportName] ?? 'blue' }}-500 dark:hover:border-{{ $sportColors[$sportName] ?? 'blue' }}-500 hover:bg-{{ $sportColors[$sportName] ?? 'blue' }}-50 dark:hover:bg-{{ $sportColors[$sportName] ?? 'blue' }}-900/20 transition-all cursor-pointer">
                                                        <span class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-{{ $sportColors[$sportName] ?? 'blue' }}-600 dark:group-hover:text-{{ $sportColors[$sportName] ?? 'blue' }}-400">
                                                            {{ $product->division?->name ?? $product->name }}
                                                        </span>
                                                        <svg class="w-4 h-4 text-zinc-400 group-hover:text-{{ $sportColors[$sportName] ?? 'blue' }}-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                        </svg>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
