<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use RuntimeException;

class NoActivePlanException extends Exception
{
    protected $message;
    protected $errors;
    protected $code;
    public function __construct(
        string $message = 'Please Subscribe so you can enjoy all the great features',
        string $errorType = 'No active plan found.',
        int $code = Response::HTTP_UNPROCESSABLE_ENTITY
    ) {
        parent::__construct($message, $code);

        $this->message = $message;
        $this->errors = $errorType;
        $this->code = $code;
    }
    public function render()
    {
        return response()->json([
            'allowed' => false,
            'error' => $this->errors,
            'message' => $this->message
        ], $this->code);
    }
}
