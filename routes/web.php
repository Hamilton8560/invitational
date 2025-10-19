<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Volt::route('/', 'home')->name('home');

// Public Event Registration Routes
Volt::route('/events', 'events.browse')->name('events.index');
Volt::route('/events/{event:slug}', 'events.show')->name('events.show');
Volt::route('/events/{event:slug}/register/{product}', 'events.register')->name('events.register');
Volt::route('/events/{event:slug}/reserve-booth/{product}', 'events.reserve-booth')->name('events.reserve-booth');

// Player Invitation Route
Volt::route('/players/accept/{token}', 'players.accept-invitation')->name('players.accept-invitation');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';

// Admin/Authenticated Resource Routes (prefixed to avoid conflicts with public routes and Filament)
Route::middleware(['auth'])->prefix('manage')->name('manage.')->group(function () {
    Route::get('events/statements', [App\Http\Controllers\EventController::class, 'statements']);
    Route::resource('events', App\Http\Controllers\EventController::class);

    Route::get('products/statements', [App\Http\Controllers\ProductController::class, 'statements']);
    Route::resource('products', App\Http\Controllers\ProductController::class);

    Route::get('teams/statements', [App\Http\Controllers\TeamController::class, 'statements']);
    Route::resource('teams', App\Http\Controllers\TeamController::class);

    Route::get('team-players/statements', [App\Http\Controllers\TeamPlayerController::class, 'statements']);
    Route::resource('team-players', App\Http\Controllers\TeamPlayerController::class);
});
