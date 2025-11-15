<?php

namespace App\Http\Controllers;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    // public function index()
    // {
    //     $tickets = Ticket::with('company', 'user')->get();
    //     return response()->json([
    //         'message' => 'Tickets retrieved successfully.',
    //         'data' => TicketResource::collection($tickets),
    //     ], 200);
    // }
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user->company_id) {
            return response()->json([
                'message' => 'You must belong to a company to create a ticket.'
            ], 400);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => ['required', 'in:' . implode(',', array_column(TicketCategory::cases(), 'value'))],
            'description' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:25',
        ]);

        $priority = $validated['priority']
            ?? $this->getDefaultPriorityForCategory($validated['category'])->value;

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => TicketStatus::Open,
            'priority' => $priority,
            'ip_address' => $request->ip(),
        ]);

        $ticket->load('company');

        return response()->json([
            'message' => 'Ticket created successfully.',
            'data' => new TicketResource($ticket),
        ], 201);
    }
    public function ticketCategories()
    {
        $categories = collect(TicketCategory::cases())->map(function ($case) {
            return [
                'value' => $case->value,
                'label' => $case->label(),
            ];
        })->values();

        return response()->json([
            'message' => 'Categories retrieved successfully.',
            'data' => $categories
        ]);
    }
    private function getDefaultPriorityForCategory(string $category): TicketPriority
    {
        return match ($category) {
            TicketCategory::Security->value, TicketCategory::Billing->value
            => TicketPriority::Urgent,

            TicketCategory::Authentication->value, TicketCategory::Performance->value
            => TicketPriority::High,

            TicketCategory::FeatureRequest->value, TicketCategory::Bug->value
            => TicketPriority::Medium,

            TicketCategory::Other->value
            => TicketPriority::Low,

            default => TicketPriority::Medium,
        };
    }
}
