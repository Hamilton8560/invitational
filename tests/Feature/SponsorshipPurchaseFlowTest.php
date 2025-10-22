<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Sale;
use App\Models\SponsorPackage;
use App\Models\Sponsorship;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\SponsorshipPurchaseConfirmation;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

test('sponsorship purchase sends confirmation email to new users with password reset link', function () {
    $venue = Venue::factory()->create();
    $event = Event::create([
        'venue_id' => $venue->id,
        'name' => 'Test Event',
        'start_date' => now()->addDays(30),
        'end_date' => now()->addDays(31),
        'status' => 'open',
        'refund_cutoff_date' => now()->addDays(25),
    ]);
    $sport = Sport::create(['name' => 'Basketball']);
    $package = SponsorPackage::create([
        'name' => 'Gold Package',
        'tier' => 'gold',
        'price' => 1000,
        'is_active' => true,
        'is_template' => false,
        'display_order' => 1,
    ]);

    // Create a new user (simulating the sponsor purchase flow)
    $user = User::factory()->create([
        'email' => 'sponsor@example.com',
        'password' => bcrypt(str()->random(32)), // Random password they can't use
    ]);

    // Create sponsorship
    $sponsorship = Sponsorship::create([
        'event_id' => $event->id,
        'sponsor_package_id' => $package->id,
        'buyer_id' => $user->id,
        'company_name' => 'Test Company',
        'contact_name' => $user->name,
        'contact_email' => 'sponsor@example.com',
        'status' => 'pending',
    ]);

    $sponsorship->sports()->attach($sport->id);

    // Create completed sale
    $sale = Sale::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'sponsorship_id' => $sponsorship->id,
        'quantity' => 1,
        'unit_price' => $package->price,
        'total_amount' => $package->price,
        'status' => 'completed',
    ]);

    // Assert notification was sent
    Notification::assertSentTo(
        $user,
        SponsorshipPurchaseConfirmation::class,
        function ($notification) use ($sale) {
            return $notification->sale->id === $sale->id
                && $notification->isNewUser === true;
        }
    );
});

test('sponsorship purchase sends confirmation email to existing users without password reset link', function () {
    $venue = Venue::factory()->create();
    $event = Event::create([
        'venue_id' => $venue->id,
        'name' => 'Test Event',
        'start_date' => now()->addDays(30),
        'end_date' => now()->addDays(31),
        'status' => 'open',
        'refund_cutoff_date' => now()->addDays(25),
    ]);
    $sport = Sport::create(['name' => 'Basketball']);
    $package = SponsorPackage::create([
        'name' => 'Gold Package',
        'tier' => 'gold',
        'price' => 1000,
        'is_active' => true,
        'is_template' => false,
        'display_order' => 1,
    ]);

    // Create existing user with a previous sale
    $user = User::factory()->create();
    Sale::factory()->create([
        'user_id' => $user->id,
        'status' => 'completed',
    ]);

    // Create sponsorship
    $sponsorship = Sponsorship::create([
        'event_id' => $event->id,
        'sponsor_package_id' => $package->id,
        'buyer_id' => $user->id,
        'company_name' => 'Test Company',
        'contact_name' => $user->name,
        'contact_email' => $user->email,
        'status' => 'pending',
    ]);

    $sponsorship->sports()->attach($sport->id);

    // Create completed sale
    $sale = Sale::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'sponsorship_id' => $sponsorship->id,
        'quantity' => 1,
        'unit_price' => $package->price,
        'total_amount' => $package->price,
        'status' => 'completed',
    ]);

    // Assert notification was sent
    Notification::assertSentTo(
        $user,
        SponsorshipPurchaseConfirmation::class,
        function ($notification) use ($sale) {
            return $notification->sale->id === $sale->id
                && $notification->isNewUser === false;
        }
    );
});

test('sponsorship notification includes all required details', function () {
    $venue = Venue::factory()->create();
    $event = Event::create([
        'venue_id' => $venue->id,
        'name' => 'Test Event',
        'start_date' => now()->addDays(30),
        'end_date' => now()->addDays(31),
        'status' => 'open',
        'refund_cutoff_date' => now()->addDays(25),
    ]);
    $sport = Sport::create(['name' => 'Basketball']);
    $package = SponsorPackage::create([
        'name' => 'Gold Package',
        'tier' => 'gold',
        'price' => 5000,
        'is_active' => true,
        'is_template' => false,
        'display_order' => 1,
    ]);

    $user = User::factory()->create(['name' => 'Test User']);

    $sponsorship = Sponsorship::create([
        'event_id' => $event->id,
        'sponsor_package_id' => $package->id,
        'buyer_id' => $user->id,
        'company_name' => 'Acme Corp',
        'contact_name' => 'John Doe',
        'contact_email' => $user->email,
        'status' => 'pending',
    ]);

    $sponsorship->sports()->attach($sport->id);

    $sale = Sale::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'sponsorship_id' => $sponsorship->id,
        'total_amount' => 5000,
        'status' => 'completed',
    ]);

    $notification = new SponsorshipPurchaseConfirmation($sale, true);
    $mail = $notification->toMail($user);

    expect($mail->subject)->toContain('Test Event');
    expect($mail->introLines)->toContain('Thank you for becoming a sponsor of Test Event!');
});

test('updating sale status to completed triggers sponsorship notification', function () {
    $venue = Venue::factory()->create();
    $event = Event::create([
        'venue_id' => $venue->id,
        'name' => 'Test Event',
        'start_date' => now()->addDays(30),
        'end_date' => now()->addDays(31),
        'status' => 'open',
        'refund_cutoff_date' => now()->addDays(25),
    ]);
    $package = SponsorPackage::create([
        'name' => 'Gold Package',
        'tier' => 'gold',
        'price' => 5000,
        'is_active' => true,
        'is_template' => false,
        'display_order' => 1,
    ]);
    $user = User::factory()->create();

    $sponsorship = Sponsorship::create([
        'event_id' => $event->id,
        'sponsor_package_id' => $package->id,
        'buyer_id' => $user->id,
        'company_name' => 'Test Company',
        'contact_name' => $user->name,
        'contact_email' => $user->email,
        'status' => 'pending',
    ]);

    // Create pending sale
    $sale = Sale::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'sponsorship_id' => $sponsorship->id,
        'status' => 'pending',
    ]);

    Notification::assertNothingSent();

    // Update to completed
    $sale->update(['status' => 'completed']);

    // Assert notification was sent
    Notification::assertSentTo($user, SponsorshipPurchaseConfirmation::class);
});
