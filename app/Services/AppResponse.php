<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class AppResponse
{
    /**
     * Return a successful JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function success($data = null, string $message = '', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function error(string $message, int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $statusCode);
    }
}