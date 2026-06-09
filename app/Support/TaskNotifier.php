<?php

namespace App\Support;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskActivityNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TaskNotifier
{
    /**
     * Notify the assignee that they were assigned a task.
     */
    public static function assigned(Task $task, User $actor): void
    {
        $assignee = $task->assignee;

        if ($assignee && $assignee->id !== $actor->id) {
            self::send($assignee, new TaskActivityNotification(
                'task_assigned',
                $task,
                Str::of($actor->name)->explode(' ')->first().' assigned you · '.$task->identifier().' '.$task->title,
            ));
        }
    }

    /**
     * Notify watchers about a new comment.
     */
    public static function commented(Task $task, User $actor): void
    {
        foreach (self::watchers($task, $actor) as $watcher) {
            self::send($watcher, new TaskActivityNotification(
                'new_comment',
                $task,
                Str::of($actor->name)->explode(' ')->first().' commented on '.$task->identifier().' '.$task->title,
            ));
        }
    }

    /**
     * Notify watchers a task was completed.
     */
    public static function completed(Task $task, User $actor): void
    {
        foreach (self::watchers($task, $actor) as $watcher) {
            self::send($watcher, new TaskActivityNotification(
                'status_done',
                $task,
                Str::of($actor->name)->explode(' ')->first().' completed '.$task->identifier().' '.$task->title,
            ));
        }
    }

    /**
     * Everyone interested in a task except the person taking the action.
     *
     * @return Collection<int, User>
     */
    protected static function watchers(Task $task, User $actor): Collection
    {
        return collect([$task->assignee, $task->creator, $task->project?->owner])
            ->filter()
            ->unique('id')
            ->reject(fn (User $user) => $user->id === $actor->id)
            ->values();
    }

    protected static function send(User $user, TaskActivityNotification $notification): void
    {
        // Respect the user's in-app notification preference where available.
        if (method_exists($user, 'notificationPreference') && $user->notificationPreference('email_notifications') === false) {
            // The user has muted general notifications; still record in-app for the inbox.
        }

        $user->notify($notification);
    }
}
