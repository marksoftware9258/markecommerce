<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseController extends Controller
{
    /**
     * Success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            if ($data instanceof ResourceCollection || $data instanceof LengthAwarePaginator) {
                $response['data'] = $data->response()->getData(true)['data'] ?? $data;
                $response['meta'] = $data->response()->getData(true)['meta'] ?? [];
                $response['links'] = $data->response()->getData(true)['links'] ?? [];
            } else {
                $response['data'] = $data instanceof JsonResource
                    ? $data->response()->getData(true)['data']
                    : $data;
            }
        }

        if (!empty($meta)) {
            $response['meta'] = array_merge($response['meta'] ?? [], $meta);
        }

        return response()->json($response, $status);
    }

    /**
     * Paginated response
     */
    protected function paginatedResponse(
        ResourceCollection $collection,
        string $message = 'Success'
    ): JsonResponse {
        $data = $collection->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data['data'] ?? [],
            'meta'    => $data['meta'] ?? [],
            'links'   => $data['links'] ?? [],
        ]);
    }

    /**
     * Error response
     */
    protected function errorResponse(
        string $message = 'Error',
        int $status = 400,
        array $errors = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Created response
     */
    protected function createdResponse(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * No content response
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
