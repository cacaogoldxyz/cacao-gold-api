<?php

use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// Route to get the CSRF token (required for stateful requests)
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['success' => true]);
});

// API routes for Task management (No token required for viewing, token required for create, update, delete)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('tasks/{id}', [TaskController::class, 'destroy']);
    Route::delete('tasks/{id}/force-delete', [TaskController::class, 'forceDelete']);
    Route::patch('/tasks/{id}/restore', [TaskController::class, 'restore']);
    Route::get('/dashboard', [TaskController::class, 'index']);
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::get('task/trashed', [TaskController::class, 'trashed']);
    Route::get('tasks-search', [TaskController::class, 'search']);
});

// API routes for Post and Comment management (No token required)
Route::prefix('v1')->group(function () {
    Route::get('posts/search', [PostController::class, 'search']);
    Route::get('posts/trashed', [PostController::class, 'trashed']);
    Route::get('comments/trashed', [CommentController::class, 'trashed']);
    Route::patch('/posts/{id}/restore', [PostController::class, 'restore']);
    Route::get('/comments', [CommentController::class, 'index']);
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::get('comments-with-posts', [CommentController::class, 'getCommentsWithPosts']);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('posts.comments', CommentController::class);
    Route::get('comments/search', [CommentController::class, 'search'])->name('comments.search');
    Route::delete('comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// API routes for User Details (Requires token)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::middleware('auth:sanctum')->get('/user-details/{id}', [UserController::class, 'userDetails']);
});

