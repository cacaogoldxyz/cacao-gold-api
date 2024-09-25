<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AppResponse; 
use App\Http\Resources\UserResource; 

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['password'] = Hash::make($validatedData['password']);
            unset($validatedData['password_confirmation']);
            $user = User::create($validatedData);
            $token = $user->createToken($validatedData['email']);

            return AppResponse::success([
                'user' => new UserResource($user), // Make the user to have a consistent response
                'token' => $token->plainTextToken,
            ], 'Registration successful!', 201);
        } catch (\Throwable $th) {
            return AppResponse::error('Registration failed: ' . $th->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $user = User::where('email', $validatedData['email'])->first();

            if (!$user || !Hash::check($validatedData['password'], $user->password)) {
                return AppResponse::error('The provided credentials are incorrect.', 401);
            }

            $token = $user->createToken($user->name);

            return AppResponse::success([
                'user' => new UserResource($user), // Make the user to have a consistent response
                'token' => $token->plainTextToken,
            ], 'Login successful.', 200);
        } catch (\Throwable $th) {
            return AppResponse::error('Login failed: ' . $th->getMessage(), 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return AppResponse::success(null, 'You are logged out.', 200);
        } catch (\Throwable $th) {
            return AppResponse::error('Logout failed: ' . $th->getMessage(), 500);
        }
    }
}
