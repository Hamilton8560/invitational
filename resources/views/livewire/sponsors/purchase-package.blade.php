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

    DB::transaction(function () {
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

        // Create sale for payment tracking
        $sale = Sale::create([
            'event_id' => $this->selectedEventId,
            'product_id' => null, // Sponsorships don't use products
            'sponsorship_id' => $sponsorship->id,
            'user_id' => $user->id,
            'quantity' => 1,
            'unit_price' => $this->package->price,
            'total_amount' => $this->package->price,
            'status' => 'pending',
        ]);

        // Store sponsorship ID in session for payment flow
        session()->put('pending_sponsorship_id', $sponsorship->id);
        session()->put('pending_sale_id', $sale->id);
    });

    session()->flash('message', 'Sponsorship application submitted successfully! Continue to payment.');

    // TODO: Redirect to payment page when implemented
    $this->redirect(route('sponsors.browse'));
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
                                <div class="space-y-2">
                                    @foreach ($this->availableSports as $sport)
                                        <label class="flex items-center gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 cursor-pointer">
                                            <input type="checkbox" wire:model="selectedSportIds" value="{{ $sport->id }}" class="rounded text-amber-500 focus:ring-amber-500">
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
                    <button type="submit" class="w-full px-6 py-3 font-bold rounded-lg transition-colors @if($package->tier === 'gold') text-white bg-amber-500 hover:bg-amber-600 @elseif($package->tier === 'silver') text-white bg-zinc-700 hover:bg-zinc-800 @else text-white bg-orange-900 hover:bg-orange-800 @endif">
                        Submit Sponsorship Application
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
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Package Price</span>
                                <span class="text-2xl font-bold @if($package->tier === 'gold') text-amber-500 @elseif($package->tier === 'silver') text-zinc-400 @else text-orange-700 @endif">
                                    ${{ number_format($package->price, 0) }}
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
