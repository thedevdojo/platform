<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Backlog = 'backlog';
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Done = 'done';

    /**
     * Human label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Backlog',
            self::Todo => 'Todo',
            self::InProgress => 'In Progress',
            self::InReview => 'In Review',
            self::Done => 'Done',
        };
    }

    /**
     * The icon name used by the <x-icon> component.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Backlog => 'circle-dashed',
            self::Todo => 'circle',
            self::InProgress => 'circle-half',
            self::InReview => 'circle-eye',
            self::Done => 'circle-check',
        };
    }

    /**
     * A tailwind text-color class for the status indicator.
     */
    public function color(): string
    {
        return match ($this) {
            self::Backlog => 'text-zinc-400',
            self::Todo => 'text-zinc-300',
            self::InProgress => 'text-amber-400',
            self::InReview => 'text-violet-400',
            self::Done => 'text-emerald-400',
        };
    }

    /**
     * Board column ordering.
     */
    public function order(): int
    {
        return match ($this) {
            self::Backlog => 0,
            self::Todo => 1,
            self::InProgress => 2,
            self::InReview => 3,
            self::Done => 4,
        };
    }

    public function isDone(): bool
    {
        return $this === self::Done;
    }

    /**
     * @return array<int, self>
     */
    public static function ordered(): array
    {
        return [self::Backlog, self::Todo, self::InProgress, self::InReview, self::Done];
    }
}
