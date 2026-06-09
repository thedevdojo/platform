<?php

namespace App\Enums;

enum TaskPriority: string
{
    case None = 'none';
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::None => 'No priority',
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    /**
     * The icon name used by the <x-icon> component.
     */
    public function icon(): string
    {
        return match ($this) {
            self::None => 'priority-none',
            self::Low => 'priority-low',
            self::Medium => 'priority-medium',
            self::High => 'priority-high',
            self::Urgent => 'priority-urgent',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::None => 'text-zinc-500',
            self::Low => 'text-zinc-400',
            self::Medium => 'text-amber-400',
            self::High => 'text-orange-400',
            self::Urgent => 'text-rose-500',
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::None => 0,
            self::Low => 1,
            self::Medium => 2,
            self::High => 3,
            self::Urgent => 4,
        };
    }

    /**
     * @return array<int, self>
     */
    public static function ordered(): array
    {
        return [self::Urgent, self::High, self::Medium, self::Low, self::None];
    }
}
