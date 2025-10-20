<?php

use App\Models\Event;
use App\Models\EventTimeSlot;
use function Livewire\Volt\{computed, state};

state(['selectedEventId' => null, 'selectedTimeSlotId' => null]);

$events = computed(function () {
    return Event::with('venue')
        ->where('status', '!=', 'cancelled')
        ->orderBy('start_date', 'desc')
        ->get();
});

$selectedEvent = computed(function () {
    if (!$this->selectedEventId) {
        return null;
    }

    return Event::with(['venue', 'eventTimeSlots', 'eventSports.sport'])
        ->find($this->selectedEventId);
});

$timeSlots = computed(function () {
    if (!$this->selectedEvent) {
        return collect();
    }

    return $this->selectedEvent->eventTimeSlots()
        ->orderBy('start_time')
        ->get();
});

$selectedTimeSlot = computed(function () {
    if (!$this->selectedTimeSlotId) {
        return null;
    }

    return EventTimeSlot::with(['event.venue'])->find($this->selectedTimeSlotId);
});

$spaceData = computed(function () {
    $event = $this->selectedEvent;
    $timeSlot = $this->selectedTimeSlot;

    if (!$event || !$event->venue) {
        return null;
    }

    $venue = $event->venue;
    $totalSportsSpace = $venue->sports_space_sqft ?? 0;

    // Use the minimum of time slot space or venue capacity to prevent showing impossible space
    $timeSlotSpace = $timeSlot?->available_space_sqft;
    $availableSpace = $timeSlotSpace
        ? min($timeSlotSpace, $totalSportsSpace)
        : $totalSportsSpace;

    // Check if time slot exceeds venue capacity (data inconsistency)
    $hasCapacityWarning = $timeSlotSpace && $timeSlotSpace > $totalSportsSpace;

    // Get sports allocated for this event (optionally filtered by time slot)
    $sportsQuery = $event->eventSports()->with('sport');

    $allocatedSports = $sportsQuery->get()->map(function ($eventSport) {
        return [
            'name' => $eventSport->sport->name ?? 'Unknown Sport',
            'space' => $eventSport->space_allocated_sqft ?? 0,
            'courts' => $eventSport->max_courts ?? 0,
        ];
    });

    $totalAllocated = $allocatedSports->sum('space');
    $remainingSpace = max(0, $availableSpace - $totalAllocated);
    $utilizationPercent = $availableSpace > 0 ? ($totalAllocated / $availableSpace) * 100 : 0;

    return [
        'venue_name' => $venue->name,
        'total_sports_space' => $totalSportsSpace,
        'available_space' => $availableSpace,
        'total_allocated' => $totalAllocated,
        'remaining_space' => $remainingSpace,
        'utilization_percent' => round($utilizationPercent, 1),
        'sports' => $allocatedSports,
        'spectator_space' => $venue->spectator_space_sqft ?? 0,
        'booth_spots' => $venue->total_booth_spots ?? 0,
        'banner_spots' => $venue->total_banner_spots ?? 0,
        'has_capacity_warning' => $hasCapacityWarning,
        'time_slot_configured_space' => $timeSlotSpace,
    ];
});

$updateEvent = function ($eventId) {
    $this->selectedEventId = $eventId;
    $this->selectedTimeSlotId = null;
};

$updateTimeSlot = function ($timeSlotId) {
    $this->selectedTimeSlotId = $timeSlotId;
};

?>

<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <flux:icon.chart-bar class="size-6 text-zinc-600 dark:text-zinc-400" />
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Venue Space Analytics</h2>
        </div>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
            Monitor venue capacity and sport space allocation across events and time slots
        </p>
    </div>

    <!-- Event Selector -->
    <div class="mb-6">
        <flux:field>
            <flux:label>Select Event</flux:label>
            <flux:select wire:model.live="selectedEventId" placeholder="Choose an event...">
                @foreach($this->events as $event)
                    <option value="{{ $event->id }}">
                        {{ $event->name }} - {{ $event->start_date->format('M j, Y') }}
                    </option>
                @endforeach
            </flux:select>
        </flux:field>
    </div>

    @if($this->selectedEvent && $this->spaceData)
        <!-- Time Slot Selector (if available) -->
        @if($this->timeSlots->isNotEmpty())
            <div class="mb-6">
                <flux:field>
                    <flux:label>Filter by Time Slot (Optional)</flux:label>
                    <flux:select wire:model.live="selectedTimeSlotId" placeholder="All time slots">
                        <option value="">All time slots</option>
                        @foreach($this->timeSlots as $slot)
                            <option value="{{ $slot->id }}">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('M j, g:i A') }} -
                                {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                ({{ number_format($slot->available_space_sqft) }} sq ft available)
                            </option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
        @endif

        <!-- Capacity Warning -->
        @if($this->spaceData['has_capacity_warning'])
            <flux:callout variant="warning" class="mb-6">
                <div class="flex items-start gap-3">
                    <flux:icon.exclamation-triangle class="size-5 flex-shrink-0" />
                    <div>
                        <div class="font-semibold mb-1">Data Inconsistency Detected</div>
                        <p class="text-sm">
                            The selected time slot is configured with {{ number_format($this->spaceData['time_slot_configured_space']) }} sq ft,
                            but the venue only has {{ number_format($this->spaceData['total_sports_space']) }} sq ft of sports space.
                            Calculations are using the venue's actual capacity.
                        </p>
                    </div>
                </div>
            </flux:callout>
        @endif

        <!-- Venue Info -->
        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <flux:icon.building-office class="size-5 text-zinc-600 dark:text-zinc-400" />
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $this->spaceData['venue_name'] }}</h3>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-zinc-600 dark:text-zinc-400">Total Sports Space:</span>
                    <p class="font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($this->spaceData['total_sports_space']) }} sq ft
                    </p>
                </div>
                <div>
                    <span class="text-zinc-600 dark:text-zinc-400">Spectator Space:</span>
                    <p class="font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($this->spaceData['spectator_space']) }} sq ft
                    </p>
                </div>
                <div>
                    <span class="text-zinc-600 dark:text-zinc-400">Booth Spots:</span>
                    <p class="font-semibold text-zinc-900 dark:text-white">
                        {{ $this->spaceData['booth_spots'] }}
                    </p>
                </div>
                <div>
                    <span class="text-zinc-600 dark:text-zinc-400">Banner Spots:</span>
                    <p class="font-semibold text-zinc-900 dark:text-white">
                        {{ $this->spaceData['banner_spots'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Space Utilization -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-zinc-900 dark:text-white">Space Utilization</h3>
                <flux:badge
                    :color="$this->spaceData['utilization_percent'] > 90 ? 'red' : ($this->spaceData['utilization_percent'] > 70 ? 'amber' : 'green')"
                    size="lg"
                >
                    {{ $this->spaceData['utilization_percent'] }}% Used
                </flux:badge>
            </div>

            <!-- Progress Bar -->
            <div class="relative h-8 bg-zinc-200 dark:bg-zinc-700 rounded-lg overflow-hidden mb-4">
                <div
                    class="absolute inset-y-0 left-0 transition-all duration-500"
                    style="width: {{ min(100, $this->spaceData['utilization_percent']) }}%; background: {{ $this->spaceData['utilization_percent'] > 90 ? 'rgb(239 68 68)' : ($this->spaceData['utilization_percent'] > 70 ? 'rgb(245 158 11)' : 'rgb(34 197 94)') }};"
                ></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xs font-semibold text-zinc-900 dark:text-white mix-blend-difference">
                        {{ number_format($this->spaceData['total_allocated']) }} / {{ number_format($this->spaceData['available_space']) }} sq ft
                    </span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900 rounded-lg p-4">
                    <div class="text-xs text-green-700 dark:text-green-400 mb-1">Available</div>
                    <div class="text-lg font-bold text-green-900 dark:text-green-300">
                        {{ number_format($this->spaceData['available_space']) }} sq ft
                    </div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 rounded-lg p-4">
                    <div class="text-xs text-blue-700 dark:text-blue-400 mb-1">Allocated</div>
                    <div class="text-lg font-bold text-blue-900 dark:text-blue-300">
                        {{ number_format($this->spaceData['total_allocated']) }} sq ft
                    </div>
                </div>
                <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 rounded-lg p-4">
                    <div class="text-xs text-amber-700 dark:text-amber-400 mb-1">Remaining</div>
                    <div class="text-lg font-bold text-amber-900 dark:text-amber-300">
                        {{ number_format($this->spaceData['remaining_space']) }} sq ft
                    </div>
                </div>
            </div>
        </div>

        <!-- Sports Breakdown -->
        @if($this->spaceData['sports']->isNotEmpty())
            <div>
                <h3 class="font-semibold text-zinc-900 dark:text-white mb-3">Sport Allocation</h3>
                <div class="space-y-3">
                    @foreach($this->spaceData['sports'] as $sport)
                        @php
                            $sportPercent = $this->spaceData['available_space'] > 0
                                ? ($sport['space'] / $this->spaceData['available_space']) * 100
                                : 0;
                        @endphp
                        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $sport['name'] }}</span>
                                    @if($sport['courts'] > 0)
                                        <flux:badge color="zinc" size="sm">{{ $sport['courts'] }} courts</flux:badge>
                                    @endif
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        {{ number_format($sport['space']) }} sq ft
                                    </span>
                                    <span class="text-zinc-600 dark:text-zinc-400">
                                        ({{ round($sportPercent, 1) }}%)
                                    </span>
                                </div>
                            </div>
                            <div class="h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-blue-500 transition-all duration-500"
                                    style="width: {{ min(100, $sportPercent) }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-8 text-center">
                <flux:icon.exclamation-triangle class="size-12 mx-auto text-zinc-400 mb-3" />
                <p class="text-zinc-600 dark:text-zinc-400">
                    No sports have been allocated space for this event yet.
                </p>
            </div>
        @endif
    @elseif($this->selectedEventId)
        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-8 text-center">
            <flux:icon.exclamation-triangle class="size-12 mx-auto text-zinc-400 mb-3" />
            <p class="text-zinc-600 dark:text-zinc-400">
                No venue data available for this event.
            </p>
        </div>
    @else
        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-8 text-center">
            <flux:icon.chart-bar class="size-12 mx-auto text-zinc-400 mb-3" />
            <p class="text-zinc-600 dark:text-zinc-400">
                Select an event above to view venue space analytics.
            </p>
        </div>
    @endif
</div>
