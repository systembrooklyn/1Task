<?php

namespace App\Modules\Ticket\Services;

use App\Modules\Ticket\Models\Ticket;
use App\Modules\Ticket\Models\TicketAction;
use App\Modules\Ticket\Repositories\Contracts\TicketActionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TicketActionService
{
    public function __construct(
        protected TicketActionRepositoryInterface $actionRepo
    ) {}

    public function createAction(Ticket $ticket, array $data, int $userId, string $ipAddress): TicketAction
    {
        return DB::transaction(function () use ($ticket, $data, $userId, $ipAddress) {
            $action = new TicketAction([
                'ticket_id'   => $ticket->id,
                'user_id'     => $userId,
                'ip_address'  => $ipAddress,
                'action_type' => $data['action_type'],
            ]);

            if ($data['action_type'] === 'status') {
                $oldStatus = $ticket->status->value;
                $newStatus = $data['to_status'];
                $action->from_status = $oldStatus;
                $action->to_status = $newStatus;
                if ($oldStatus !== $newStatus) {
                    $ticket->status = $newStatus;
                    $ticket->closed_at = in_array($newStatus, ['closed', 'resolved'])
                        ? now()
                        : null;
                    $ticket->save();
                }
            } elseif ($data['action_type'] === 'priority') {
                $oldPriority = $ticket->priority->value;
                $newPriority = $data['to_priority'];
                $action->from_priority = $oldPriority;
                $action->to_priority = $newPriority;
                if ($oldPriority !== $newPriority) {
                    $ticket->priority = $newPriority;
                    $ticket->save();
                }
            } else {
                $action->content = $data['content'] ?? '';
            }

            $action->save();
            return $action;
        });
    }
}
