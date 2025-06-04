<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use RuntimeException;

class ResourceDeletedException extends RuntimeException
{
    protected $message;
    protected $errors;
    protected $code;

    public function __construct(
        string $message = 'This resource has been deleted.',
        string $errorType = 'Resource Deleted',
        int $code = Response::HTTP_UNPROCESSABLE_ENTITY
    ) {
        parent::__construct($message, $code);

        $this->message = $message;
        $this->errors = $errorType;
        $this->code = $code;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'errors' => $this->errors
        ], $this->code);
    }
}