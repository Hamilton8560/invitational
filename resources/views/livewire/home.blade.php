<?php

use App\Models\Event;
use function Livewire\Volt\{computed, layout};

layout('components.layouts.public');

$events = computed(function () {
    return Event::with(['venue', 'products' => function ($query) {
        $query->where('type', 'team_registration');
    }])
        ->where('status', 'open')
        ->where('start_date', '>=', now())
        ->orderBy('start_date')
        ->get()
        ->map(function ($event) {
            $event->sports_count = $event->products->unique('sport_name')->count();
            $event->divisions_count = $event->products->count();
            $event->sports_list = $event->products->unique('sport_name')->pluck('sport_name');
            return $event;
        });
});

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <div class="container mx-auto px-4 py-12 max-w-7xl">
        <!-- Header -->
        <div class="mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-zinc-900 dark:text-white mb-4">
                Upcoming Events
            </h1>
            <p class="text-lg text-zinc-600 dark:text-zinc-400">
                Elite multi-sport tournaments with competitive divisions and substantial cash prizes
            </p>
        </div>

        <!-- Events List -->
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
            <div class="space-y-6">
                @foreach ($this->events as $event)
                    <a href="{{ route('events.show', $event->slug) }}"
                       wire:navigate
                       class="group block bg-white dark:bg-zinc-800 rounded-xl shadow-sm hover:shadow-md transition-all overflow-hidden border border-zinc-200 dark:border-zinc-700">

                        <div class="p-6 md:p-8">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                                <div>
                                    <h2 class="text-2xl md:text-3xl font-bold text-zinc-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors mb-2">
                                        {{ $event->name }}
                                    </h2>
                                    <div class="flex flex-wrap gap-4 text-zinc-600 dark:text-zinc-400">
                                        <div class="flex items-center gap-2">
                                            <flux:icon.calendar class="size-4" />
                                            <span class="text-sm">{{ $event->start_date->format('M j') }} - {{ $event->end_date->format('M j, Y') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <flux:icon.map-pin class="size-4" />
                                            <span class="text-sm">{{ $event->venue->name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $event->sports_count }} Sports
                                        </div>
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $event->divisions_count }} Divisions
                                        </div>
                                    </div>
                                    <flux:icon.chevron-right class="size-6 text-zinc-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors" />
                                </div>
                            </div>

                            <!-- Sports Preview -->
                            <div class="flex flex-wrap gap-2">
                                @foreach ($event->sports_list as $sport)
                                    <flux:badge color="zinc" size="sm">{{ $sport }}</flux:badge>
                                @endforeach
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
        </div>
</div>
