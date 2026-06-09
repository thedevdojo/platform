<?php

namespace App\Enums;

enum MessageType: string
{
    case Reply = 'reply';
    case Note = 'note';

    public function label(): string
    {
        return match ($this) {
            self::Reply => 'Reply',
            self::Note => 'Internal note',
        };
    }
}
