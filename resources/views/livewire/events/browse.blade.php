<?php

use App\Models\Event;
use function Livewire\Volt\{state, computed, layout};

layout('components.layouts.public');

$events = computed(function () {
    return Event::with(['venue', 'products', 'eventTimeSlots'])
        ->where('status', 'open')
        ->where('start_date', '>=', now())
        ->orderBy('start_date')
        ->get();
});

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <div class="container mx-auto px-4 py-12 max-w-7xl">
            <!-- Header -->
            <div class="mb-12 text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-zinc-900 dark:text-white mb-4">
                    Upcoming Events
                </h1>
                <p class="text-lg text-zinc-600 dark:text-zinc-400">
                    Register for multi-sport tournaments with cash prizes
                </p>
            </div>

            <!-- Events Grid -->
            @if ($this->events->isEmpty())
                <div class="text-center py-16">
                    <flux:icon.calendar class="size-16 mx-auto text-zinc-400 mb-4" />
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                        No upcoming events
                    </h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Check back soon for new tournaments!
                    </p>
                </div>
            @else
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($this->events as $event)
                        <a href="{{ route('events.show', $event->slug) }}"
                           class="group bg-white dark:bg-zinc-800 rounded-xl shadow-sm hover:shadow-lg transition-all overflow-hidden border border-zinc-200 dark:border-zinc-700">

                            <!-- Event Image Placeholder -->
                            <div class="aspect-video bg-gradient-to-br from-amber-400 to-orange-600 relative overflow-hidden">
                                <div class="absolute inset-0 bg-black/10"></div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="flex items-center gap-2 text-white">
                                        <flux:icon.map-pin class="size-4" />
                                        <span class="text-sm font-medium">{{ $event->venue->name }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Event Content -->
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                        {{ $event->name }}
                                    </h3>
                                </div>

                                <!-- Event Details -->
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                                        <flux:icon.calendar class="size-4" />
                                        <span class="text-sm">
                                            {{ $event->start_date->format('M j') }} - {{ $event->end_date->format('M j, Y') }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                                        <flux:icon.users class="size-4" />
                                        <span class="text-sm">
                                            {{ $event->products()->where('type', 'team_registration')->distinct('sport_name')->count('sport_name') }} Sports Available
                                        </span>
                                    </div>

                                    @if ($event->venue->sports_space_sqft)
                                        <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                                            <flux:icon.layout-grid class="size-4" />
                                            <span class="text-sm">
                                                {{ number_format($event->venue->sports_space_sqft) }} sq ft venue
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Featured Sports -->
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach ($event->products()->distinct('sport_name')->pluck('sport_name')->take(3) as $sport)
                                        <flux:badge color="zinc" size="sm">{{ $sport }}</flux:badge>
                                    @endforeach
                                </div>

                                <!-- CTA -->
                                <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                                    <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">
                                        Register Now
                                    </span>
                                    <flux:icon.arrow-right class="size-5 text-zinc-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 group-hover:translate-x-1 transition-all" />
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
</div>
