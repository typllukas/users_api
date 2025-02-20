<?php

namespace App\EventListener;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Psr\Log\LoggerInterface;

class ExceptionListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Handle ValidationException with optional detailed errors
        if ($exception instanceof ValidationException) {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'errors' => $exception->getDetails() ?: null, 
            ], $exception->getStatusCode());

            $event->setResponse($response);
            return;
        }

        // Handle other HttpExceptions (like 404, 403, etc.)
        if ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());

            $event->setResponse($response);
            return;
        }

        // Fallback for unhandled exceptions 
        $this->logger->error($exception->getMessage(), ['exception' => $exception]);

        $response = new JsonResponse([
            'status' => 'error',
            'message' => 'Internal Server Error',
        ], 500);

        $event->setResponse($response);
    }
}
