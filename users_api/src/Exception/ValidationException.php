<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationException extends HttpException
{
    private array $details;

    public function __construct(string $message = 'Validation error', array $details = [], int $statusCode = 400)
    {
        $this->details = $details;
        parent::__construct($statusCode, $message);
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
