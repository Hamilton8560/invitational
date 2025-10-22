<?php

use App\Models\SponsorPackage;
use App\Models\Sponsorship;
use App\Models\Sale;
use App\Models\User;
use App\Models\Event;
use App\Models\Sport;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\{state, mount, rules, computed, layout};

layout('components.layouts.public');

state([
    'package',
    'selectedEventId' => null,
    'selectedSportIds' => [],
    'companyName',
    'companyLogoUrl',
    'websiteUrl',
    'contactName',
    'contactEmail',
    'contactPhone',
    'waiverAccepted' => false
]);

mount(function (SponsorPackage $package) {
    $this->package = $package->load('benefits');
});

$availableEvents = computed(function () {
    return Event::where('status', 'open')
        ->where('start_date', '>=', now())
        ->orderBy('start_date')
        ->get();
});

$availableSports = computed(function () {
    if (!$this->selectedEventId) {
        return collect();
    }

    $event = Event::find($this->selectedEventId);
    return $event ? Sport::whereHas('eventSports', function ($query) use ($event) {
        $query->where('event_id', $event->id);
    })->get() : collect();
});

$calculatedPrice = computed(function () {
    $sportCount = count($this->selectedSportIds);
    if ($sportCount === 0) {
        return 0;
    }
    return $this->package->price * $sportCount;
});

rules([
    'selectedEventId' => 'required|exists:events,id',
    'selectedSportIds' => 'required|array|min:1',
    'selectedSportIds.*' => 'exists:sports,id',
    'companyName' => 'required|string|max:255',
    'companyLogoUrl' => 'nullable|url|max:255',
    'websiteUrl' => 'nullable|url|max:255',
    'contactName' => 'required|string|max:255',
    'contactEmail' => 'required|email|max:255',
    'contactPhone' => 'nullable|string|max:20',
    'waiverAccepted' => 'accepted',
]);

$submit = function () {
    $this->validate();

    try {
        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $this->contactEmail],
            [
                'name' => $this->contactName,
                'password' => bcrypt(str()->random(32)),
            ]
        );

        // Create sponsorship
        $sponsorship = Sponsorship::create([
            'event_id' => $this->selectedEventId,
            'sponsor_package_id' => $this->package->id,
            'buyer_id' => $user->id,
            'company_name' => $this->companyName,
            'company_logo_url' => $this->companyLogoUrl,
            'website_url' => $this->websiteUrl,
            'contact_name' => $this->contactName,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'status' => 'pending',
        ]);

        // Attach selected sports
        $sponsorship->sports()->attach($this->selectedSportIds);

        // Create Stripe Checkout session
        $checkoutService = app(\App\Services\StripeCheckoutService::class);
        $sportCount = count($this->selectedSportIds);

        // Sync package to Stripe if needed
        if ($this->package->needsStripeSync()) {
            $syncService = app(\App\Services\StripeProductSync::class);
            $syncService->syncSponsorPackage($this->package);
        }

        $result = $checkoutService->createPackageCheckout(
            $user,
            $this->package,
            $sportCount,
            $this->selectedEventId,
            $sponsorship->id
        );

        // Redirect to Stripe Checkout
        return redirect($result['checkout_url']);
    } catch (\Exception $e) {
        $this->addError('general', 'Failed to create checkout session. Please try again.');
        \Log::error('Sponsorship checkout creation failed', [
            'error' => $e->getMessage(),
            'package_id' => $this->package->id,
            'email' => $this->contactEmail,
        ]);
    }
};

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
    <!-- Header -->
    <div class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
        <div class="container mx-auto px-4 py-8 max-w-4xl">
            <a href="{{ route('sponsors.browse') }}" class="inline-flex items-center gap-2 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white mb-6 transition-colors">
                <flux:icon.arrow-left class="size-4" />
                <span class="text-sm font-medium">Back to Packages</span>
            </a>

            <div class="flex items-start gap-4">
                <div class="inline-flex items-center justify-center size-16 rounded-lg @if($package->tier === 'gold') bg-amber-400/20 @elseif($package->tier === 'silver') bg-zinc-400/20 @else bg-orange-900/20 @endif">
                    @if($package->tier === 'gold')
                        <flux:icon.star class="size-8 text-amber-500" />
                    @elseif($package->tier === 'silver')
                        <flux:icon.sparkles class="size-8 text-zinc-400" />
                    @else
                        <flux:icon.check-circle class="size-8 text-orange-700" />
                    @endif
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white mb-2">
                        {{ $package->name }}
                    </h1>
                    <p class="text-lg @if($package->tier === 'gold') text-amber-500 @elseif($package->tier === 'silver') text-zinc-400 @else text-orange-700 @endif font-bold">
                        ${{ number_format($package->price, 0) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <form wire:submit="submit">
            @error('general')
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                </div>
            @enderror

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Form Column -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Event Selection -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4">Event Selection</h2>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                Which event would you like to sponsor? *
                            </label>
                            <select wire:model.live="selectedEventId" class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Select an event</option>
                                @foreach ($this->availableEvents as $event)
                                    <option value="{{ $event->id }}">
                                        {{ $event->name }} - {{ $event->start_date->format('M j, Y') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedEventId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        @if($selectedEventId && $this->availableSports->isNotEmpty())
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Which sports would you like to sponsor? *
                                </label>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3">
                                    Price: ${{ number_format($package->price, 0) }} per sport selected
                                </p>
                                <div class="space-y-2">
                                    @foreach ($this->availableSports as $sport)
                                        <label class="flex items-center gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
                                            <input type="checkbox" wire:model.live="selectedSportIds" value="{{ $sport->id }}" class="rounded text-amber-500 focus:ring-amber-500">
                                            <span class="text-sm text-zinc-900 dark:text-white">{{ $sport->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('selectedSportIds') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <!-- Company Information -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4">Company Information</h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Company Name *
                                </label>
                                <input type="text" wire:model="companyName" class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                @error('companyName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Company Logo URL
                                </label>
                                <input type="url" wire:model="companyLogoUrl" placeholder="https://example.com/logo.png" class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">We'll contact you later for final logo assets</p>
                                @error('companyLogoUrl') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Website URL
                                </label>
                                <input type="url" wire:model="websiteUrl" placeholder="https://example.com" class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                @error('websiteUrl') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4">Contact Information</h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Contact Name *
                                </label>
                                <input type="text" wire:model="contactName" class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                @error('contactName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Contact Email *
                                </label>
                                <input type="email" wire:model="contactEmail" class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                @error('contactEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                                    Contact Phone
                                </label>
                                <input type="tel" wire:model="contactPhone" class="w-full px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                @error('contactPhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Waiver -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="waiverAccepted" class="mt-1 rounded text-amber-500 focus:ring-amber-500">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                I agree to the sponsorship terms and conditions. I understand that sponsorship benefits will be fulfilled according to the package details and that payment is required to activate the sponsorship.
                            </span>
                        </label>
                        @error('waiverAccepted') <span class="text-red-500 text-sm block mt-2">{{ $message }}</span> @enderror
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        class="w-full px-6 py-3 font-bold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed @if($package->tier === 'gold') text-white bg-amber-500 hover:bg-amber-600 @elseif($package->tier === 'silver') text-white bg-zinc-700 hover:bg-zinc-800 @else text-white bg-orange-900 hover:bg-orange-800 @endif"
                    >
                        <span wire:loading.remove wire:target="submit">
                            Submit Sponsorship Application
                        </span>
                        <span wire:loading wire:target="submit" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>

                <!-- Package Summary Sidebar -->
                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6 sticky top-4">
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-4">Package Benefits</h3>

                        <div class="space-y-2 mb-6">
                            @foreach ($package->benefits()->enabled()->ordered()->get() as $benefit)
                                <div class="flex items-start gap-2 text-sm">
                                    <flux:icon.check class="size-4 flex-shrink-0 mt-0.5 @if($package->tier === 'gold') text-amber-500 @elseif($package->tier === 'silver') text-zinc-400 @else text-orange-700 @endif" />
                                    <span class="text-zinc-900 dark:text-white">{{ $benefit->name }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                            <div class="space-y-2 mb-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">Base Price per Sport</span>
                                    <span class="text-zinc-900 dark:text-white font-medium">
                                        ${{ number_format($package->price, 0) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">Sports Selected</span>
                                    <span class="text-zinc-900 dark:text-white font-medium">
                                        {{ count($selectedSportIds) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mb-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">Total Price</span>
                                <span class="text-2xl font-bold @if($package->tier === 'gold') text-amber-500 @elseif($package->tier === 'silver') text-zinc-400 @else text-orange-700 @endif">
                                    ${{ number_format($this->calculatedPrice, 0) }}
                                </span>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                Payment will be collected after application review
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
