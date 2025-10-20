<?php

use App\Models\SponsorPackage;
use function Livewire\Volt\{computed, layout};

layout('components.layouts.public');

$packages = computed(function () {
    return SponsorPackage::with('benefits')
        ->whereRaw('is_template = true')
        ->whereRaw('is_active = true')
        ->ordered()
        ->get();
});

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <!-- Hero Section -->
    <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
        <div class="container mx-auto px-4 py-12 max-w-7xl">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white mb-6 transition-colors">
                <flux:icon.arrow-left class="size-4" />
                <span class="text-sm font-medium">Back to Home</span>
            </a>

            <div class="text-center max-w-3xl mx-auto">
                <flux:icon.star class="size-16 mx-auto text-amber-400 mb-6" />
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-zinc-900 dark:text-white mb-4">
                    Sponsor Our Events
                </h1>
                <p class="text-lg text-zinc-600 dark:text-zinc-400">
                    Amplify your brand while supporting our community. Choose a sponsorship package that aligns with your marketing goals and makes a lasting impact on thousands of athletes and families.
                </p>
            </div>
        </div>
    </div>

    <!-- Packages Section -->
    <div class="container mx-auto px-4 py-12 max-w-7xl">
        @if ($this->packages->isEmpty())
            <div class="text-center py-16">
                <flux:icon.star class="size-16 mx-auto text-zinc-300 dark:text-zinc-600 mb-4" />
                <h3 class="text-xl font-semibold mb-2 text-zinc-900 dark:text-white">No sponsorship packages available</h3>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Check back soon for new sponsorship opportunities!
                </p>
            </div>
        @else
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                @foreach ($this->packages as $package)
                    <div class="relative bg-white dark:bg-zinc-800 rounded-xl border-2 @if($package->tier === 'gold') border-amber-500/50 dark:border-amber-500/30 shadow-lg shadow-amber-500/10 @elseif($package->tier === 'silver') border-zinc-400 dark:border-zinc-600 @else border-orange-900/50 dark:border-orange-900/30 @endif p-8 hover:shadow-xl transition-all">
                        @if($package->tier === 'gold')
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                                <span class="inline-flex items-center px-4 py-1 text-xs font-bold uppercase tracking-wider rounded-full bg-amber-500 text-zinc-900">
                                    Most Popular
                                </span>
                            </div>
                        @endif

                        <div class="text-center mb-6">
                            <div class="inline-flex items-center justify-center size-20 rounded-full mb-4 @if($package->tier === 'gold') bg-amber-400/20 @elseif($package->tier === 'silver') bg-zinc-400/20 @else bg-orange-900/20 @endif">
                                @if($package->tier === 'gold')
                                    <flux:icon.star class="size-10 text-amber-500" />
                                @elseif($package->tier === 'silver')
                                    <flux:icon.sparkles class="size-10 text-zinc-400 dark:text-zinc-500" />
                                @else
                                    <flux:icon.check-circle class="size-10 text-orange-700" />
                                @endif
                            </div>
                            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">{{ $package->name }}</h2>
                            <div class="text-5xl font-extrabold mb-2 @if($package->tier === 'gold') text-amber-500 @elseif($package->tier === 'silver') text-zinc-400 dark:text-zinc-500 @else text-orange-700 @endif">
                                ${{ number_format($package->price, 0) }}
                            </div>
                            @if ($package->description)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $package->description }}
                                </p>
                            @endif
                        </div>

                        <!-- Benefits List -->
                        <div class="space-y-3 mb-8">
                            @foreach ($package->benefits()->enabled()->ordered()->get() as $benefit)
                                <div class="flex items-start gap-3 text-sm">
                                    <flux:icon.check class="size-5 flex-shrink-0 mt-0.5 @if($package->tier === 'gold') text-amber-500 @elseif($package->tier === 'silver') text-zinc-400 @else text-orange-700 @endif" />
                                    <div class="flex-1">
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $benefit->name }}</span>
                                        @if($benefit->quantity > 1)
                                            <span class="text-zinc-600 dark:text-zinc-400"> ({{ $benefit->quantity }}x)</span>
                                        @endif
                                        @if ($benefit->description)
                                            <p class="text-zinc-600 dark:text-zinc-400 mt-0.5">{{ $benefit->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ route('sponsors.purchase', $package->id) }}"
                           wire:navigate
                           class="block w-full text-center px-6 py-3 font-bold rounded-lg transition-colors @if($package->tier === 'gold') text-white bg-amber-500 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700 @elseif($package->tier === 'silver') text-white bg-zinc-700 hover:bg-zinc-800 dark:bg-zinc-600 dark:hover:bg-zinc-700 @else text-white bg-orange-900 hover:bg-orange-800 dark:bg-orange-800 dark:hover:bg-orange-700 @endif">
                            Get Started
                        </a>
                    </div>
                @endforeach
            </div>

            <!-- Additional Info -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-8 mb-8">
                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">Why Sponsor Our Events?</h3>
                <div class="grid md:grid-cols-3 gap-8">
                    <div>
                        <div class="inline-flex items-center justify-center size-12 bg-amber-400/10 rounded-lg mb-4">
                            <flux:icon.user-group class="size-6 text-amber-500" />
                        </div>
                        <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Massive Reach</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Connect with thousands of athletes, families, and community members who attend our multi-sport events.
                        </p>
                    </div>
                    <div>
                        <div class="inline-flex items-center justify-center size-12 bg-amber-400/10 rounded-lg mb-4">
                            <flux:icon.heart class="size-6 text-amber-500" />
                        </div>
                        <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Community Impact</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Every sponsorship supports families affected by domestic violence, making a real difference in our community.
                        </p>
                    </div>
                    <div>
                        <div class="inline-flex items-center justify-center size-12 bg-amber-400/10 rounded-lg mb-4">
                            <flux:icon.chart-bar class="size-6 text-amber-500" />
                        </div>
                        <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Brand Visibility</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Multi-channel exposure across physical signage, digital platforms, and social media throughout the event season.
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 dark:from-amber-500/5 dark:to-orange-500/5 rounded-xl border border-amber-500/20 dark:border-amber-500/10 p-8 text-center">
                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-3">
                    Custom Sponsorship Packages Available
                </h3>
                <p class="text-zinc-600 dark:text-zinc-400 mb-6 max-w-2xl mx-auto">
                    Need something tailored to your specific marketing goals? We can create a custom sponsorship package that fits your needs perfectly.
                </p>
                <a href="{{ route('legal.contact') }}" class="inline-flex justify-center items-center px-8 py-3 font-bold leading-6 text-center bg-amber-500 rounded-full border border-transparent transition duration-150 ease-in-out text-white hover:bg-amber-600 focus:outline-none active:bg-amber-700">
                    Contact Us
                </a>
            </div>
        @endif
    </div>
</div>
