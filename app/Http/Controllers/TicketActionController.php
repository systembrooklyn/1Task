<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Resources\TicketActionResource;
use App\Models\Ticket;
use App\Models\TicketAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketActionController extends Controller
{
    /**
     * Add a new action (e.g., note or status update).
     */
    public function store(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if ($ticket->user_id !== $user->id && $ticket->company_id !== $user->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'action_type' => 'required|in:note,status,priority',
            'content' => 'nullable|string|max:1000',
            'to_status' => ['required_if:action_type,status', 'in:' . implode(',', array_column(TicketStatus::cases(), 'value'))],
            'to_priority' => ['required_if:action_type,priority', 'in:' . implode(',', array_column(TicketPriority::cases(), 'value'))],
        ]);
        $action = DB::transaction(function () use ($ticket, $user, $validated, $request) {
            $action = new TicketAction([
                'ticket_id'   => $ticket->id,
                'user_id'     => $user->id,
                'ip_address'  => $request->ip(),
                'action_type' => $validated['action_type'],
            ]);
            if ($validated['action_type'] === 'status') {
                $oldStatus = $ticket->status->value;
                $newStatus = $validated['to_status'];
                $action->from_status = $oldStatus;
                $action->to_status = $newStatus;
                if ($oldStatus !== $newStatus) {
                    $ticket->status = $newStatus;
                    $ticket->closed_at = in_array($newStatus, ['closed', 'resolved'])
                        ? now()
                        : null;
                    $ticket->save();
                }
            } elseif ($validated['action_type'] === 'priority') {
                $oldPriority = $ticket->priority->value;
                $newPriority = $validated['to_priority'];
                $action->from_priority = $oldPriority;
                $action->to_priority = $newPriority;
                if ($oldPriority !== $newPriority) {
                    $ticket->priority = $newPriority;
                    $ticket->save();
                }
            } else {
                $action->content = $validated['content'] ?? '';
            }
            $action->save();
            return $action;
        });

        return response()->json([
            'message' => 'Action recorded successfully.',
            'data' => new TicketActionResource($action->load('user', 'ticket')),
        ], 201);
    }
}
