<?php

use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// API routes for Task management
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);         
    Route::post('/tasks', [TaskController::class, 'store']);      
    Route::get('/tasks/{task}', [TaskController::class, 'show']);   
    Route::put('/tasks/{task}', [TaskController::class, 'update']); 
    Route::get('task/trashed', [TaskController::class, 'trashed']);
    Route::delete('tasks/{id}', [TaskController::class, 'destroy']);
    Route::get('tasks-search', [TaskController::class, 'search']);
    Route::patch('/tasks/{id}/restore', [TaskController::class, 'restore']);  
});

// API routes for Post and Comment management
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('posts/search', [PostController::class, 'search']);
    Route::get('posts/trashed', [PostController::class, 'trashed']);
    Route::get('comments/trashed', [CommentController::class, 'trashed']);
    Route::patch('/posts/{id}/restore', [PostController::class, 'restore']);
    Route::get('/comments', [CommentController::class, 'index']);
    Route::get('comments-with-posts', [CommentController::class, 'getCommentsWithPosts']);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('posts.comments', CommentController::class);
    Route::get('comments/search', [CommentController::class, 'search'])->name('comments.search');
    Route::delete('comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

// Return the authenticated user's information
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API routes for Admin Authentication
Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// API routes for User Details
Route::prefix('v1')->group(function() {
    Route::middleware('auth:sanctum')->get('/user/details', [UserController::class, 'userDetails']);
});
