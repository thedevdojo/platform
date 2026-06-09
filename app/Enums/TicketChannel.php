<?php

namespace App\Enums;

enum TicketChannel: string
{
    case Email = 'email';
    case Chat = 'chat';
    case Web = 'web';
    case Phone = 'phone';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Chat => 'Chat',
            self::Web => 'Web form',
            self::Phone => 'Phone',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Email => 'envelope',
            self::Chat => 'chat-bubble',
            self::Web => 'globe',
            self::Phone => 'phone',
        };
    }
}
