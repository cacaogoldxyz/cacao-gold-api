<?php

namespace App\Http\Controllers\Api\V1;

// use App\Exceptions\Handler;
use App\Exceptions\InvalidQueryException;
use App\Models\Comment;
use App\Models\Post;
use App\Services\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('comments')->whereNull('deleted_at')->get();
    
        if ($posts->isEmpty()) {
            return AppResponse::error('No posts available.', 404);
        }
        
        return AppResponse::success($posts, 'Posts retrieved successfully.', 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $post = Post::create($validated);
        return AppResponse::success($post, 'Post created successfully.', 201);
    }

    public function show($id)
    {
        $post = Post::find($id);
    
        if (!$post) {
            return AppResponse::error('Post not found.', 404);
        }
    
        $postWithComments = $post->load('comments');
        return AppResponse::success($postWithComments, 'Post retrieved successfully.', 200);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $userName = $request->input('user_name');

        if (!$query) {
            throw new InvalidQueryException('Query cannot be empty.');
        }
    
        $posts = Post::query()
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('body', 'LIKE', "%{$query}%");
            })
            ->when($userName, function ($queryBuilder) use ($userName) {
                return $queryBuilder->whereHas('user', function ($q) use ($userName) {
                    $q->where('name', 'LIKE', "%{$userName}%");
                });
            })
            ->with(['user', 'comments.user'])
            ->paginate(10);
    
        if ($posts->isEmpty()) {
            return AppResponse::error('No posts found matching the search criteria.', 404);
        }

        return AppResponse::success($posts, 'Search results retrieved successfully.', 200);
    }

    public function destroy(Post $post, Comment $comment)
    {
        if (!$post->exists) {
            return AppResponse::error('Post not found.', 404);
        }

        $post->comments()->delete();
        $post->delete();
        
        return AppResponse::success(message: 'Post and comments deleted successfully.', statusCode: 200);
    }

    public function restore(Request $request, $id)
    {
        $post = Post::withTrashed()->find($id);
    
        if (!$post || !$post->trashed()) {
            return AppResponse::error('Post not found or not deleted.', 404);
        }

        $post->restore();
        $post->comments()->onlyTrashed()->restore();
    
        return AppResponse::success(message: 'Post and associated comments restored successfully!', statusCode: 200);
    }

    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $trashedPosts = Post::onlyTrashed()->paginate($perPage); 
        if ($trashedPosts->isEmpty()) {
            return AppResponse::error('No trashed posts found.', 404);
        }

        return AppResponse::success($trashedPosts, 'Trashed posts retrieved successfully.', 200);
    }
}
