<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(string $message, private int $status = 422, private array $extra = [])
    {
        parent::__construct($message, $status);
    }

    public function render($request)
    {
        return response()->json(array_merge(['message' => $this->getMessage()], $this->extra), $this->status);
    }
}
