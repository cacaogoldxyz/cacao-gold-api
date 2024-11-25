<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AppResponse; 
use App\Exceptions\InvalidCredentialsException;
use Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, $e): JsonResponse
    {
        try {
            Log::error($e->getMessage());
            $validatedData = $request->validated();
            $validatedData['password'] = Hash::make($validatedData['password']);
            unset($validatedData['password_confirmation']);
            $user = User::create($validatedData);
    
            return AppResponse::success([
                'user' => new UserResource($user),
            ], 'Registration successful!', 201);
        } catch (\Exception $e) {
            return AppResponse::error('Registration failed. Please try again later.', 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $user = User::where('email', $validatedData['email'])->first();

            if (!$user) {
                return AppResponse::error('Invalid email or password.', 401);
            }
            
            if (!Hash::check($validatedData['password'], $user->password)) {
                return AppResponse::error('Invalid email or password.', 401);
            }
    
            $existingToken = $user->tokens()->first();

            if ($existingToken) {
                Log::info("Revoking existing token for user: {$user->email}");
                $existingToken->delete();
            }

            $token = $user->createToken($user->email);
            Log::info("New token created for user: {$user->email}");

            return AppResponse::success([
                'user' => new UserResource($user),
                'token' => $token->plainTextToken,
            ], 'Login successful.', 200);
        } catch (InvalidCredentialsException $e) {
            return AppResponse::error('Invalid credentials provided.', 401);
        } catch (\Exception $e) {
            return AppResponse::error('An error occurred during login. Please try again later.', 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return AppResponse::error('User not authenticated', 401);
            }
    
            $user->tokens()->delete(); 
            return AppResponse::success('Logged out successfully', 200);
        } catch (\Exception $e) {
            return AppResponse::error('Logout failed.', 500);
        }
    }
}
