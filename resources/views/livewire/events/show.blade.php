<?php

use App\Models\Event;
use function Livewire\Volt\{state, computed, mount, layout};

layout('components.layouts.public');

state(['event']);

mount(function (Event $event) {
    $this->event = $event->load(['venue', 'products.division.ageGroup', 'products.eventTimeSlot', 'booths', 'banners']);
});

$sportsByCategory = computed(function () {
    return $this->event->products()
        ->where('type', 'team_registration')
        ->with(['division.ageGroup', 'eventTimeSlot'])
        ->orderBy('sport_name')
        ->orderBy('display_order')
        ->get()
        ->groupBy(function ($product) {
            return $product->sport_name;
        })
        ->map(function ($products) {
            return $products->groupBy(function ($product) {
                return $product->category ?? 'general';
            });
        });
});

$boothProduct = computed(function () {
    return $this->event->products()->where('type', 'booth')->first();
});

$availableBooths = computed(function () {
    if (!$this->boothProduct) {
        return 0;
    }
    return $this->boothProduct->max_quantity - $this->event->booths()->count();
});

$bannerProduct = computed(function () {
    return $this->event->products()->where('type', 'banner')->first();
});

$availableBanners = computed(function () {
    if (!$this->bannerProduct) {
        return 0;
    }
    return $this->bannerProduct->max_quantity - $this->event->banners()->count();
});

$hasSponsorshipOpportunities = computed(function () {
    return ($this->boothProduct && $this->availableBooths > 0) || ($this->bannerProduct && $this->availableBanners > 0);
});

$spectatorTickets = computed(function () {
    return $this->event->products()
        ->where('type', 'spectator_ticket')
        ->orderBy('display_order')
        ->orderBy('price')
        ->get()
        ->filter(function ($ticket) {
            return $ticket->current_quantity < $ticket->max_quantity;
        });
});

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <!-- Event Header -->
        <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
            <div class="container mx-auto px-4 py-8 max-w-7xl">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white mb-6 transition-colors">
                    <flux:icon.arrow-left class="size-4" />
                    <span class="text-sm font-medium">Back to Events</span>
                </a>

                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-zinc-900 dark:text-white">
                        {{ $event->name }}
                    </h1>

                    <flux:button href="{{ route('events.schedule', $event) }}" variant="primary" icon="calendar">
                        View Schedule
                    </flux:button>
                </div>

                <div class="flex flex-wrap gap-3 sm:gap-6 text-sm sm:text-base text-zinc-600 dark:text-zinc-400">
                    <div class="flex items-center gap-2">
                        <flux:icon.calendar class="size-5" />
                        <span>{{ $event->start_date->format('F j') }} - {{ $event->end_date->format('F j, Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon.map-pin class="size-5" />
                        <span>{{ $event->venue->name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:icon.layout-grid class="size-5" />
                        <span>{{ number_format($event->venue->sports_space_sqft) }} sq ft</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sports Content -->
        <div class="container mx-auto px-4 py-12 max-w-7xl">
            <!-- Sponsorship Opportunities Section -->
            @if ($this->hasSponsorshipOpportunities)
                <div class="mb-12">
                    <h2 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-white mb-6">
                        Sponsorship Opportunities
                    </h2>

                    <div class="grid sm:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Vendor Booths -->
                        @if ($this->boothProduct && $this->availableBooths > 0)
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                                <div class="flex items-center gap-3 mb-3">
                                    <flux:icon.briefcase class="size-6 text-zinc-600 dark:text-zinc-400" />
                                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
                                        Vendor Booths
                                    </h3>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                    Showcase your business at this event. Perfect for reaching our community of athletes and families.
                                </p>
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-600 dark:text-zinc-400">Available:</span>
                                        <span class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $this->availableBooths }} of {{ $this->boothProduct->max_quantity }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-600 dark:text-zinc-400">Price:</span>
                                        <span class="font-semibold text-zinc-900 dark:text-white">
                                            ${{ number_format($this->boothProduct->price, 0) }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('events.reserve-booth', ['event' => $event->slug, 'product' => $this->boothProduct->id]) }}"
                                   wire:navigate
                                   class="block w-full text-center px-4 py-2 text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 rounded-lg transition-colors">
                                    Reserve a Booth
                                </a>
                            </div>
                        @endif

                        <!-- Banner Advertisements -->
                        @if ($this->bannerProduct && $this->availableBanners > 0)
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                                <div class="flex items-center gap-3 mb-3">
                                    <flux:icon.flag class="size-6 text-zinc-600 dark:text-zinc-400" />
                                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
                                        Banner Ads
                                    </h3>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                    Maximize your brand visibility with premium banner placement throughout the venue.
                                </p>
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-600 dark:text-zinc-400">Available:</span>
                                        <span class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $this->availableBanners }} of {{ $this->bannerProduct->max_quantity }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-600 dark:text-zinc-400">Price:</span>
                                        <span class="font-semibold text-zinc-900 dark:text-white">
                                            ${{ number_format($this->bannerProduct->price, 0) }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('events.reserve-banner', ['event' => $event->slug, 'product' => $this->bannerProduct->id]) }}"
                                   wire:navigate
                                   class="block w-full text-center px-4 py-2 text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 rounded-lg transition-colors">
                                    Reserve a Banner
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Spectator Tickets Section -->
            @if ($this->spectatorTickets->isNotEmpty())
                <div class="mb-12">
                    <h2 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-white mb-6">
                        Spectator Tickets
                    </h2>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach ($this->spectatorTickets as $ticket)
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                                <div class="flex items-center gap-3 mb-3">
                                    <flux:icon.ticket class="size-6 text-zinc-600 dark:text-zinc-400" />
                                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
                                        {{ $ticket->name }}
                                    </h3>
                                </div>

                                @if ($ticket->description)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                        {{ $ticket->description }}
                                    </p>
                                @endif

                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-2xl font-bold text-zinc-900 dark:text-white">
                                            ${{ number_format($ticket->price, 0) }}
                                        </span>
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                            per ticket
                                        </span>
                                    </div>
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ number_format($ticket->max_quantity - $ticket->current_quantity) }} available
                                    </div>
                                </div>

                                <a href="{{ route('events.purchase-tickets', ['event' => $event->slug, 'product' => $ticket->id]) }}"
                                   wire:navigate
                                   class="block w-full text-center px-4 py-2 text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 rounded-lg transition-colors">
                                    Buy Tickets
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($this->sportsByCategory->isEmpty())
                <div class="text-center py-16">
                    <flux:icon.calendar class="size-16 mx-auto text-zinc-400 mb-4" />
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                        No registrations available yet
                    </h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Check back soon for registration details
                    </p>
                </div>
            @else
                <div x-data="{ search: '' }">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <flux:input
                            x-model="search"
                            placeholder="Search sports..."
                            icon="magnifying-glass"
                            class="max-w-md"
                        />
                    </div>

                    <div class="space-y-4" x-ref="sportsList">
                        @foreach ($this->sportsByCategory as $sportName => $categorizedProducts)
                            <div
                                x-data="{ expanded: {{ $sportName === 'Basketball' ? 'true' : 'false' }} }"
                                x-show="search === '' || '{{ strtolower($sportName) }}'.includes(search.toLowerCase())"
                                x-transition
                                class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden"
                            >
                                <!-- Sport Header (Clickable) -->
                                <button @click="expanded = !expanded" class="w-full flex items-center justify-between gap-4 p-6 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors border-b-2 border-amber-500">
                                    <div class="flex items-center gap-4">
                                        <h2 class="text-3xl font-bold text-zinc-900 dark:text-white">
                                            {{ $sportName }}
                                        </h2>
                                        <flux:badge color="amber" size="lg">
                                            {{ $categorizedProducts->flatten()->count() }} {{ Str::plural('Division', $categorizedProducts->flatten()->count()) }}
                                        </flux:badge>
                                    </div>
                                    <flux:icon.chevron-down class="size-6 text-zinc-600 dark:text-zinc-400 transition-transform" ::class="expanded && 'rotate-180'" />
                                </button>

                            <!-- Sport Content (Collapsible) -->
                            <div x-show="expanded" x-collapse>
                                <div class="p-6 space-y-8">
                                    @foreach (['youth', 'adult'] as $category)
                                        @if ($categorizedProducts->has($category))
                                            <div>
                                                <!-- Category Header -->
                                                <h3 class="text-xl font-semibold text-zinc-700 dark:text-zinc-300 mb-4 flex items-center gap-2">
                                                    {{ ucfirst($category) }}
                                                    <span class="text-sm font-normal text-zinc-500 dark:text-zinc-400">
                                                        ({{ $categorizedProducts[$category]->count() }} {{ Str::plural('division', $categorizedProducts[$category]->count()) }})
                                                    </span>
                                                </h3>

                                                <!-- Divisions Grid -->
                                                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    @foreach ($categorizedProducts[$category] as $product)
                                                        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:shadow-md transition-shadow">
                                                            <!-- Division Header -->
                                                            <div class="bg-zinc-50 dark:bg-zinc-900 px-4 py-3 border-b border-zinc-200 dark:border-zinc-600">
                                                                <h4 class="font-semibold text-zinc-900 dark:text-white text-sm">
                                                                    {{ str_replace(['Team Registration - ', $sportName . ' '], '', $product->name) }}
                                                                </h4>
                                                            </div>

                                                            <!-- Division Details -->
                                                            <div class="p-4">
                                                                @if ($product->description)
                                                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">
                                                                        {{ $product->description }}
                                                                    </p>
                                                                @endif

                                                                <!-- Stats -->
                                                                <div class="space-y-2 mb-4">
                                                                    @if ($product->eventTimeSlot)
                                                                        <div class="flex items-center justify-between text-sm">
                                                                            <span class="text-zinc-600 dark:text-zinc-400">Time:</span>
                                                                            <span class="font-medium text-zinc-900 dark:text-white">
                                                                                {{ $product->eventTimeSlot->start_time->format('D g:i A') }} - {{ $product->eventTimeSlot->end_time->format('g:i A') }}
                                                                            </span>
                                                                        </div>
                                                                    @endif

                                                                    <div class="flex items-center justify-between text-sm">
                                                                        <span class="text-zinc-600 dark:text-zinc-400">Entry Fee:</span>
                                                                        <span class="font-semibold text-zinc-900 dark:text-white">${{ number_format($product->price, 0) }}</span>
                                                                    </div>

                                                                    @if ($product->cash_prize)
                                                                        <div class="flex items-center justify-between text-sm">
                                                                            <span class="text-zinc-600 dark:text-zinc-400">Prize:</span>
                                                                            <span class="font-semibold text-amber-600 dark:text-amber-400">${{ number_format($product->cash_prize, 0) }}</span>
                                                                        </div>
                                                                    @endif

                                                                    @if ($product->format)
                                                                        <div class="flex items-center justify-between text-sm">
                                                                            <span class="text-zinc-600 dark:text-zinc-400">Format:</span>
                                                                            <span class="font-medium text-zinc-900 dark:text-white capitalize">{{ str_replace('_', ' ', $product->format) }}</span>
                                                                        </div>
                                                                    @endif

                                                                    <div class="flex items-center justify-between text-sm">
                                                                        <span class="text-zinc-600 dark:text-zinc-400">Spots:</span>
                                                                        <span class="font-medium text-zinc-900 dark:text-white">
                                                                            {{ $product->spotsRemaining }} / {{ $product->max_quantity }}
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <!-- Register Button -->
                                                                @if ($product->hasAvailableSpots())
                                                                    <a href="{{ route('events.register', ['event' => $event->slug, 'product' => $product->id]) }}"
                                                                       wire:navigate
                                                                       class="block w-full text-center px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 rounded-lg transition-colors">
                                                                        Register Team
                                                                    </a>
                                                                @else
                                                                    <button disabled class="block w-full text-center px-4 py-2 text-sm font-medium text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-700 rounded-lg cursor-not-allowed">
                                                                        Sold Out
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- No Results Message -->
                <template x-if="search !== ''">
                    <div x-show="!Array.from($refs.sportsList.children).some(el => el.offsetParent !== null)"
                         x-transition
                         class="text-center py-16">
                        <flux:icon.magnifying-glass class="size-16 mx-auto text-zinc-400 mb-4" />
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                            No sports found
                        </h3>
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Try searching for a different sport
                        </p>
                    </div>
                </template>
            </div>
            @endif

        </div>
</div>
