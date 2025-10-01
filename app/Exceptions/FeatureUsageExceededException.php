<?php

namespace App\Exceptions;

use RuntimeException;
use Illuminate\Http\Request;

class FeatureUsageExceededException extends RuntimeException
{
    protected string $featureName;
    protected int $limit;
    protected int $used;
    protected string $unit;

    public function __construct(
        string $featureName,
        int $limit,
        int $used,
        string $unit = '',
        $code = 403 // HTTP 403 Forbidden is appropriate here
    ) {
        $this->featureName = $featureName;
        $this->limit = $limit;
        $this->used = $used;
        $this->unit = $unit;

        $message = "Usage limit exceeded for {$featureName}. You have used {$used} {$unit} of {$limit} {$unit}.";

        parent::__construct($message, $code);
    }

    public function getFeatureName(): string
    {
        return $this->featureName;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getUsed(): int
    {
        return $this->used;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getData(): array
    {
        return [
            'feature' => $this->featureName,
            'limit' => $this->limit,
            'used' => $this->used,
            'unit' => $this->unit,
        ];
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request)
    {
        return response()->json([
            'error' => 'Feature Usage Limit Exceeded',
            'message' => $this->getMessage(),
            'data' => $this->getData(),
        ], $this->getCode());
    }
}