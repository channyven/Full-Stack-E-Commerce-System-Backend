<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

abstract class ApiController extends Controller
{
    protected function respond(mixed $data, int $status = Response::HTTP_OK, ?string $message = null): JsonResponse
    {
        $payload = [
            'success' => true,
            'data' => $data,
        ];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    protected function respondOk($data): JsonResponse
    {
        return $this->respond($data, Response::HTTP_OK);
    }

    protected function respondCreated($data): JsonResponse
    {
        return $this->respond($data, Response::HTTP_CREATED);
    }

    protected function respondMessage(string $message, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $status);
    }

    protected function respondError(string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    protected function respondUnauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return $this->respondError($message, Response::HTTP_UNAUTHORIZED);
    }

    protected function respondForbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->respondError($message, Response::HTTP_FORBIDDEN);
    }

    protected function respondNotFound(string $message = 'Not found.'): JsonResponse
    {
        return $this->respondError($message, Response::HTTP_NOT_FOUND);
    }
}
