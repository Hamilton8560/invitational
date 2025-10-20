<?php

use function Livewire\Volt\{computed, layout};

layout('components.layouts.app.sidebar');

$teams = computed(function () {
    return auth()->user()->ownedTeams()
        ->with(['event', 'product', 'sale', 'playerInvitations'])
        ->orderBy('created_at', 'desc')
        ->get();
});

$spectatorTickets = computed(function () {
    return auth()->user()->sales()
        ->whereHas('product', fn($q) => $q->where('type', 'spectator_ticket'))
        ->with(['product', 'event'])
        ->orderBy('created_at', 'desc')
        ->get();
});

$booths = computed(function () {
    return auth()->user()->booths()
        ->with(['event', 'product', 'sale'])
        ->orderBy('created_at', 'desc')
        ->get();
});

$banners = computed(function () {
    return auth()->user()->banners()
        ->with(['event', 'product', 'sale'])
        ->orderBy('created_at', 'desc')
        ->get();
});

$individualRegistrations = computed(function () {
    return auth()->user()->individualPlayerRegistrations()
        ->with(['event', 'product', 'sale'])
        ->orderBy('created_at', 'desc')
        ->get();
});

?>

<div class="max-w-7xl mx-auto space-y-8 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">My Purchases</h1>
                <p class="text-zinc-600 dark:text-zinc-400 mt-1">Manage your registrations, tickets, and sponsorships</p>
            </div>
            <flux:button variant="primary" :href="route('home')" wire:navigate>
                <flux:icon.magnifying-glass class="size-4" />
                Browse Events
            </flux:button>
        </div>

        <!-- Admin-Only: Venue Space Analytics -->
        @if(auth()->user()->hasRole(['super_admin', 'admin']))
            <div class="border-t-4 border-blue-500 dark:border-blue-600">
                @livewire('dashboard.admin-space-visualization')
            </div>
        @endif

        <!-- Team Registrations -->
        @if ($this->teams->isNotEmpty())
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon.user-group class="size-5 text-zinc-600 dark:text-zinc-400" />
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Team Registrations</h2>
                </div>
                <div class="grid gap-4">
                    @foreach ($this->teams as $team)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $team->name }}</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $team->event->name }}</p>
                                </div>
                                <flux:badge :color="$team->sale?->status === 'completed' ? 'green' : 'amber'">
                                    {{ ucfirst($team->sale?->status ?? 'pending') }}
                                </flux:badge>
                            </div>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm mb-4">
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Division:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1 break-words">{{ $team->product->name }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Event Date:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">{{ $team->event->start_date->format('M j, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Amount:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">${{ number_format($team->sale?->total_amount ?? 0, 2) }}</span>
                                </div>
                            </div>

                            <!-- Roster Status -->
                            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.users class="size-4 text-zinc-600 dark:text-zinc-400" />
                                        <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                            Roster: {{ $team->current_players }} / {{ $team->max_players }} Players
                                        </span>
                                    </div>
                                    @if($team->current_players < $team->max_players)
                                        <flux:badge color="amber" size="sm">
                                            {{ $team->max_players - $team->current_players }} spots remaining
                                        </flux:badge>
                                    @else
                                        <flux:badge color="green" size="sm">
                                            Full roster
                                        </flux:badge>
                                    @endif
                                </div>

                                @if($team->playerInvitations->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach($team->playerInvitations as $invitation)
                                            <div class="flex items-center justify-between text-sm bg-zinc-50 dark:bg-zinc-900 rounded px-3 py-2">
                                                <span class="text-zinc-900 dark:text-white">
                                                    {{ $invitation->first_name }} {{ $invitation->last_name }}
                                                </span>
                                                @if($invitation->accepted)
                                                    <flux:badge color="green" size="sm">
                                                        <flux:icon.check class="size-3" />
                                                        Accepted
                                                    </flux:badge>
                                                @else
                                                    <flux:badge color="zinc" size="sm">
                                                        <flux:icon.clock class="size-3" />
                                                        Pending
                                                    </flux:badge>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 italic">
                                        No players added yet. Add players to complete your roster.
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Spectator Tickets -->
        @if ($this->spectatorTickets->isNotEmpty())
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon.ticket class="size-5 text-zinc-600 dark:text-zinc-400" />
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Spectator Tickets</h2>
                </div>
                <div class="grid gap-4">
                    @foreach ($this->spectatorTickets as $sale)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $sale->product->name }}</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $sale->event->name }}</p>
                                </div>
                                <flux:badge :color="$sale->status === 'completed' ? 'green' : 'amber'">
                                    {{ ucfirst($sale->status) }}
                                </flux:badge>
                            </div>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Quantity:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">{{ $sale->quantity }} {{ Str::plural('ticket', $sale->quantity) }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Event Date:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">{{ $sale->event->start_date->format('M j, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Total:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">${{ number_format($sale->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Sponsorships (Booths & Banners) -->
        @if ($this->booths->isNotEmpty() || $this->banners->isNotEmpty())
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon.star class="size-5 text-zinc-600 dark:text-zinc-400" />
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Sponsorships</h2>
                </div>
                <div class="grid gap-4">
                    @foreach ($this->booths as $booth)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <flux:icon.briefcase class="size-5 text-zinc-600 dark:text-zinc-400" />
                                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Vendor Booth #{{ $booth->booth_number }}</h3>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $booth->event->name }}</p>
                                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mt-1">{{ $booth->company_name }}</p>
                                </div>
                                <flux:badge :color="$booth->sale?->status === 'completed' ? 'green' : 'amber'">
                                    {{ ucfirst($booth->sale?->status ?? 'pending') }}
                                </flux:badge>
                            </div>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Contact:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1 break-words">{{ $booth->contact_name }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Event Date:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">{{ $booth->event->start_date->format('M j, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Amount:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">${{ number_format($booth->sale?->total_amount ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($this->banners as $banner)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <flux:icon.flag class="size-5 text-zinc-600 dark:text-zinc-400" />
                                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Banner Advertisement</h3>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $banner->event->name }}</p>
                                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mt-1">{{ $banner->company_name }}</p>
                                    @if ($banner->banner_location)
                                        <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">Location: {{ $banner->banner_location }}</p>
                                    @endif
                                </div>
                                <flux:badge :color="$banner->sale?->status === 'completed' ? 'green' : 'amber'">
                                    {{ ucfirst($banner->sale?->status ?? 'pending') }}
                                </flux:badge>
                            </div>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Contact:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1 break-words">{{ $banner->contact_name }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Event Date:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">{{ $banner->event->start_date->format('M j, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Amount:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">${{ number_format($banner->sale?->total_amount ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Individual Registrations -->
        @if ($this->individualRegistrations->isNotEmpty())
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon.user class="size-5 text-zinc-600 dark:text-zinc-400" />
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Individual Registrations</h2>
                </div>
                <div class="grid gap-4">
                    @foreach ($this->individualRegistrations as $registration)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $registration->player_name }}</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $registration->event->name }}</p>
                                </div>
                                <flux:badge :color="$registration->sale?->status === 'completed' ? 'green' : 'amber'">
                                    {{ ucfirst($registration->sale?->status ?? 'pending') }}
                                </flux:badge>
                            </div>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Division:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1 break-words">{{ $registration->product->name }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Event Date:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">{{ $registration->event->start_date->format('M j, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-600 dark:text-zinc-400">Amount:</span>
                                    <span class="text-zinc-900 dark:text-white font-medium ml-1">${{ number_format($registration->sale?->total_amount ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if ($this->teams->isEmpty() && $this->spectatorTickets->isEmpty() && $this->booths->isEmpty() && $this->banners->isEmpty() && $this->individualRegistrations->isEmpty())
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                <flux:icon.shopping-bag class="size-16 mx-auto text-zinc-400 mb-4" />
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-2">
                    No Purchases Yet
                </h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                    Browse our upcoming events to register your team, purchase tickets, or explore sponsorship opportunities.
                </p>
                <flux:button variant="primary" :href="route('home')" wire:navigate>
                    <flux:icon.magnifying-glass class="size-4" />
                    Browse Events
                </flux:button>
            </div>
        @endif
</div>
