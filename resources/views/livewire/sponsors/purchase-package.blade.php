<?php

use App\Models\Event;
use App\Models\SponsorPackage;
use App\Models\Sponsorship;
use App\Models\Sport;
use App\Models\User;

use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

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
    'waiverAccepted' => false,
    'paymentMethod' => 'stripe',
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
    if (! $this->selectedEventId) {
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
    'paymentMethod' => 'required|in:stripe,paypal',
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

        // Create checkout session based on selected payment method
        $sportCount = count($this->selectedSportIds);

        if ($this->paymentMethod === 'stripe') {
            // Sync package to Stripe if needed
            if ($this->package->needsStripeSync()) {
                $syncService = app(\App\Services\StripeProductSync::class);
                $syncService->syncSponsorPackage($this->package);
            }

            $checkoutService = app(\App\Services\StripeCheckoutService::class);
            $result = $checkoutService->createPackageCheckout(
                $user,
                $this->package,
                $sportCount,
                $this->selectedEventId,
                $sponsorship->id
            );
        } else {
            // PayPal
            // Sync package to PayPal if needed
            if ($this->package->needsPayPalSync()) {
                $syncService = app(\App\Services\PayPalProductSync::class);
                $syncService->syncSponsorPackage($this->package);
            }

            $checkoutService = app(\App\Services\PayPalCheckoutService::class);
            $result = $checkoutService->createPackageCheckout(
                $user,
                $this->package,
                $sportCount,
                $this->selectedEventId,
                $sponsorship->id
            );
        }

        // Redirect to checkout
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

                    <!-- Payment Method -->
                    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                        <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-4">Payment Method</h2>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Stripe Option -->
                            <label class="relative flex flex-col items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all @if($paymentMethod === 'stripe') border-amber-500 bg-amber-50 dark:bg-amber-900/20 @else border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 @endif">
                                <input type="radio" wire:model.live="paymentMethod" value="stripe" class="sr-only">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-8" viewBox="0 0 60 25" xmlns="http://www.w3.org/2000/svg">
                                        <path fill="@if($paymentMethod === 'stripe') #635bff @else #6772e5 @endif" d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.37 4.05-.95v3.32a8.33 8.33 0 0 1-4.56 1.1c-4.01 0-6.83-2.5-6.83-7.48 0-4.19 2.39-7.52 6.3-7.52 3.92 0 5.96 3.28 5.96 7.5 0 .4-.04 1.26-.06 1.48zm-5.92-5.62c-1.03 0-2.17.73-2.17 2.58h4.25c0-1.85-1.07-2.58-2.08-2.58zM40.95 20.3c-1.44 0-2.32-.6-2.9-1.04l-.02 4.63-4.12.87V5.57h3.76l.08 1.02a4.7 4.7 0 0 1 3.23-1.29c2.9 0 5.62 2.6 5.62 7.4 0 5.23-2.7 7.6-5.65 7.6zM40 8.95c-.95 0-1.54.34-1.97.81l.02 6.12c.4.44.98.78 1.95.78 1.52 0 2.54-1.65 2.54-3.87 0-2.15-1.04-3.84-2.54-3.84zM28.24 5.57h4.13v14.44h-4.13V5.57zm0-4.7L32.37 0v3.36l-4.13.88V.88zm-4.32 9.35v9.79H19.8V5.57h3.7l.12 1.22c1-1.77 3.07-1.41 3.62-1.22v3.79c-.52-.17-2.29-.43-3.32.86zm-8.55 4.72c0 2.43 2.6 1.68 3.12 1.46v3.36c-.55.3-1.54.54-2.89.54a4.15 4.15 0 0 1-4.27-4.24l.01-13.17 4.02-.86v3.54h3.14V9.1h-3.13v5.85zm-4.91.7c0 2.97-2.31 4.66-5.73 4.66a11.2 11.2 0 0 1-4.46-.93v-3.93c1.38.75 3.1 1.31 4.46 1.31.92 0 1.53-.24 1.53-1C6.26 13.77 0 14.51 0 9.95 0 7.04 2.28 5.3 5.62 5.3c1.36 0 2.72.2 4.09.75v3.88a9.23 9.23 0 0 0-4.1-1.06c-.86 0-1.44.25-1.44.93 0 1.85 6.29.97 6.29 5.88z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">Credit Card</span>
                                </div>
                                @if($paymentMethod === 'stripe')
                                    <div class="absolute top-2 right-2">
                                        <flux:icon.check-circle class="size-5 text-amber-500" />
                                    </div>
                                @endif
                            </label>

                            <!-- PayPal Option -->
                            <label class="relative flex flex-col items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-all @if($paymentMethod === 'paypal') border-amber-500 bg-amber-50 dark:bg-amber-900/20 @else border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 @endif">
                                <input type="radio" wire:model.live="paymentMethod" value="paypal" class="sr-only">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-8" viewBox="0 0 100 32" xmlns="http://www.w3.org/2000/svg">
                                        <path fill="@if($paymentMethod === 'paypal') #003087 @else #0070ba @endif" d="M12 4.917h8.104c4.659 0 7.796 2.458 7.796 6.916 0 5.004-3.584 8.084-8.508 8.084h-2.917l-1.5 7.083H12l3-22zm6.271 11c2.5 0 4.271-1.5 4.271-4 0-2-1.271-3-3.271-3h-2l-1.5 7h2.5z"/>
                                        <path fill="@if($paymentMethod === 'paypal') #009cde @else #0079c1 @endif" d="M35 4.917h2.958L35 27h-2.959l3-22.083zm4.917 0h8.104c4.659 0 7.796 2.458 7.796 6.916 0 5.004-3.584 8.084-8.508 8.084h-2.917l-1.5 7.083H40l3-22zm6.271 11c2.5 0 4.271-1.5 4.271-4 0-2-1.271-3-3.271-3h-2l-1.5 7h2.5z"/>
                                        <path fill="@if($paymentMethod === 'paypal') #012169 @else #003087 @endif" d="M70.146 15.013h2.917c4.708 0 6.604 2.146 6.604 5.188 0 3.938-2.75 6.813-7.417 6.813h-8.333l3-22.083h3l-1.188 7.604h2.604c3.146 0 5.063 1.417 5.063 4.167 0 2.27-1.563 3.77-4.063 3.77h-2.187v-.459zm-2.792 8.987h2.917c2.5 0 4.271-1.5 4.271-4 0-2-1.271-3-3.271-3h-2l-1.917 7z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">PayPal</span>
                                </div>
                                @if($paymentMethod === 'paypal')
                                    <div class="absolute top-2 right-2">
                                        <flux:icon.check-circle class="size-5 text-amber-500" />
                                    </div>
                                @endif
                            </label>
                        </div>
                        @error('paymentMethod') <span class="text-red-500 text-sm block mt-2">{{ $message }}</span> @enderror
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
