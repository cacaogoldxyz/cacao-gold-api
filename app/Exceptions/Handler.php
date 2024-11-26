<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    
    public function render($request, Throwable $exception)
    {
        // Handle ModelNotFoundException
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'error' => 'Post not found',
            ], 404);
        }

        // Handle ThrottleRequestsException
        if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'message' => 'Too many login attempts. Please try again in a few minutes.',
            ], 429);
        }
    
        return parent::render($request, $exception);
    }
}
