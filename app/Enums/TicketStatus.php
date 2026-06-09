<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Pending = 'pending';
    case Snoozed = 'snoozed';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Pending => 'Pending',
            self::Snoozed => 'Snoozed',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Open => 'status-open',
            self::Pending => 'status-pending',
            self::Snoozed => 'status-snoozed',
            self::Resolved => 'status-resolved',
            self::Closed => 'status-closed',
        };
    }

    /**
     * Text color class for the status glyph.
     */
    public function color(): string
    {
        return match ($this) {
            self::Open => 'text-jade-500',
            self::Pending => 'text-amber-500',
            self::Snoozed => 'text-sky-500',
            self::Resolved => 'text-muted',
            self::Closed => 'text-subtle',
        };
    }

    /**
     * Soft badge classes (background + text) for status chips.
     */
    public function badge(): string
    {
        return match ($this) {
            self::Open => 'bg-jade-500/10 text-jade-600 dark:text-jade-400',
            self::Pending => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
            self::Snoozed => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
            self::Resolved => 'bg-fg/5 text-muted',
            self::Closed => 'bg-fg/5 text-subtle',
        };
    }

    /**
     * Whether the ticket still needs agent attention.
     */
    public function isActive(): bool
    {
        return in_array($this, [self::Open, self::Pending, self::Snoozed], true);
    }

    public function order(): int
    {
        return match ($this) {
            self::Open => 0,
            self::Pending => 1,
            self::Snoozed => 2,
            self::Resolved => 3,
            self::Closed => 4,
        };
    }

    /**
     * @return array<int, self>
     */
    public static function ordered(): array
    {
        $cases = self::cases();
        usort($cases, fn (self $a, self $b) => $a->order() <=> $b->order());

        return $cases;
    }
}
