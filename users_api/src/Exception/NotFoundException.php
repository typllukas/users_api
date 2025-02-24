<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'Resource not found', \Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}
