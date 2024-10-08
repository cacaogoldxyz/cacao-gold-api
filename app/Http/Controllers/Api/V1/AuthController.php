<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AppResponse; 
use App\Exceptions\InvalidCredentialsException;
use Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['password'] = Hash::make($validatedData['password']);
            unset($validatedData['password_confirmation']);
            $user = User::create($validatedData);

            $token = $user->createToken($user->email);

            return AppResponse::success([
                'user' => new UserResource($user),  
                'token' => $token->plainTextToken,
            ], 'Registration successful!', 201);
        } catch (\Exception $e) {
            // Log the actual error for internal use
            // Log::error('Registration error: ' . $e->getMessage());
            
            return AppResponse::error('Registration failed. Please try again later.', 500); // General error message
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $user = User::where('email', $validatedData['email'])->first(); 
    
            if (!$user || !Hash::check($validatedData['password'], $user->password)) {
                throw new InvalidCredentialsException();
            }
    
            $token = $user->createToken($user->email);
    
            return AppResponse::success([
                'user' => new UserResource($user),
                'token' => $token->plainTextToken,
            ], 'Login successful.', 200);
    
        } catch (InvalidCredentialsException $e) {
            return AppResponse::error('Invalid credentials provided.', 401);  // General error for invalid credentials
        } catch (\Exception $e) {
            // Log the actual error for internal use
            // Log::error('Login error: ' . $e->getMessage());

            // TODO: Return a general error message to avoid exposing sensitive information
            return AppResponse::error('An error occurred during login. Please try again later.', 500);  // General error for system failure
        }
    } 

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();
    
            return AppResponse::success(null, 'You are logged out.', 200);
        } catch (\Exception $e) {
            // Log the actual error for internal use
            // Log::error('Logout error: ' . $e->getMessage());
            
            return AppResponse::error('Logout failed. Please try again later.', 500); // General error message
        }
    }
}
