<?php

namespace App\Modules\Ticket\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ticket\Http\Requests\StoreTicketActionRequest;
use App\Modules\Ticket\Services\TicketActionService;
use App\Modules\Ticket\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TicketActionController extends Controller
{
    public function __construct(protected TicketActionService $actionService) {}

    public function store(StoreTicketActionRequest $request, int $ticketId): JsonResponse
    {
        $user = Auth::user();

        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        if ($ticket->company_id !== $user->company_id && $ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $action = $this->actionService->createAction(
            $ticket,
            $request->validated(),
            $user->id,
            $request->ip()
        );

        return response()->json([
            'message' => 'Action recorded successfully.',
            'data'    => $action->load('user'),
        ], 201);
    }
}
