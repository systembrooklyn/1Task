<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompanyMismatchException extends Exception
{
    public function __construct(string $message = "Company membership validation failed.", int $code = 422)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'Company Mismatch',
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
