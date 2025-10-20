<?php

use App\Models\Event;
use function Livewire\Volt\{computed, layout, state};

layout('components.layouts.public');

state(['email' => '']);

$events = computed(function () {
    return Event::with(['venue', 'products', 'eventTimeSlots'])
        ->where('status', 'open')
        ->where('start_date', '>=', now())
        ->orderBy('start_date')
        ->get();
});

$sponsorPackages = computed(function () {
    return \App\Models\SponsorPackage::with(['benefits' => function ($query) {
        $query->whereRaw('is_enabled = true')->orderBy('display_order')->limit(5);
    }])
        ->whereRaw('is_template = true')
        ->whereRaw('is_active = true')
        ->ordered()
        ->get();
});

$signUp = function () {
    return $this->redirect(route('register', ['email' => $this->email]), navigate: true);
};

?>

<div>
    <!-- Hero Section -->
    <section class="px-6 w-full lg:px-7">
            <div class="mx-auto max-w-6xl">
                <div class="px-6 py-32 mx-auto md:text-center md:px-4">
                    <h1 class="text-4xl font-extrabold tracking-tight leading-none text-white sm:text-5xl md:text-6xl xl:text-7xl">
                        <span class="block">The Ultimate</span>
                        <span class="inline-block relative mt-3 text-white">Sports Weekend</span>
                    </h1>
                    <p class="mx-auto mt-6 text-sm text-left text-stone-200 md:text-center md:mt-12 sm:text-base md:max-w-xl md:text-lg xl:text-xl">
                        From pickleball to futsal, cricket to volleyball - gather your crew and compete in the sports you love, all in one incredible weekend. Every registration supports families in need.
                    </p>
                    <form wire:submit="signUp" class="flex overflow-hidden relative items-center mx-auto mt-12 text-left rounded-full border border-stone-200/20 md:max-w-md md:text-center">
                        <input
                            type="email"
                            wire:model="email"
                            placeholder="Email Address"
                            class="px-6 py-2 w-full h-12 font-medium rounded-l-full text-stone-800 bg-white/90 focus:outline-none"
                        />
                        <span class="block relative top-0 right-0">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center px-8 w-32 h-12 text-base font-bold leading-6 text-white rounded-r-full border border-l-0 border-transparent transition duration-150 ease-in-out cursor-pointer bg-stone-800/40 hover:bg-stone-700/50 focus:outline-none active:bg-stone-700"
                            >
                                Sign Up
                            </button>
                        </span>
                    </form>
                    <div class="mt-8 text-sm text-stone-300">By signing up, you agree to our terms and services.</div>

                    <!-- Trust Indicators -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 pt-12 mt-12 border-t border-stone-700">
                        <div>
                            <div class="text-3xl md:text-4xl font-bold text-amber-400 mb-1">5,000+</div>
                            <div class="text-sm text-stone-300">Athletes</div>
                        </div>
                        <div>
                            <div class="text-3xl md:text-4xl font-bold text-amber-400 mb-1">12+</div>
                            <div class="text-sm text-stone-300">Sports Offered</div>
                        </div>
                        <div>
                            <div class="text-3xl md:text-4xl font-bold text-amber-400 mb-1">All</div>
                            <div class="text-sm text-stone-300">Skill Levels</div>
                        </div>
                        <div>
                            <div class="text-3xl md:text-4xl font-bold text-amber-400 mb-1">100%</div>
                            <div class="text-sm text-stone-300">Give Back</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charity Callout -->
        <div class="border-y border-stone-700/50 py-12 bg-stone-900/50">
            <div class="container mx-auto px-4 max-w-4xl text-center">
                <flux:icon.heart class="size-10 mx-auto text-amber-400 mb-3" />
                <h2 class="text-xl md:text-2xl font-bold mb-2 text-white">Play for a Purpose</h2>
                <p class="text-stone-300">
                    Every registration helps support families affected by domestic violence. When you compete, you're making a difference in your community.
                </p>
            </div>
        </div>

        <!-- Value Propositions -->
        <div class="container mx-auto px-4 py-16 md:py-20 max-w-6xl">
            <div class="grid md:grid-cols-3 gap-8 md:gap-12">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center size-16 bg-amber-400/10 rounded-xl mb-6">
                        <flux:icon.layout-grid class="size-8 text-amber-400" />
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Your Sports Festival Awaits</h3>
                    <p class="text-stone-300">
                        From basketball to volleyball, softball to pickleball - play all your favorite sports at world-class venues in one amazing weekend.
                    </p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center size-16 bg-amber-400/10 rounded-xl mb-6">
                        <flux:icon.currency-dollar class="size-8 text-amber-400" />
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Play Hard, Win Big</h3>
                    <p class="text-stone-300">
                        Cash prizes for champions, competitive divisions for serious players, and fun categories for everyone else. Come to compete or just have a great time.
                    </p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center size-16 bg-amber-400/10 rounded-xl mb-6">
                        <flux:icon.user-group class="size-8 text-amber-400" />
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-white">Everyone's Invited</h3>
                    <p class="text-stone-300">
                        Youth, adult, and senior divisions with skill levels for beginners to pros. Bring your team, your family, or come solo - there's a spot for you.
                    </p>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="bg-stone-800/30 border-y border-stone-700/50 py-16 md:py-20">
            <div class="container mx-auto px-4 max-w-6xl">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">How It Works</h2>
                    <p class="text-lg text-stone-300">
                        Getting started is simple - just three easy steps
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8 md:gap-12">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="size-12 flex items-center justify-center text-lg font-bold rounded-full bg-amber-400 text-stone-900">1</div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-white">Find Your Event</h3>
                            <p class="text-stone-300">
                                Check out upcoming tournaments and pick the sports that excite you. Solo or with your team - your choice.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="size-12 flex items-center justify-center text-lg font-bold rounded-full bg-amber-400 text-stone-900">2</div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-white">Sign Up & Rally Your Crew</h3>
                            <p class="text-stone-300">
                                Select your division and complete registration in minutes. Invite teammates, set up your roster, and get ready to play.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="size-12 flex items-center justify-center text-lg font-bold rounded-full bg-amber-400 text-stone-900">3</div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-white">Play & Make Memories</h3>
                            <p class="text-stone-300">
                                Show up, have fun, and give it your all. With professional facilities and great competition, every game is a win.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sponsorship Packages -->
        <div class="container mx-auto px-4 py-16 md:py-20 max-w-6xl">
            <div class="text-center mb-12">
                <flux:icon.star class="size-12 mx-auto text-amber-400 mb-4" />
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">Power the Event - Become a Sponsor</h2>
                <p class="text-lg text-stone-300 max-w-2xl mx-auto">
                    Amplify your brand while supporting our community. Choose a sponsorship package that aligns with your marketing goals and makes a lasting impact.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 md:gap-8 mb-8">
                @foreach ($this->sponsorPackages as $package)
                    <div class="relative @if($package->tier === 'gold') bg-gradient-to-b from-amber-500/10 to-stone-800/50 border-amber-500/50 hover:border-amber-400 hover:shadow-lg hover:shadow-amber-500/20 @elseif($package->tier === 'silver') bg-stone-800/50 border-stone-600 hover:border-stone-500 @else bg-stone-800/50 border-orange-900/50 hover:border-orange-800 @endif rounded-xl border-2 p-8 transition-all">
                        @if($package->tier === 'gold')
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                                <span class="inline-flex items-center px-4 py-1 text-xs font-bold uppercase tracking-wider rounded-full bg-amber-500 text-stone-900">
                                    Most Popular
                                </span>
                            </div>
                        @endif
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center justify-center size-16 rounded-full mb-4 @if($package->tier === 'gold') bg-amber-400/20 @elseif($package->tier === 'silver') bg-stone-400/20 @else bg-orange-900/20 @endif">
                                @if($package->tier === 'gold')
                                    <flux:icon.star class="size-8 text-amber-400" />
                                @elseif($package->tier === 'silver')
                                    <flux:icon.sparkles class="size-8 text-stone-400" />
                                @else
                                    <flux:icon.check-circle class="size-8 text-orange-700" />
                                @endif
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">{{ $package->name }}</h3>
                            <div class="text-4xl font-extrabold mb-1 @if($package->tier === 'gold') text-amber-400 @elseif($package->tier === 'silver') text-stone-300 @else text-orange-700 @endif">
                                ${{ number_format($package->price, 0) }}
                            </div>
                            <p class="text-sm text-stone-400">per sport</p>
                        </div>
                        <ul class="space-y-3 mb-8 text-sm">
                            @foreach ($package->benefits->take(4) as $benefit)
                                <li class="flex items-start gap-2 text-stone-300">
                                    <flux:icon.check class="size-5 flex-shrink-0 mt-0.5 @if($package->tier === 'gold') text-amber-400 @elseif($package->tier === 'silver') text-stone-400 @else text-orange-700 @endif" />
                                    <span>
                                        @if($benefit->quantity > 1)
                                            {{ $benefit->quantity }} {{ $benefit->name }}
                                        @else
                                            {{ $benefit->name }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                            @if($package->benefits->count() > 4)
                                <li class="flex items-start gap-2 text-stone-300">
                                    <flux:icon.check class="size-5 flex-shrink-0 mt-0.5 @if($package->tier === 'gold') text-amber-400 @elseif($package->tier === 'silver') text-stone-400 @else text-orange-700 @endif" />
                                    <span>And {{ $package->benefits->count() - 4 }} more premium {{ Str::plural('benefit', $package->benefits->count() - 4) }}...</span>
                                </li>
                            @endif
                        </ul>
                        <a href="{{ route('sponsors.browse') }}" class="block w-full text-center px-6 py-3 font-bold rounded-lg transition-colors @if($package->tier === 'gold') bg-amber-500 text-stone-900 hover:bg-amber-400 @elseif($package->tier === 'silver') bg-stone-700 text-white hover:bg-stone-600 @else bg-orange-900 text-white hover:bg-orange-800 @endif">
                            View Package
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="text-center">
                <p class="text-sm text-stone-400">
                    Join our growing community of sponsors making a difference
                </p>
            </div>
        </div>

        <!-- Upcoming Events Preview -->
        <div class="container mx-auto px-4 py-16 md:py-20 max-w-6xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">Upcoming Events</h2>
                <p class="text-lg text-stone-300">
                    Find your next sports adventure - spots are filling up fast!
                </p>
            </div>

            <!-- Events List -->
            @if ($this->events->isEmpty())
                <div class="text-center py-16">
                    <flux:icon.calendar class="size-16 mx-auto text-stone-500 mb-4" />
                    <h3 class="text-xl font-semibold mb-2 text-white">No upcoming events</h3>
                    <p class="text-stone-300">
                        Check back soon for new tournaments!
                    </p>
                </div>
            @else
                <div class="grid gap-6 max-w-5xl mx-auto">
                    @foreach ($this->events->take(3) as $event)
                        <a href="{{ route('events.show', $event->slug) }}"
                           wire:navigate
                           class="group block bg-stone-800/50 rounded-xl shadow-sm hover:shadow-md transition-all overflow-hidden border border-stone-700 hover:border-amber-400/50">

                            <div class="p-6 md:p-8">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                                    <div>
                                        <h3 class="text-2xl md:text-3xl font-bold text-white group-hover:text-amber-400 transition-colors mb-2">
                                            {{ $event->name }}
                                        </h3>
                                        <div class="flex flex-wrap gap-4 text-stone-300">
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
                                            <div class="text-sm text-stone-300">
                                                {{ $event->products()->where('type', 'team_registration')->distinct('sport_name')->count('sport_name') }} Sports
                                            </div>
                                            <div class="text-sm text-stone-300">
                                                {{ $event->products()->where('type', 'team_registration')->count() }} Divisions
                                            </div>
                                        </div>
                                        <flux:icon.chevron-right class="size-6 text-stone-400 group-hover:text-amber-400 transition-colors" />
                                    </div>
                                </div>

                                <!-- Sports Preview -->
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($event->products()->where('type', 'team_registration')->distinct('sport_name')->pluck('sport_name') as $sport)
                                        <span class="px-3 py-1 text-xs font-medium rounded-full bg-stone-700 text-stone-200">{{ $sport }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($this->events->count() > 3)
                    <div class="text-center mt-8">
                        <a href="{{ route('events.index') }}" class="inline-flex justify-center items-center px-6 py-3 font-medium leading-6 text-center bg-stone-800 rounded-full border border-stone-700 transition duration-150 ease-in-out text-white hover:bg-stone-700 focus:outline-none focus:border-amber-400">
                            View All Events
                        </a>
                    </div>
                @endif
            @endif
        </div>

        <!-- Final CTA Section -->
        <div class="border-t border-stone-700/50 py-16 md:py-20">
            <div class="container mx-auto px-4 max-w-4xl text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">Ready to Play?</h2>
                <p class="text-lg text-stone-300 mb-8">
                    Join thousands of athletes at the ultimate multi-sport weekend. Compete, connect, and give back to your community.
                </p>
                <a href="{{ route('events.index') }}" class="inline-flex justify-center items-center px-8 py-3 font-bold leading-6 text-center bg-amber-500 rounded-full border border-transparent transition duration-150 ease-in-out text-white hover:bg-amber-600 focus:outline-none active:bg-amber-700">
                    Browse Upcoming Events
                </a>
            </div>
        </div>
</div>
