<?php
declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses {

    /**
     * @param string $message
     * @param array $data
     * @return JsonResponse
     */
    protected function ok(string $message, array $data = []) : JsonResponse
    {
        return $this->success($message, $data, 200);
    }

    /**
     * @param string $message
     * @param array $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function success(string $message, array $data = [], int $statusCode = 200) : JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }

    /**
     * @param string|array $errors
     * @param int|null $statusCode
     * @return JsonResponse
     */
    protected function error(string|array $errors = [], int|null $statusCode = null): JsonResponse
    {
        // If errors is already an array of formatted error objects, return as-is
        if (is_array($errors) && isset($errors[0]['message']) && array_key_exists('source', $errors[0])) {
            return response()->json([
                'errors' => $errors,
                'status' => $statusCode
            ], $statusCode);
        }

        // Otherwise, format as a single error message
        return response()->json([
            'errors' => [['message' => is_string($errors) ? $errors : 'An error occurred', 'source' => null]],
            'status' => $statusCode
        ], $statusCode);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    protected function notAuthorized(string $message) : JsonResponse
    {
        return $this->error([
            'status' => 401,
            'message' => $message,
            'source' => ''
        ], 401);
    }
}
