<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;

trait JsonResponseHandler
{
    public function successResponse(array $data = [], int $statusCode = 200): JsonResponse
    {
        return new JsonResponse(['status' => 'success', 'data' => $data], $statusCode);
    }

    public function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return new JsonResponse(['status' => 'error', 'message' => $message], $statusCode);
    }
}