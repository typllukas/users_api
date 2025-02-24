<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;

trait JsonResponseHandler
{
    public function successResponse(array $data = [], int $statusCode = 200): JsonResponse
    {
        return new JsonResponse(['status' => 'success', 'data' => $data], $statusCode);
    }

    // Error response including details 
    public function errorResponse(string $message, int $statusCode = 400, array $details = []): JsonResponse
    {
        $response = ['status' => 'error', 'message' => $message];

        if (!empty($details)) {
            $response['errors'] = $details;
        }

        return new JsonResponse($response, $statusCode);
    }

}