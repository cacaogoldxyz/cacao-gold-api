<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // For API requests, we don't redirect; instead, we return a 401 response
        if ($request->expectsJson()) {
            Log::warning('Unauthenticated access attempt to: ' . $request->url());
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // For web requests, redirect to the login page
        return route('login');
    }

    // Optionally, you can add a method to handle unauthenticated responses
    protected function unauthenticated($request, array $guards)
    {
        // Log the unauthorized access attempt
        Log::warning('Unauthenticated access attempt to: ' . $request->url());

        // Return a 401 response for API requests
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
