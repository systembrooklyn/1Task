<?php

namespace App\Enums;

enum TicketCategory: string
{
    case Authentication = 'authentication';
    case Billing = 'billing';
    case FeatureRequest = 'feature_request';
    case Bug = 'bug';
    case Performance = 'performance';
    case Security = 'security';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Authentication => 'Authentication/Login',
            self::Billing => 'Billing & Payments',
            self::FeatureRequest => 'Feature Request',
            self::Bug => 'Bug Report',
            self::Performance => 'Performance Issue',
            self::Security => 'Security Concern',
            self::Other => 'Other',
        };
    }
}