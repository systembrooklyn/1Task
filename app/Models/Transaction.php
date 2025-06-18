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
        'paid_at'
    ];

    protected $casts = [
        'additional_info' => 'array',
        'success' => 'boolean',
        'paid_at' => 'datetime',
    ];
}