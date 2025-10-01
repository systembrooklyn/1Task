<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'company_id',
        'user_id',
        'plan_id',
        'plan_name',
        'amount_cents',
        'currency',
        'payment_method',
        'additional_info',
        'success',
        'error_message',
        'paid_at',
        'status',
        'pending',
        'is_refunded',
        'is_voided',
        'refunded_amount_cents',
        'raw_response',
    ];

    protected $casts = [
        'additional_info' => 'array',
        'success' => 'boolean',
        'pending' => 'boolean',
        'is_refunded' => 'boolean',
        'is_voided' => 'boolean',
        'paid_at' => 'datetime',
        'amount_cents' => 'integer',
        'refunded_amount_cents' => 'integer',
        'raw_response' => 'array',
    ];
}
