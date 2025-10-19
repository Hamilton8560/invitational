<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class PurchaseConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Sale $sale
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
        $message = (new MailMessage)
            ->subject('Registration Confirmation - '.$this->sale->event->name)
            ->greeting('Hello '.$this->sale->user->name.',')
            ->line('Thank you for registering for '.$this->sale->event->name.'!')
            ->line('Your registration has been confirmed.');

        // Add purchase details
        $message->line('**Registration Details:**')
            ->line('Event: '.$this->sale->event->name)
            ->line('Product: '.$this->sale->product->name)
            ->line('Quantity: '.$this->sale->quantity)
            ->line('Total: $'.number_format($this->sale->amount, 2));

        // Add team/player details if applicable
        if ($this->sale->team) {
            $message->line('Team: '.$this->sale->team->name);
        }

        if ($this->sale->individualPlayer) {
            $message->line('Player: '.$this->sale->individualPlayer->name);
        }

        if ($this->sale->booth) {
            $message->line('Booth: '.$this->sale->booth->name);
        }

        if ($this->sale->banner) {
            $message->line('Banner: '.$this->sale->banner->name);
        }

        // Attach QR code if available
        if ($this->sale->qr_code_path && Storage::exists($this->sale->qr_code_path)) {
            $message->attach(Storage::path($this->sale->qr_code_path), [
                'as' => 'event-qr-code.svg',
                'mime' => 'image/svg+xml',
            ]);

            $message->line('**Your QR Code is attached to this email.**')
                ->line('Please bring this QR code with you to the event for quick check-in.');
        }

        $message->line('We look forward to seeing you at the event!')
            ->salutation('Best regards, '.$this->sale->event->name.' Team');

        return $message;
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
            'event_id' => $this->sale->event_id,
            'amount' => $this->sale->amount,
        ];
    }
}
