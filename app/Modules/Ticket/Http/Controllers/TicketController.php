<?php

namespace App\Modules\Ticket\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ticket\Http\Requests\StoreTicketRequest;
use App\Modules\Ticket\Services\TicketService;
use App\Modules\Ticket\Http\Resources\TicketResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function __construct(protected TicketService $ticketService) {}

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user->company_id) {
            return response()->json([
                'message' => 'You must belong to a company to create a ticket.'
            ], 400);
        }

        $ticket = $this->ticketService->createTicket(
            $request->validated(),
            $user->id,
            $user->company_id,
            $request->ip()
        );

        $ticket->load('company');

        return response()->json([
            'message' => 'Ticket created successfully.',
            'data'    => new TicketResource($ticket),
        ], 201);
    }

    public function ticketCategories(): JsonResponse
    {
        $categories = $this->ticketService->getTicketCategories();
        return response()->json([
            'message' => 'Categories retrieved successfully.',
            'data'    => $categories,
        ]);
    }
}
