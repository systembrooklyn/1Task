<?php

namespace App\Exceptions;

use Exception;

class NoActivePlanException extends Exception
{
    public function render()
    {
        return response()->json([
            'allowed' => false,
            'error' => 'No active plan found.',
            'message' => 'Please Subscribe so you can enjoy all the great features'
        ], 403);
    }
}