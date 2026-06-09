<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Urgent = 'urgent';
    case High = 'high';
    case Normal = 'normal';
    case Low = 'low';

    public function label(): string
    {
        return match ($this) {
            self::Urgent => 'Urgent',
            self::High => 'High',
            self::Normal => 'Normal',
            self::Low => 'Low',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Urgent => 'priority-urgent',
            self::High => 'priority-high',
            self::Normal => 'priority-normal',
            self::Low => 'priority-low',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Urgent => 'text-rose-500',
            self::High => 'text-orange-500',
            self::Normal => 'text-muted',
            self::Low => 'text-subtle',
        };
    }

    /**
     * Sort weight — higher means more important.
     */
    public function weight(): int
    {
        return match ($this) {
            self::Urgent => 3,
            self::High => 2,
            self::Normal => 1,
            self::Low => 0,
        };
    }

    /**
     * First-response SLA target, in hours.
     */
    public function responseTargetHours(): int
    {
        return match ($this) {
            self::Urgent => 1,
            self::High => 4,
            self::Normal => 8,
            self::Low => 24,
        };
    }
}
