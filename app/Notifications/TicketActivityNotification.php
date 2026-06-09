<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketActivityNotification extends Notification
{
    use Queueable;

    /**
     * @param  'ticket_assigned'|'new_reply'|'ticket_resolved'|'sla_breach'  $event
     */
    public function __construct(
        public string $event,
        public Ticket $ticket,
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
            'ticket_id' => $this->ticket->id,
            'message' => $this->message,
            'url' => route('tickets.show', ['ticket' => $this->ticket->id]),
        ];
    }
}
