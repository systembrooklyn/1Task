<?php

namespace App\Modules\Ticket\Services;

use App\Modules\Ticket\Models\Ticket;
use App\Modules\Ticket\Enums\TicketCategory;
use App\Modules\Ticket\Enums\TicketPriority;
use App\Modules\Ticket\Enums\TicketStatus;
use App\Modules\Ticket\Repositories\Contracts\TicketRepositoryInterface;

class TicketService
{
    public function __construct(
        protected TicketRepositoryInterface $ticketRepo
    ) {}

    public function createTicket(array $data, int $userId, int $companyId, string $ipAddress): Ticket
    {
        $priority = $data['priority'] ?? $this->getDefaultPriorityForCategory($data['category'])->value;

        return $this->ticketRepo->create([
            'user_id'     => $userId,
            'company_id'  => $companyId,
            'title'       => $data['title'],
            'category'    => $data['category'],
            'description' => $data['description'],
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?? null,
            'status'      => TicketStatus::Open,
            'priority'    => $priority,
            'ip_address'  => $ipAddress,
        ]);
    }

    public function getTicketCategories(): array
    {
        return collect(TicketCategory::cases())->map(function ($case) {
            return [
                'value' => $case->value,
                'label' => $case->label(),
            ];
        })->values()->toArray();
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
