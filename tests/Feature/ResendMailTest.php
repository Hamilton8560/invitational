<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('resend is configured correctly in production environment', function () {
    // Check that Resend API key is configured
    expect(config('services.resend.key'))->not->toBeEmpty();
    expect(config('mail.from.address'))->toBe('info@bryantinvitational.com');
    expect(config('mail.from.name'))->toBe('The Invitational');

    // Check that resend mailer is defined
    expect(config('mail.mailers.resend'))->toBeArray();
    expect(config('mail.mailers.resend.transport'))->toBe('resend');
});

test('can send email verification notification', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    $user->sendEmailVerificationNotification();

    Notification::assertSentTo(
        $user,
        \Illuminate\Auth\Notifications\VerifyEmail::class
    );
});

test('resend mailer transport is configured', function () {
    // Verify the resend mailer exists and has the correct transport
    $resendMailer = config('mail.mailers.resend');

    expect($resendMailer)->not->toBeNull();
    expect($resendMailer['transport'])->toBe('resend');
});
