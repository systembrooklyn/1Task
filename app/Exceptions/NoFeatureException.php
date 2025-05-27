<?php

namespace App\Exceptions;

use Exception;

class NoFeatureException extends Exception
{
    public function render()
    {
        return response()->json([
            'error' => 'Feature not available in your plan.',
            'message' => 'Please Subscribe so you can enjoy this features'
        ], 403);
    }
}
