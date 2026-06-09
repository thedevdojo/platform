<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskActivityNotification extends Notification
{
    use Queueable;

    /**
     * @param  'task_assigned'|'new_comment'|'status_done'  $event
     */
    public function __construct(
        public string $event,
        public Task $task,
        public string $message,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'message' => $this->message,
            'url' => route('projects.show', ['project' => $this->task->project_id, 'task' => $this->task->id]),
        ];
    }
}
