<?php

namespace App\Notifications;

use App\Models\PlayerInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlayerInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PlayerInvitation $invitation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->invitation->team;
        $product = $team->product;
        $division = $product->division;
        $event = $product->event;
        $acceptUrl = route('invitations.accept', ['token' => $this->invitation->token]);

        return (new MailMessage)
            ->subject("You've been invited to join {$team->name}")
            ->greeting("Hello {$this->invitation->first_name}!")
            ->line("You've been invited to join **{$team->name}** for the upcoming {$event->name}.")
            ->line('**Event Details:**')
            ->line("📅 {$event->start_date->format('F j, Y')} - {$event->end_date->format('F j, Y')}")
            ->line("📍 {$event->venue->name}")
            ->line("🏆 {$division->sport->name} - {$division->name}")
            ->line('')
            ->line("Click the button below to accept your invitation. No password needed - you'll be automatically logged in!")
            ->action('Accept Invitation & Join Team', $acceptUrl)
            ->line('This invitation will expire on '.$this->invitation->expires_at->format('F j, Y').'.')
            ->line('If you have any questions, please contact your team captain.')
            ->salutation('See you at the tournament!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'team_id' => $this->invitation->team_id,
            'team_name' => $this->invitation->team->name,
            'event_name' => $this->invitation->team->event->name,
        ];
    }
}
