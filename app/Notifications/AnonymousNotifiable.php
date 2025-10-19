<?php

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;

class AnonymousNotifiable
{
    use Notifiable;

    public function __construct(
        public string $email,
        public string $name = ''
    ) {}

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
