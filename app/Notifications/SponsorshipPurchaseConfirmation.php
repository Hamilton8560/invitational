<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class SponsorshipPurchaseConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Sale $sale,
        public bool $isNewUser = false
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $sponsorship = $this->sale->sponsorship;
        $package = $sponsorship->sponsorPackage;
        $event = $this->sale->event;

        $message = (new MailMessage)
            ->subject('Sponsorship Confirmed - '.$event->name)
            ->greeting('Hello '.$sponsorship->contact_name.',')
            ->line('Thank you for becoming a sponsor of '.$event->name.'!');

        // Welcome message for new users
        if ($this->isNewUser) {
            $resetUrl = $this->generatePasswordResetUrl($notifiable);

            $message->line('**Welcome to our platform!**')
                ->line('An account has been created for you. Please set your password to access your sponsorship dashboard and track your benefits.')
                ->action('Set Your Password', $resetUrl)
                ->line('---');
        }

        // Sponsorship details
        $message->line('**Sponsorship Details:**')
            ->line('Package: '.$package->name.' ('.$package->tier.')')
            ->line('Event: '.$event->name)
            ->line('Company: '.$sponsorship->company_name)
            ->line('Sports Sponsored: '.$sponsorship->sports->pluck('name')->join(', '))
            ->line('Total Investment: $'.number_format($this->sale->total_amount, 2));

        // Package benefits
        $benefits = $package->benefits()->enabled()->ordered()->get();
        if ($benefits->isNotEmpty()) {
            $message->line('**Package Benefits:**');
            foreach ($benefits as $benefit) {
                $message->line('• '.$benefit->name);
            }
        }

        // Dashboard link
        $message->line('---')
            ->line('**Track Your Sponsorship**')
            ->line('Visit your dashboard to view your sponsorship status, benefits fulfillment, and event details.')
            ->action('View Dashboard', route('dashboard'));

        // QR Code attachment
        if ($this->sale->qr_code_path && Storage::exists($this->sale->qr_code_path)) {
            $message->line('---')
                ->line('**Your Access QR Code**')
                ->line('A QR code is attached to this email for easy access to your sponsorship dashboard. You can also find it in your dashboard at any time.');

            $qrCodePath = Storage::path($this->sale->qr_code_path);
            $message->attach($qrCodePath, [
                'as' => 'sponsorship-qr-code.svg',
                'mime' => 'image/svg+xml',
            ]);
        }

        // Contact information
        $message->line('---')
            ->line('If you have any questions about your sponsorship or need assistance, please don\'t hesitate to contact us.')
            ->line('We look forward to a successful partnership!')
            ->salutation('Best regards, The '.$event->name.' Team');

        return $message;
    }

    /**
     * Generate a password reset URL for the user
     */
    protected function generatePasswordResetUrl(object $notifiable): string
    {
        $token = Password::broker()->createToken($notifiable);

        return route('password.reset', [
            'token' => $token,
            'email' => $notifiable->email,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'sale_id' => $this->sale->id,
            'sponsorship_id' => $this->sale->sponsorship_id,
            'event_id' => $this->sale->event_id,
            'amount' => $this->sale->total_amount,
        ];
    }
}
