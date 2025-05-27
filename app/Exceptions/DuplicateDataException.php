<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class DuplicateDataException extends Exception
{
    public function __construct($message = "Duplicate data found", $code = 409)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request)
    {
        return response()->json([
            'error' => 'Duplicate Data',
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}