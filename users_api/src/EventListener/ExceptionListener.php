<?php

namespace App\EventListener;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExceptionListener
{
    private LoggerInterface $logger;
    private string $appEnv;

    public function __construct(LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->appEnv = $params->get('kernel.environment'); // Get APP_ENV
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Handle ValidationException
        if ($exception instanceof ValidationException) {
            $errorResponse = [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];

            if (!empty($exception->getDetails())) {
                $errorResponse['errors'] = $exception->getDetails();
            }

            if ($this->appEnv === 'dev') {
                $errorResponse['trace'] = $exception->getTraceAsString();
            }

            $response = new JsonResponse($errorResponse, $exception->getStatusCode());
            $event->setResponse($response);
            return;
        }

        // Handle HttpExceptions (404, 403, etc.)
        if ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());

            $event->setResponse($response);
            return;
        }

        // Log the exception
        $this->logger->error($exception->getMessage(), [
            'exception' => $exception,
            'trace' => $exception->getTraceAsString(),
        ]);

        // Prepare error response
        $errorResponse = [
            'status' => 'error',
            'message' => 'Internal Server Error',
        ];

        // In dev mode, include full exception details
        /* if ($this->appEnv === 'dev') {
            $errorResponse = array_merge($errorResponse, $this->formatExceptionDetails($exception));
        } */

        $response = new JsonResponse($errorResponse, 500);
        $event->setResponse($response);
    }

    private function formatExceptionDetails(\Throwable $exception): array
    {
        $details = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        $previous = $exception->getPrevious();
        if ($previous) {
            $details['previous'] = $this->formatExceptionDetails($previous);
        }

        return $details;
    }
}
